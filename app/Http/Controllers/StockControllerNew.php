<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Http\Controllers\NSEStockControllerNew;
use App\Http\Controllers\Traits\ApplicationTrait;
use App\Jobs\ProcessStockData;

// use App\Models\StockSymbol;
use App\Models\StockDetails;
// use App\Models\DailyData;
use App\Models\StockDailyPriceData;
use App\Models\StockHoliday;
use App\Models\IpoStockList;
// use App\Models\DailyStockJsonData;
// use App\Models\NseIndexDayRecord;

class StockControllerNew extends Controller
{
    use ApplicationTrait;

    protected NSEStockControllerNew $nseStockController;
    protected string $today;

    // last tested on 06 Sep 2026 11:48 AM
    public function __construct(NSEStockControllerNew $nseStockController)
    {
        $this->nseStockController = $nseStockController;
        $this->today = $this->nseStockController->today();
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function allStocks()
    {
        $symbols = $this->nseStockController->getAllStocksArray();

        if (!$symbols) {
            return 'Data not found';
        }

        // Prepare array for insert
        $insertData = array_map(fn($symbol) => [
            'symbol' => $symbol,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ], $symbols);


        $affected = DB::table('s_stock_symbols')->insertOrIgnore($insertData);

        return response()->json([
            'inserted_count' => $affected
        ]);
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function getAndUpdateHolidayList(Request $request)
    {
        try {
            $type = $request->query('type', 'trading');

            $response = $this->nseStockController->marketHolidays($type);
            $holidaysData = $response->getData(true);

            if (empty($holidaysData['CM']) || !is_array($holidaysData['CM'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No holiday data found.',
                ], 404);
            }

            $created = 0;
            $updated = 0;

            foreach ($holidaysData['CM'] as $holiday) {
                $year = date('Y', strtotime($holiday['tradingDate']));
                $date = date('Y-m-d', strtotime($holiday['tradingDate']));

                $record = StockHoliday::updateOrCreate(
                    [
                        'year' => $year,
                        'date' => $date,
                    ],
                    [
                        'week_day' => $holiday['weekDay'],
                        'description' => $holiday['description'],
                    ]
                );

                if ($record->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock holidays synchronized successfully.',
                'created' => $created,
                'updated' => $updated,
            ]);

        } catch (\Throwable $e) {
            $this->appLog([
                'error' => 'Error synchronizing stock holidays',
                'message' => $e->getMessage(),
                'type' => $request->query('type', 'trading'),
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize stock holidays.',
            ], 500);
        }
    }

    // last tested on 23 Sep 2026 11:48 AM
    public function processStockData(string $symbol)
    {
        try {
            $response = $this->nseStockController->getStockDetails($symbol);
            $data = $response->getData(true);
            
            if (empty($data)) {
                $this->appLog([
                    'message' => "No data returned for stock: {$symbol}",
                ], 'warning');
                return null;
            }
                    
        } catch (\Exception $e) {
            $this->appLog([
                'message' => "Error fetching data for stock {$symbol}: " . $e->getMessage(),
            ], 'error');
            throw $e;
        }

        try {
            return $this->insertStockData($symbol, $data);
        } catch (\Exception $e) {
            $this->appLog([
                'message' => "Error processing insert stock data {$symbol}: " . $e->getMessage(),
            ], 'error');
            throw $e;
        }
    }

    // last tested on 23 Sep 2026 11:48 AM
    protected function insertStockData(string $symbol, array $data)
    {
        try {
            $this->appLog([
                'message' => "Inserting data for stock: {$symbol}",
            ], 'info');
            
            $metaData = $data['metaData'] ?? [];
            $securityData = $data['securityData'] ?? [];
            $priceData = $data['priceData'] ?? [];
            $stockData = [];
            $stockData = array_merge($metaData, $securityData, $priceData);
            
            $insertData = [
                'symbol' => $symbol,
                'company_name' => $stockData['companyName'],
                'macro' => $stockData['macro'],
                'sector' => $stockData['sector'],
                'basic_industry' => $stockData['basicIndustry'],
                'industry' => $stockData['industryInfo'],
                'isin' => $stockData['isin'],
                'listing_date' => $stockData['listingDate'],
                'status' => $stockData['status'],
                'series' => $stockData['activeSeries'],
                'market_type' => $stockData['marketType'],
                'last_update_time' => $stockData['lastUpdateTime'],
                'sector_index' => $stockData['index'],
                'trading_status' => $stockData['tradingStatus'],
                'trading_segment' => $stockData['tradingSegment'],
                'surveillance_surv' => $stockData['surveillanceSurv'],
                'surveillance_desc' => $stockData['surveillanceDesc'],
                'face_value' => $stockData['faceValue'],
                'week_high_low_min' => $stockData['yearLow'],
                'week_high_low_min_date' => $stockData['yearLowDt'],
                'week_high_low_max' => $stockData['yearHigh'],
                'week_high_low_max_date' => $stockData['yearHightDt'],
                'stock_date' => $this->today,
                'stock_last_price' => $stockData['lastPrice'],
                'stock_change' => $stockData['change'],
                'stock_p_change' => $stockData['pChange'],
                'sector_index_all' => $stockData['indexList']
            ];

            $insertStockDetails = StockDetails::updateOrCreate(
                ['symbol' => $symbol],
                $insertData
            );

            $is52WeekHigh = !empty($stockData['yearHightDt'])
                && date('Y-m-d', strtotime($stockData['yearHightDt'])) === $this->today ? 1 : 0;
            $is52WeekHighValue = $is52WeekHigh ? $stockData['yearHigh'] : 0;
            $is52WeekLow = !empty($stockData['yearLowDt'])
                && date('Y-m-d', strtotime($stockData['yearLowDt'])) === $this->today ? 1 : 0;
            $is52WeekLowValue = $is52WeekLow ? $stockData['yearLow'] : 0;


            $insertPriceDataValues = [
                'symbol' => $symbol,
                'date' => $this->today,
                'last_price' => $stockData['lastPrice'],
                'change' => $stockData['change'],
                'p_change' => $stockData['pChange'],
                'previous_close' => $stockData['previousClose'],
                'open' => $stockData['open'],
                'close' => $stockData['closePrice'],
                'lower_cp' => $stockData['lowerCP'],
                'upper_cp' => $stockData['upperCP'],
                'intra_day_high_low_min' => $stockData['dayLow'],
                'intra_day_high_low_max' => $stockData['dayHigh'],
                'is_52_week_high' => $is52WeekHigh,
                'is_52_week_high_value' => $is52WeekHighValue,
                'is_52_week_low' => $is52WeekLow,
                'is_52_week_low_value' => $is52WeekLowValue,
                'sector_index_all' => $stockData['indexList']
            ];

            $insertPriceData = StockDailyPriceData::updateOrCreate(
                ['symbol' => $symbol,
                'date' => $this->today],
                $insertPriceDataValues
            );

            if (!$insertStockDetails || !$insertPriceData) {
                $this->appLog([
                    'message' => 'Error processing stock data: ' . $insertStockDetails->errors()->first() . ' - ' . $insertPriceData->errors()->first()
                ], 'error');
                throw new \RuntimeException('Error processing stock data for symbol: ' . $symbol);
            }
            return response()->json([
                'message' => "Data inserted or updated successfully for stock: {$symbol}"
            ]);
        } catch (\Exception $e) {
            $this->appLog([
                'message' => "Error inserting data for stock {$symbol}: " . $e->getMessage(),
            ], 'error');
            throw $e;
        }
    }

    // last tested on 23 Sep 2026 11:48 AM
    public function insertLatestDividedActions()
    {
        $dividedData = $this->nseStockController->corporateTopActions()->getData(true);
        $insertCount = 0;
        foreach($dividedData as $action){
            $corporateInfoData = [
                'actions_type' => 'corporate_actions',
                'symbol' => $action['symbol'],
                'actions_date' => $this->nseStockController->datetimeFormat($action['exDate'], "Y-m-d"),
                'record_date' => $this->nseStockController->datetimeFormat($action['recDate'], "Y-m-d"),
                'actions_purpose' => $action['subject'],
            ];

            DB::table('s_stock_corporate_info')->insertOrIgnore($corporateInfoData);
        }

        return response()->json([
            'message' => "Data inserted or updated successfully Corporate Divided",
            'success' => true
        ]);
    }
    
    // last tested on 23 Sep 2026 11:48 AM
    public function runMissedStocks()
    {
        try {
            $currentHour = now()->hour;
            $today = $this->today;
            $missedStocks = DB::table('s_stock_symbols as sss')
                ->whereNotIn('sss.symbol', function ($query) use ($today, $currentHour) {
                    $query->select('symbol')
                        ->from('s_stock_daily_price_data')
                        ->whereDate('date', $today)
                        ->when($currentHour > 15, function ($query) {
                            $query->whereTime('updated_at', '>', '15:00:00');
                        });
                })
                ->where('is_active', true);

            $dispatchedCount = 0;
            $missedStocks->chunk(100, function ($stocks) use (&$dispatchedCount) {
                foreach ($stocks as $stock) {
                    ProcessStockData::dispatch($stock->symbol);
                    $dispatchedCount++;
                }
            });
        } catch (\Throwable $e) {
             return response()->json([
                'result' => false,
                'msg' => $e->getMessage(),
                'error' => [
                    'type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }

        return response()->json([
            'result' => true,
            'dispatched_count' => $dispatchedCount,
            'msg' => "Successfully queued {$dispatchedCount} missed stocks",
        ]);
    }

    public function ShowIpoStockListFromNSE()
    {
        $notAllowedSecurityType = ["SME", "Debt", "InvITs", "REITs"];
        $normalizedNotAllowedSecurityType = array_map('strtoupper', $notAllowedSecurityType);

        $result = $this->nseStockController->getIpoUpcomingStocksfromNSE()->getData(true);
        $ipoStockList = array_filter($result, function ($ipo) use ($normalizedNotAllowedSecurityType) {
            $series = strtoupper((string) ($ipo['series'] ?? ''));

            return !in_array($series, $normalizedNotAllowedSecurityType, true);
        });

        //insert into ipo_stock_lists table
        $activeIpoList = [];
        foreach ($ipoStockList as $ipo) {
            $ipoData = [
                'symbol' => $ipo['symbol'] ?? null,
                'symbol_name' => $ipo['companyName'] ?? null,
                'security_type' => $ipo['series'] ?? null,
                'issue_start_date' => $this->nseStockController->datetimeFormat($ipo['issueStartDate'] ?? null, 'Y-m-d'),
                'issue_end_date' => $this->nseStockController->datetimeFormat($ipo['issueEndDate'] ?? null, 'Y-m-d'),
                'status' => 'Active',
                'issue_price_range' => $ipo['issuePrice'] ?? null,
                'issue_price' => null,
                'date_of_listing' => null,
            ];
            $activeIpoList[] = $ipoData;
        }

        $issuedIpoStockList = $this->nseStockController->getIpoIssuedStocksfromNSE()->getData(true);
        // dd($issuedIpoStockList);
        $ListedIpoList = [];
        foreach ($issuedIpoStockList as $ipo) {
            $securityType = strtoupper((string) ($ipo['securityType'] ?? null));
            if (in_array($securityType, $normalizedNotAllowedSecurityType, true)){
                continue; // Skip this IPO if the security type is not allowed
            }
            $listingDate = $this->nseStockController->datetimeFormat($ipo['listingDate'] ?? null, 'Y-m-d');
            $ipoData = [
                'symbol' => $ipo['symbol'] ?? null,
                'symbol_name' => $ipo['company'] ?? null,
                'security_type' => $securityType ?? null,
                'issue_start_date' => $this->nseStockController->datetimeFormat($ipo['ipoStartDate'] ?? null, 'Y-m-d'),
                'issue_end_date' => $this->nseStockController->datetimeFormat($ipo['ipoEndDate'] ?? null, 'Y-m-d'),
                'status' => $listingDate === null ? 'Listing' : 'Closed',
                'issue_price_range' => $ipo['priceRange'] ?? null,
                'issue_price' => $this->nseStockController->twoDecimals($ipo['issuePrice'] ?? null),
                'date_of_listing' => $listingDate,
            ];
            $ListedIpoList[] = $ipoData;
            if(count($ListedIpoList) >= 30){
                break; // Limit to 30 records
            }
        }
        return response()->json([
            'result' => true,
            'active_ipo_list' => $activeIpoList,
            'listed_ipo_list' => $ListedIpoList,
        ]);
    }

    public function getIpoStockListFromNSE()
    {
        $response = $this->ShowIpoStockListFromNSE()->getData(true);
        if (
            !($response['result'] ?? false)
            || !isset($response['active_ipo_list'], $response['listed_ipo_list'])
            || !is_array($response['active_ipo_list'])
            || !is_array($response['listed_ipo_list'])
        ) {
            return response()->json([
                'result' => false,
                'msg' => 'Unable to retrieve a valid IPO list for insertion.',
            ], 502);
        }

        $insertCount = 0;
        $updateCount = 0;
        $processedCount = 0;
        $ipoRecords = array_merge($response['active_ipo_list'], $response['listed_ipo_list']);

        foreach ($ipoRecords as $ipoData) {
            if ($processedCount >= 30) {
                break;
            }

            if (empty($ipoData['symbol'])) {
                continue;
            }

            $ipoRecord = IpoStockList::updateOrCreate(
                ['symbol' => $ipoData['symbol']],
                $ipoData
            );
            $ipoRecord->wasRecentlyCreated ? $insertCount++ : $updateCount++;
            $processedCount++;
        }

        return response()->json([
            'result' => true,
            'inserted_count' => $insertCount,
            'updated_count' => $updateCount,
            'msg' => "Successfully inserted {$insertCount} and updated {$updateCount} IPO stocks",
        ]);
    }
}
