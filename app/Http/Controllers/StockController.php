<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockSymbol;
use App\Models\StockDetails;
use App\Models\DailyData;
use App\Models\StockDailyPriceData;
use App\Models\StockHoliday;
use App\Models\StockIndexName;
use Illuminate\Support\Facades\Log;
use DB;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function allStocks()
    {
        $symbols = (new NSEStockController())->getAllStocksArray();

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

        // StockSymbols::insertOrIgnore($insertData);

        $affected = DB::table('s_stock_symbols')->insertOrIgnore($insertData);

        return response()->json([
            'inserted_count' => $affected
        ]);

        // return $symbols;
    }

    public function monthlyView(Request $request)
    {
        // $month = $request->month ?? now()->format('Y-m');
        // $start = date('Y-m-01', strtotime($month));
        // $end   = date('Y-m-t', strtotime($month));

        $end = now()->format('Y-m-d');              // today
        $start = now()->subMonth()->format('Y-m-d'); // 1 month back


        // 1️⃣ Fetch price data for selected month
        $prices = DB::table('s_stock_prices')
            ->join('s_stock_symbols', 's_stocks_symbols.id', '=', 's_stock_prices.stock_id')
            ->whereBetween('s_stock_prices.date', [$start, $end])
            ->select(
                's_stock_symbols.symbol',
                's_stock_prices.date',
                's_stock_prices.price_close'
            )
            ->orderBy('s_stock_symbols.symbol')
            ->orderBy('s_stock_symbols.date')
            ->get();

        // 2️⃣ Prepare date list for header
        $dates = [];
        $period = new \DatePeriod(
            new \DateTime($start),
            new \DateInterval('P1D'),
            (new \DateTime($end))->modify('+1 day')
        );
        foreach ($period as $dt) {
            $dates[] = $dt->format("Y-m-d");
        }

        // 3️⃣ Transform into pivot data
        $grouped = $prices->groupBy('symbol');
        $result = [];

        foreach ($grouped as $symbol => $records) {
            $records = $records->sortBy('date')->values();

            $row = ['symbol' => $symbol];
            $prevClose = null;

            foreach ($records as $rec) {
                if ($prevClose === null) {
                    $percent = 0; // for first day
                } else {
                    $percent = (($rec->price_close - $prevClose) / $prevClose) * 100;
                }

                $row[$rec->date] = round($percent, 2);
                $prevClose = $rec->price_close;
            }

            $result[] = $row;
        }

        return view('stocks.monthly', compact('dates', 'result', 'month'));
    }

    public function processStockData($symbol)
    {
        $today = (new NSEStockController())->today();
        try {
            $data = (new NSEStockController())->equity($symbol)->getData(true);

            if (!$data) {
                Log::warning("No data returned for stock: {$symbol}");
                return null;
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Connection error for stock {$symbol}: " . $e->getMessage());
            throw $e; // Re-throw to be caught by the command handler
        } catch (\Exception $e) {
            Log::error("Error fetching data for stock {$symbol}: " . $e->getMessage());
            throw $e;
        }

        try {
            $infoData = $data['info'];
            $metadata = $data['metadata'];
            $securityInfo = $data['securityInfo'];
            // $sddDetails = $data['sddDetails'];
            // $currentMarketType = $data['currentMarketType'];
            $priceInfo = $data['priceInfo'];
            $industryInfo = $data['industryInfo'];
            // $preOpenMarket = $data['preOpenMarket'];

            $infoStockSymbol = $infoData['symbol'];
            $infoStockName = $infoData['companyName'];
            $infoIsin = $infoData['isin'];
            $infoListingDate = $infoData['listingDate'];
            $metaDataStatus = $metadata['status'] ?? 'N/A';
            $metaDataLastUpdateTime = $metadata['lastUpdateTime'] ?? 'N/A';
            $metaDataPDSEctorInd = $metadata['pdSectorInd'] ?? 'N/A';
            // $metaDataPDSEctorIndAll = $metadata['pdSectorIndAll'];
            $securityInfoTradingStatus = $securityInfo['tradingStatus'] ?? 'N/A';
            $securityInfoTradingSegment = $securityInfo['tradingSegment'] ?? 'N/A';
            $securityInfoFaceValue = $securityInfo['faceValue'] ?? 0;
            $priceInfoLastPrice = $priceInfo['lastPrice'] ?? 0;
            $priceInfoChange = $priceInfo['change'] ?? 0;
            $priceInfoPChange = $priceInfo['pChange'] ?? 0;
            $priceInfoPreviousClose = $priceInfo['previousClose'] ?? 0;
            $priceInfoOpen = $priceInfo['open'] ?? 0;
            $priceInfoClose = (float) ($priceInfo['close'] ?? 0);
            $priceInfoLowerCp = (float) $priceInfo['lowerCP'] ?? 0;
            $priceInfoUpperCp = (float)$priceInfo['upperCP'] ?? 0;
            $priceInfoIntraDayHighLowMin = (float)($priceInfo['intraDayHighLow']['min'] ?? 0);
            $priceInfoIntraDayHighLowMax = (float)($priceInfo['intraDayHighLow']['max'] ?? 0);
            $priceInfoWeekHighLowMin = (float) ($priceInfo['weekHighLow']['min'] ?? 0);
            $priceInfoWeekHighLowMax = (float)($priceInfo['weekHighLow']['max'] ?? 0);
            $priceInfoWeekHighLowMinDate = $priceInfo['weekHighLow']['minDate'] ?? 'N/A';
            $priceInfoWeekHighLowMaxDate = $priceInfo['weekHighLow']['maxDate'] ?? 'N/A';
            $industryInfoMacro = $industryInfo['macro'] ?? 'N/A';
            $industryInfoSector = $industryInfo['sector'] ?? 'N/A';
            $industryInfoIndustry = $industryInfo['industry'] ?? 'N/A';
            $industryInfoBasicIndustry = $industryInfo['basicIndustry'] ?? 'N/A';

            $insertData = [
                'symbol' => $infoStockSymbol,
                'company_name' => $infoStockName,
                'macro' => $industryInfoMacro,
                'sector' => $industryInfoSector,
                'basic_industry' => $industryInfoBasicIndustry,
                'industry' => $industryInfoIndustry,
                'isin' => $infoIsin,
                'listing_date' => $infoListingDate,
                'status' => $metaDataStatus,
                'last_update_time' => date('Y-m-d H:i:s', strtotime($metaDataLastUpdateTime)),
                'pdsectorind' => $metaDataPDSEctorInd,
                'trading_status' => $securityInfoTradingStatus,
                'trading_segment' => $securityInfoTradingSegment,
                'face_value' => $securityInfoFaceValue,
                'week_high_low_min' => round($priceInfoWeekHighLowMin, 2),
                'week_high_low_min_date' => date('Y-m-d', strtotime($priceInfoWeekHighLowMinDate)),
                'week_high_low_max' => round($priceInfoWeekHighLowMax, 2),
                'week_high_low_max_date' => date('Y-m-d', strtotime($priceInfoWeekHighLowMaxDate)),
            ];

            $insertStockDetails = StockDetails::updateOrCreate(
                ['symbol' => $infoStockSymbol],
                $insertData
            );

            $insertPriceDataValues = [
                'symbol' => $infoStockSymbol,
                'date' => $today,
                'last_price' => round($priceInfoLastPrice, 2),
                'change' => round($priceInfoChange, 2),
                'p_change' => round($priceInfoPChange, 2),
                'previous_close' => round($priceInfoPreviousClose, 2),
                'open' => round($priceInfoOpen, 2),
                'close' => round($priceInfoClose, 2),
                'lower_cp' => round($priceInfoLowerCp, 2),
                'upper_cp' => round($priceInfoUpperCp, 2),
                'intra_day_high_low_min' => round($priceInfoIntraDayHighLowMin, 2),
                'intra_day_high_low_max' => round($priceInfoIntraDayHighLowMax, 2),
            ];

            $insertDailyData = [
                'symbol' => $infoStockSymbol,
                'date' => $today,
                'daily_data' => json_encode($data),
            ];

            DailyData::insert($insertDailyData);

            $insertPriceData = StockDailyPriceData::updateOrCreate(
                ['symbol' => $infoStockSymbol,
                'date' => $today],
                $insertPriceDataValues
            );
            
            if (!$insertStockDetails || !$insertPriceData) {
                Log::error('Error processing stock data: ' . $insertStockDetails->errors()->first() . ' - ' . $insertPriceData->errors()->first());
                throw new \Exception('Error processing stock data');
            }
            return $infoStockSymbol;
        } catch (\Exception $e) {
            Log::error('Error processing stock data: ' . $e->getMessage());
            throw new \Exception('Error processing stock data: ' . $e->getMessage());
        }
    }

    public function getHolidayList(Request $request)
    {
        // echo "getHolidayList: " . print_r($request->all(), true);
        $type = $request->query('type');
        $response = (new NSEStockController())->marketHolidays($type);
        $holidaysData = $response->getData(true);
        if(empty($holidaysData['CM'])) {
            return response()->json(['error' => 'No data found']);
        }
        $holidays = $holidaysData['CM'];
        foreach($holidays as $holiday) {
            $year = date('Y', strtotime($holiday['tradingDate']));
            $date = date('Y-m-d', strtotime($holiday['tradingDate']));
            $week_day = $holiday['weekDay'];
            $description = $holiday['description'];
            StockHoliday::updateOrCreate(['year' => $year, 'date' => $date], ['year' => $year, 'date' => $date, 'week_day' => $week_day, 'description' => $description]);
        }
        return response()->json(['success' => 'Inserted successfully']);
    }

    public function getIndexNames()
    {
        $response = (new NSEStockController())->getIndexNames();
        $indexNames = $response->getData(true);
        // if(empty($indexNames['stn'])) {
        //     return response()->json(['error' => 'No data found']);
        // }-
        // $indexNamesData = $indexNames['stn'];
        // echo "<pre>";
        // print_r($indexNames['stn']);
        foreach($indexNames['stn'] as $indexName) {
            list($indexSymbol, $indexNameValue) = $indexName;
            StockIndexName::updateOrCreate(['index_symbol' => $indexSymbol], ['index_symbol' => $indexSymbol, 'index_name' => $indexNameValue]);
        }
        // die();
        return response()->json(['success' => 'Inserted successfully']);
    }
}
