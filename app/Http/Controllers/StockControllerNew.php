<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Http\Controllers\NSEStockControllerNew;
use App\Http\Controllers\Traits\ApplicationTrait;

// use App\Models\StockSymbol;
use App\Models\StockDetails;
// use App\Models\DailyData;
use App\Models\StockDailyPriceData;
use App\Models\StockHoliday;
// use App\Models\StockIndexName;
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
}
