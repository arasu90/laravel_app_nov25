<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockSymbol;
use App\Models\StockDetails;
use App\Models\DailyData;
use App\Models\StockDailyPriceData;
use App\Models\StockHoliday;
use App\Models\StockIndexName;
use App\Models\DailyStockJsonData;
use App\Models\NseIndexDayRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
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


        $affected = DB::table('s_stock_symbols')->insertOrIgnore($insertData);

        return response()->json([
            'inserted_count' => $affected
        ]);
    }

    // no more used this function
    public function monthlyView(Request $request)
    {
        $end = now()->format('Y-m-d');              // today
        $start = now()->subMonth()->format('Y-m-d'); // 1 month back


        // 1️⃣ Fetch price data for selected month
        $prices = DB::table('s_stock_prices')
            ->join('s_stock_symbols', 's_stocks_symbols.id', '=', 's_stock_prices.stock_id')
            ->where('s_stock_symbols.is_active', true)
            ->whereBetween('s_stock_prices.date', [$start, $end])
            ->select(
                's_stock_symbols.symbol',
                's_stock_prices.date',
                's_stock_prices.price_close'
            )
            ->orderBy('s_stock_symbols.symbol')
            ->orderBy('s_stock_prices.date')
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
            Log::info("processStockData: {$symbol} :: ");
            $data = (new NSEStockController())->equity($symbol)->getData(true);
            Log::info("Data returned for stock: {$symbol} :: ");

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
            $priceInfo = $data['priceInfo'];
            $industryInfo = $data['industryInfo'];

            $infoStockSymbol = $infoData['symbol'];
            $infoStockName = $infoData['companyName'];
            $infoIsin = $infoData['isin'];
            $infoListingDate = $infoData['listingDate'];
            $metaDataSeries = $metadata['series'] ?? 'N/A';
            $metaDataStatus = $metadata['status'] ?? 'N/A';
            $metaDataLastUpdateTime = $metadata['lastUpdateTime'] ?? 'N/A';
            $metaDataPDSEctorInd = $metadata['pdSectorInd'] ?? 'N/A';
            $metaDataPDSEctorIndAll = $metadata['pdSectorIndAll'] ?? '[]';
            $securityInfoTradingStatus = $securityInfo['tradingStatus'] ?? 'N/A';
            $securityInfoTradingSegment = $securityInfo['tradingSegment'] ?? 'N/A';
            $securityInfoFaceValue = $securityInfo['faceValue'] ?? 0;
            $securityInfoSurveillanceSurv = $securityInfo['surveillance']['surv'] ?? 'N/A';
            $securityInfoSurveillanceDesc = $securityInfo['surveillance']['desc'] ?? "N/A";
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
            // dd($metadata);
            if($metaDataPDSEctorIndAll != "NA"){
                $metaDataPDSEctorIndAll = implode(",",$metaDataPDSEctorIndAll);
            }
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
                'series' => $metaDataSeries,
                'last_update_time' => date('Y-m-d H:i:s', strtotime($metaDataLastUpdateTime)),
                'pdsectorind' => $metaDataPDSEctorInd,
                'trading_status' => $securityInfoTradingStatus,
                'trading_segment' => $securityInfoTradingSegment,
                'surveillance_surv' => $securityInfoSurveillanceSurv,
                'surveillance_desc' => $securityInfoSurveillanceDesc,
                'face_value' => $securityInfoFaceValue,
                'week_high_low_min' => round($priceInfoWeekHighLowMin, 2),
                'week_high_low_min_date' => date('Y-m-d', strtotime($priceInfoWeekHighLowMinDate)),
                'week_high_low_max' => round($priceInfoWeekHighLowMax, 2),
                'week_high_low_max_date' => date('Y-m-d', strtotime($priceInfoWeekHighLowMaxDate)),
                'stock_date' => $today,
                'stock_last_price' => round($priceInfoLastPrice, 2),
                'stock_change' => round($priceInfoChange, 2),
                'stock_p_change' => round($priceInfoPChange, 2),
                'pd_sector_ind_all' => $metaDataPDSEctorIndAll
            ];
            // dd($insertData);
            if("-" == $metaDataLastUpdateTime || $metaDataLastUpdateTime == "N/A" || $metaDataLastUpdateTime == "N/A"){
                unset($insertData['last_update_time']);
            }
            Log::error("infoStockSymbol ".$infoStockSymbol. " data is ". json_encode($insertData, true));
            $insertStockDetails = StockDetails::updateOrCreate(
                ['symbol' => $infoStockSymbol],
                $insertData
            );

            $is52WeekHigh = date('Y-m-d', strtotime($priceInfoWeekHighLowMaxDate)) == $today ? 1 : 0;
            $is52WeekHighValue = date('Y-m-d', strtotime($priceInfoWeekHighLowMaxDate)) == $today ? round($priceInfoWeekHighLowMax, 2) : 0;
            $is52WeekLow = date('Y-m-d', strtotime($priceInfoWeekHighLowMinDate)) == $today ? 1 : 0;
            $is52WeekLowValue = date('Y-m-d', strtotime($priceInfoWeekHighLowMinDate)) == $today ? round($priceInfoWeekHighLowMin, 2) : 0;

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
                'is_52_week_high' => $is52WeekHigh,
                'is_52_week_high_value' => $is52WeekHighValue,
                'is_52_week_low' => $is52WeekLow,
                'is_52_week_low_value' => $is52WeekLowValue,
                'pd_sector_ind_all' => $metaDataPDSEctorIndAll,
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

        foreach($indexNames['stn'] as $indexName) {
            list($indexSymbol, $indexNameValue) = $indexName;
            StockIndexName::updateOrCreate(['index_symbol' => $indexSymbol], ['index_symbol' => $indexSymbol, 'index_name' => $indexNameValue]);
        }
        return response()->json(['success' => 'Inserted successfully']);
    }

    public function insertStockDailyData($symbol)
    {
        $today = (new NSEStockController())->todayDateTime();
        $data = (new NSEStockController())->equity($symbol)->getData(true);
        if (!$data) {
            Log::warning("No data returned for stock: {$symbol}");
            return null;
        }
        Log::info("Inserting stock daily data for {$symbol} :: Started");
        Log::channel('stock_backup_json')->info("Stock {date('Y-m-d H:i:s')} {$symbol} Daily Data :: ".json_encode($data));
        $metaDataLastUpdateTime = $data['metadata']['lastUpdateTime'] ?? 'N/A';
        $nseDate = date('Y-m-d H:i:s', strtotime($metaDataLastUpdateTime));
        
        $insertData = [
                'symbol' => $symbol,
                'date' => $today,
                'nse_date' => $nseDate,
                'daily_data' => json_encode($data),
        ];
        DailyStockJsonData::insert($insertData);
        Log::info("Stock {$symbol} Daily Json Data Inserted Successfully");
        return $symbol;
    }

    public function updateCorporateInfo($symbol)
    {
        try {
            Log::info("updateCorporateInfo: {$symbol} :: ");
            $data = (new NSEStockController())->corporateInfo($symbol)->getData(true);
            Log::info("Data returned for stock: {$symbol} :: ");

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
            $corporateActions = $data['corporate_actions']['data'];
            foreach($corporateActions as $action){
                $corporateInfoData = [
                    'actions_type' => 'corporate_actions',
                    'symbol' => $action['symbol'],
                    'actions_date' => date("Y-m-d", strtotime($action['exdate'])),
                    'actions_purpose' => $action['purpose'],
                ];

                DB::table('s_stock_corporate_info')->insertOrIgnore($corporateInfoData);
            }

            $boradMeeting = $data['borad_meeting']['data'];
            foreach($boradMeeting as $action){
                $boradMeetingInfoData = [
                    'actions_type' => 'borad_meeting',
                    'symbol' => $action['symbol'],
                    'actions_date' => date("Y-m-d", strtotime($action['meetingdate'])),
                    'actions_purpose' => $action['purpose'],
                ];

                DB::table('s_stock_corporate_info')->insertOrIgnore($boradMeetingInfoData);
            }

            $latestAnnouncements = $data['latest_announcements']['data'];
            foreach($latestAnnouncements as $action){
                $latestAnnouncementsInfoData = [
                    'actions_type' => 'latest_announcements',
                    'symbol' => $action['symbol'],
                    'actions_date' => date("Y-m-d", strtotime($action['broadcastdate'])),
                    'actions_purpose' => $action['subject'],
                ];

                DB::table('s_stock_corporate_info')->insertOrIgnore($latestAnnouncementsInfoData);
            }

            Log::info("Corporate info updated: {$symbol} ");

        } catch (\Exception $e) {
            Log::error('Error corporate info stock data: ' .  $symbol . $e->getMessage());
            throw new \Exception('Error corporate info stock data: ' .  $symbol . $e->getMessage());
        }

    }

    public function updateAllIndex()
    {
        $trade_date = (new NSEStockController())->today();
        $dayIndexData = (new NSEStockController())->indices()->getData(true);
        foreach($dayIndexData['data'] as $indexData){
            $insertindexData = [
                'index_symbol' => $indexData['indexSymbol'],
                'value_last' => $indexData['last'],
                'value_change' => $indexData['variation'],
                'value_p_change' => $indexData['percentChange'],
                'value_open' => $indexData['open'],
                'day_high' => $indexData['high'],
                'day_low' => $indexData['low'],
                'previous_close' => $indexData['previousClose'],
                'year_high' => $indexData['yearHigh'],
                'year_low' => $indexData['yearLow'],
                'declines' => $indexData['declines'] ?? 0,
                'advances' => $indexData['advances'] ?? 0,
                'unchanged' => $indexData['unchanged'] ?? 0,
                'trade_date' => $trade_date,
            ];

            NseIndexDayRecord::updateOrCreate([
                'index_symbol' => $indexData['indexSymbol'],
                'trade_date' => $trade_date,
            ],$insertindexData);
            echo 'NSE Index Data Insert ::'.$indexData['indexSymbol'];
            echo '<br />';
            Log::info('NSE Index Data Insert ::'.$indexData['indexSymbol']);
        }
    }
}
