<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\StockDailyPriceData;
use App\Models\StockHoliday;
use App\Models\StockDetails;
use App\Models\StockSymbol;
use App\Models\MyPortfolioStock;
use App\Models\MyWatchList as MyWatchListMaster;
use App\Models\NseIndexDayRecord;
use Carbon\Carbon;

use App\Http\Controllers\Home\Traits\DashboardTrait;
use App\Http\Controllers\Home\Traits\{
    UtilityTrait
};
use Exception;
use Illuminate\Testing\Constraints\CountInDatabase;
use stdClass;

class HomeController extends Controller
{
    public const VALUE_P_CHANGE_LESS_THAN = "value_p_change < 0";

    use DashboardTrait;
    use UtilityTrait;

    public function stockListTableView()
    {
        list($start, $end) = (new NSEStockController)->getDateRange(15);
        
        $prices = DB::table('s_stock_daily_price_data')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
            ->whereBetween('s_stock_daily_price_data.date', [$start, $end])
            // ->where('s_stock_symbols.symbol', 'IDEA')
            ->where('s_stock_symbols.is_active', true)
            ->select(
                's_stock_daily_price_data.symbol',
                's_stock_details.company_name',
                's_stock_daily_price_data.date',
                's_stock_daily_price_data.last_price',
                's_stock_daily_price_data.change',
                's_stock_daily_price_data.p_change'
            )
            ->groupBy('s_stock_daily_price_data.symbol', 's_stock_details.company_name', 's_stock_daily_price_data.date')
            ->orderBy('s_stock_daily_price_data.symbol')
            ->orderBy('s_stock_daily_price_data.date')
            ->get();

        // 3️⃣ Transform into pivot data
        $grouped = $prices->groupBy('symbol');
        $dates =  $prices->unique('date')->pluck('date')->toArray();

        $result = [];
        foreach ($grouped as $symbol => $records) {
            $row = ['symbol' => $symbol, 'company_name' => $records->first()->company_name];

            foreach ($records as $rec) {
                $percent = $rec->last_price;

                $row[$rec->date]['last_price'] = round($percent, 2);
                $row[$rec->date]['change'] = round($rec->change, 2);
                $row[$rec->date]['p_change'] = round($rec->p_change, 2);
            }

            $result[] = $row;
        }
        return view('stock_list_table', compact('dates', 'result'));
    }

    public function oneDayView(Request $request)
    {
        $stock_name = $request->input('stock_name');
        $sort_by = $request->input('sort_by');
        
        $sort_by_column = match($sort_by) {
            'name_az' => [
                'column' => 's_stock_symbols.symbol',
                'order' => 'asc',
                'where' => null,
            ],
            'name_za' => [
                'column' => 's_stock_symbols.symbol',
                'order' => 'desc',
                'where' => null,
            ],
            'low_price' => [
                'column' => 's_stock_daily_price_data.last_price',
                'order' => 'asc',
                'where' => null,
            ],
            'high_price' => [
                'column' => 's_stock_daily_price_data.last_price',
                'order' => 'desc',
                'where' => null,
            ],
            'p_change_asc' => [
                'column' => 's_stock_daily_price_data.p_change',
                'order' => 'asc',
                'where' => null,
            ],
            'p_change_desc' => [
                'column' => 's_stock_daily_price_data.p_change',
                'order' => 'desc',
                'where' => null,
            ],
            'low_price_zero' => [
                'column' => 's_stock_daily_price_data.last_price',
                'order' => 'asc',
                'where' => 's_stock_daily_price_data.last_price != 0',
            ],
            'high_price_zero' => [
                'column' => 's_stock_daily_price_data.last_price',
                'order' => 'desc',
                'where' => 's_stock_daily_price_data.last_price != 0',
            ],
            'p_change_asc_gt_zero' => [
                'column' => 's_stock_daily_price_data.p_change',
                'order' => 'asc',
                'where' => 's_stock_daily_price_data.p_change > 0',
            ],
            'p_change_desc_gt_zero' => [
                'column' => 's_stock_daily_price_data.p_change',
                'order' => 'desc',
                'where' => 's_stock_daily_price_data.p_change > 0',
            ],
            'p_change_asc_lt_zero' => [
                'column' => 's_stock_daily_price_data.p_change',
                'order' => 'asc',
                'where' => 's_stock_daily_price_data.p_change < 0',
            ],
            'p_change_desc_lt_zero' => [
                'column' => 's_stock_daily_price_data.p_change',
                'order' => 'desc',
                'where' => 's_stock_daily_price_data.p_change < 0',
            ],
            'low_price_price' => [
                'column' => 's_stock_daily_price_data.change',
                'order' => 'asc',
                'where' => null,
            ],
            'high_price_price' => [
                'column' => 's_stock_daily_price_data.change',
                'order' => 'desc',
                'where' => null,
            ],
            'p_change_price_asc_gt_zero' => [
                'column' => 's_stock_daily_price_data.change',
                'order' => 'asc',
                'where' => 's_stock_daily_price_data.change > 0',
            ],
            'p_change_price_desc_gt_zero' => [
                'column' => 's_stock_daily_price_data.change',
                'order' => 'desc',
                'where' => 's_stock_daily_price_data.change > 0',
            ],
            'p_change_price_asc_lt_zero' => [
                'column' => 's_stock_daily_price_data.change',
                'order' => 'asc',
                'where' => 's_stock_daily_price_data.change < 0',
            ],
            'p_change_price_desc_lt_zero' => [
                'column' => 's_stock_daily_price_data.change',
                'order' => 'desc',
                'where' => 's_stock_daily_price_data.change < 0',
            ],
            default => [
                'column' => 's_stock_symbols.symbol',
                'order' => 'asc',
                'where' => null,
            ],
        };
        
        $today = (new NSEStockController())->today();
        $stockCount = StockSymbol::where('is_active', true)->count();
        $record_date = $today;
        $day_records = DB::table('s_stock_daily_price_data')
            ->where('date', $today)
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
            ->where('s_stock_symbols.is_active', true)
            ->select(
                's_stock_daily_price_data.symbol',
                's_stock_details.company_name',
                's_stock_daily_price_data.date',
                's_stock_daily_price_data.last_price',
                's_stock_daily_price_data.change',
                's_stock_daily_price_data.p_change'
            )
            // ->limit(110)
            ->whereRaw($sort_by_column['where'] ?? '1=1')
            ->groupBy('s_stock_daily_price_data.symbol', 's_stock_details.company_name', 's_stock_daily_price_data.date')
            // ->orderBy('s_stock_daily_price_data.symbol')
            // ->orderBy('s_stock_daily_price_data.date')
            // ->orderBy('s_stock_daily_price_data.p_change', 'desc')
            ->orderBy($sort_by_column['column'], $sort_by_column['order']);
            
        if(!empty($stock_name)):
            $day_records = $day_records->where('s_stock_symbols.symbol', 'like', '%'.$stock_name.'%')->orWhere('s_stock_details.company_name', 'like', '%'.$stock_name.'%');
        endif;
        $day_records = $day_records->get();

        return view('one_day_view', compact('day_records', 'record_date', 'stockCount'));
    }

    public function stockDetailView(Request $request)
    {
        $stock_name = $request->input('stock_name') ?? 'TNTELE';
        $stock_daily_price_data = StockDailyPriceData::where('symbol', $stock_name)
            ->orderBy('date', 'desc')
            ->get();
        $stock_details = StockDetails::where('symbol', $stock_name)->first();
        $stock_list = StockSymbol::with('details')->where('is_active', true)->orderBy('symbol')->get();

        $lineLabel = $stock_daily_price_data->slice(0, 5)
            ->pluck('date')
            ->reverse()
            ->values()
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('d-M'));
        
        $lineData = $stock_daily_price_data->slice(0, 5)
            ->pluck('last_price')
            ->reverse()
            ->values();
        $lineData_1 = $stock_daily_price_data->slice(0, 5)
            ->pluck('open')
            ->reverse()
            ->values();

        $chartData['line']['label'] = $lineLabel;
        $chartData['line']['data'] = $lineData;
        $chartData['line']['data_1'] = $lineData_1;
        return view('stock_detail_view',
            compact('stock_daily_price_data',
            'stock_details',
            'stock_list',
            'stock_name',
            'chartData'
            )
        );
    }
    
    public function averageStock(Request $request)
    {
        $current_total_quantity = $request->input('current_total_quantity');
        $current_average_price = $request->input('current_average_price');
        $new_buy_price = $request->input('new_buy_price');
        $expected_average_price = $request->input('expected_average_price');
        $calculator_type = $request->input('calculator_type');
        $new_buy_quantity = $request->input('new_buy_quantity');
        $qty_profit_loss = $request->input('qty_profit_loss');
        $qty_live_price = $request->input('qty_live_price');
        $avg_profit_loss = $request->input('avg_profit_loss');
        $avg_live_price = $request->input('avg_live_price');
        $new_buy_quantity_average = 0;
        $new_buy_price_average = 0;
        $average_price_increment = $expected_average_price>0 ? 0.004 : 0;
        if($calculator_type == 'average_stock' && $expected_average_price > 0):
            $after_expected_average_price = $expected_average_price - $average_price_increment;
            $numerator = $current_total_quantity * ($current_average_price - $after_expected_average_price);
            $denominator = $after_expected_average_price - $new_buy_price;
            if($denominator == 0):
                $new_buy_quantity_average = 0;
            else:
                $new_buy_quantity_average = round($numerator / $denominator);
            endif;
        endif;
        if($calculator_type == 'buy_quantity_calculator' && $new_buy_price > 0):
            $totalPrice = ($current_total_quantity * $current_average_price) + ($new_buy_price * $new_buy_quantity);
            $denominator = $current_total_quantity + $new_buy_quantity;
            if($denominator == 0):
                $new_buy_price_average = 0;
            else:
                $new_buy_price_average = round($totalPrice / $denominator, 2);
            endif;
        endif;
        return view('average_stock', compact('current_total_quantity', 'current_average_price', 'new_buy_price', 'expected_average_price', 'new_buy_quantity', 'new_buy_quantity_average', 'new_buy_price_average', 'qty_profit_loss', 'qty_live_price', 'avg_profit_loss', 'avg_live_price'));
    }

    public function myPortfolio()
    {
        $stock_list = StockSymbol::with('details')
            ->where('is_active', true)
            ->orderBy('symbol')
            ->get();

        $today = (new NSEStockController())->today();

        $myPortfolioStocks = DB::table('s_portfolio_stocks as p')
            ->join('s_stock_symbols as s', 's.symbol', '=', 'p.symbol')
            ->join('s_stock_details as d', 'd.symbol', '=', 's.symbol')
            ->join('s_stock_daily_price_data as dp', function($join) use ($today) {
                $join->on('dp.symbol', '=', 'p.symbol')
                    ->where('dp.date', $today);
            })
            ->where('s.is_active', true)
            ->where('p.portfolio_type',1)
            ->select(
                'p.symbol',
                'd.company_name',
                DB::raw('SUM(p.buy_qty) as total_qty'),
                DB::raw('ROUND(SUM(p.buy_qty * p.buy_price)/SUM(p.buy_qty), 2) as avg_buy_price'),
                'dp.last_price',
                'dp.change',
                'dp.p_change'
            )
            ->groupBy('p.symbol', 'd.company_name', 'dp.last_price', 'dp.change', 'dp.p_change')
            ->orderBy('p.symbol', 'asc')
            ->get();

        return view('my_portfolio', compact('stock_list', 'myPortfolioStocks'));
    }


    public function addMyPortfolio(Request $request)
    {
        $portfolio_type = $request->input('portfolio_type') ?? 1;
        $request->validate([
            'stock_name' => 'required',
            'buy_qty' => 'required|numeric|min:1',
            'buy_price' => 'required|numeric|min:0',
            'buy_date' => 'required|date'
        ]);

        MyPortfolioStock::create([
            'symbol' => $request->stock_name,
            'buy_price' => $request->buy_price,
            'buy_qty' => $request->buy_qty,
            'buy_date' => date('Y-m-d', strtotime($request->buy_date)),
            'portfolio_type' => $portfolio_type
        ]);
        
        return redirect()->back()
            ->with('success', 'Stock added to portfolio successfully');
    }

    public function myWatchList(Request $request)
    {
        $stock_name = $request->get('stock_name') ?? null;
        $today = (new NSEStockController())->today();
        $price_min = $request->get('price_min') ?? null;
        $price_max = $request->get('price_max') ?? null;

        $watchListMaster = MyWatchListMaster::get();
        $watchListList = [];
        foreach($watchListMaster as $watchList):
            $query = DB::table('s_watchlist_items')
                ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_watchlist_items.symbol')
                ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
                ->join('s_stock_daily_price_data', 's_stock_daily_price_data.symbol', '=', 's_stock_symbols.symbol')
                ->where('s_stock_daily_price_data.date', $today)
                ->where('s_stock_symbols.is_active', true)
                ->where('s_watchlist_items.watchlist_id', $watchList->id);

            if (!empty($stock_name)) {
                $query->where('s_stock_symbols.symbol', $stock_name);
            } elseif (!empty($price_min) && !empty($price_max)) {
                $query->whereBetween('s_stock_daily_price_data.last_price', [$price_min, $price_max]);
            }

            $watchListItems = $query->select(
                's_watchlist_items.symbol',
                's_stock_details.company_name',
                's_stock_daily_price_data.last_price',
                's_stock_daily_price_data.change',
                's_stock_daily_price_data.p_change',
                's_stock_daily_price_data.previous_close',
                's_stock_daily_price_data.open',
                's_stock_daily_price_data.close',
                's_stock_daily_price_data.lower_cp',
                's_stock_daily_price_data.upper_cp',
                's_stock_daily_price_data.intra_day_high_low_min',
                's_stock_daily_price_data.intra_day_high_low_max',
                's_stock_details.week_high_low_min',
                's_stock_details.week_high_low_min_date',
                's_stock_details.week_high_low_max',
                's_stock_details.week_high_low_max_date'
            )->get();

            if ($watchListItems->isNotEmpty()) {
                $key = str_replace([' ', '-'], '_', $watchList->watchlist_name);
                $watchListList[$key] = [
                    'name' => $watchList->watchlist_name,
                    'stock_list' => $watchListItems,
                ];
            }
        endforeach;
        
        $stock_list = StockSymbol::whereHas('watchlistItems', function ($q) {
                $q->where('is_active', true);
            })
            ->with('details')
            // ->limit(5)
            ->get();
        return view('my_watchlist', compact('watchListList','stock_list', 'stock_name'));
    }

    public static function sameMonthYear($passDate)
    {
        $date = Carbon::parse($passDate);

        return $date->isSameMonth(now()) && $date->isSameYear(now());
    }

    public static function viewAllIndex()
    {
        list($start, $end) = (new NSEStockController)->getDateRange(15);
        DB::enableQueryLog();
        $prices = NseIndexDayRecord::whereBetween('trade_date', [$start, $end])->get();

        $grouped = $prices->groupBy('index_symbol');
        $dates = $prices->unique('trade_date')->pluck('trade_date')->toArray();

        $indexData = [];
        foreach ($grouped as $symbol => $records) {
            $row = ['index_symbol' => $symbol];

            foreach ($records as $rec) {
                $rec->value_last = $rec->value_last ?? 0;
                $rec->value_change = $rec->value_change ?? 0;
                $rec->value_p_change = $rec->value_p_change ?? 0;
                $rec->value_open = $rec->value_open ?? 0;

                $row[$rec->trade_date]['value_last'] = round($rec->value_last, 2);
                $row[$rec->trade_date]['value_change'] = round($rec->value_change, 2);
                $row[$rec->trade_date]['value_p_change'] = round($rec->value_p_change, 2);
                $row[$rec->trade_date]['value_open'] = round($rec->value_open, 2);
            }
            $indexData[] = $row;
        }

        return view('view_all_index', compact('dates', 'indexData'));
    }

    public function todayStock(){
        $today = (new NSEStockController())->today();
        $todayAddedStock = StockSymbol::whereDate('created_at', $today)->orderby('symbol', 'asc')->get();
        $currentHour = now()->hour;

        $todayMissedStock = DB::table('s_stock_symbols as sss')
            ->whereNotIn('sss.symbol', function ($query) use ($today, $currentHour) {
                $query->select('symbol')
                    ->from('s_stock_daily_price_data')
                    ->where(function ($q) use ($today, $currentHour) {

                // main condition
                        $q->whereDate('date', $today);

                        // add OR condition only after 4 PM
                        if ($currentHour > 15) {
                            $q->WhereRaw('hour(time(updated_at))  > 15');
                        }
                    });
            })
            ->where('is_active', true)
            ->get();

        list($start, $end) = (new NSEStockController)->getDateRange(5);

        $recentAddedStock = StockSymbol::where('is_active', true)
            ->whereBetween('created_at', [$start, $end])
            ->orderby('created_at', 'desc')
            ->orderby('symbol', 'asc')
            ->get();

        $recentSuspendedStock = StockDetails::where('trading_status', 'Suspended')
            ->whereHas('symbol', function ($q) {
                $q->where('is_active', true);
            })
            ->where('is_active', true)
            ->orderby('last_update_time', 'asc')
            ->get();
        
        return view('today-stock',
            compact(
                'todayAddedStock',
                'todayMissedStock',
                'recentAddedStock',
                'today',
                'recentSuspendedStock',
            )
        );
    }

    public function oneDayIndex(Request $request)
    {
        $indexName = $request->input('index_name');
        $sort_by = $request->input('sort_by');
        $today = (new NSEStockController())->today();
        $record_date = $today;

        $sort_by_column = match($sort_by) {
            'top_gain_asc' => [
                'column' => 'value_p_change',
                'order' => 'asc',
                'where' => 'value_p_change > 0',
            ],
            'top_gain_desc' => [
                'column' => 'value_p_change',
                'order' => 'desc',
                'where' => 'value_p_change > 0',
            ],
            'top_lose_asc' => [
                'column' => 'value_p_change',
                'order' => 'asc',
                'where' => self::VALUE_P_CHANGE_LESS_THAN,
            ],
            'top_lose_desc' => [
                'column' => 'value_p_change',
                'order' => 'desc',
                'where' => self::VALUE_P_CHANGE_LESS_THAN,
            ],
            'top_gain_price_asc' => [
                'column' => 'value_change',
                'order' => 'asc',
                'where' => 'value_p_change > 0',
            ],
            'top_gain_price_desc' => [
                'column' => 'value_change',
                'order' => 'desc',
                'where' => 'value_p_change > 0',
            ],
            'top_lose_price_asc' => [
                'column' => 'value_change',
                'order' => 'asc',
                'where' => self::VALUE_P_CHANGE_LESS_THAN,
            ],
            'top_lose_price_desc' => [
                'column' => 'value_change',
                'order' => 'desc',
                'where' => self::VALUE_P_CHANGE_LESS_THAN,
            ],
            default => [
                'column' => 'id',
                'order' => 'asc',
                'where' => null,
            ],
        };

        $day_records = NseIndexDayRecord::where('trade_date', $today)
        ->whereRaw($sort_by_column['where'] ?? '1=1')
            // ->groupBy('index_symbol', 's_stock_details.company_name', 's_stock_daily_price_data.date')
            // ->orderBy('s_stock_daily_price_data.symbol')
            // ->orderBy('s_stock_daily_price_data.date')
            // ->orderBy('s_stock_daily_price_data.p_change', 'desc')
            ->orderBy($sort_by_column['column'], $sort_by_column['order']);
        if(!empty($indexName)):
            $day_records = $day_records->where('index_symbol', 'like', '%'.$indexName.'%');
        endif;

        $day_records = $day_records->get();
        return view('one_day_index', compact('day_records', 'record_date'));
    }

    public function lastFewDays()
    {
        $days = 5; // number of consecutive days
        $today = (new NSEStockController())->today();
        list($startDate, $endDate) = (new NSEStockController)->getDateRange($days);
        DB::enableQueryLog();
        $gainerQuery = DB::table('s_stock_daily_price_data')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->select('s_stock_symbols.symbol')
            ->where('s_stock_symbols.is_active', true)
            ->whereBetween('s_stock_daily_price_data.date', [$startDate, $endDate])
            ->groupBy('symbol')
            ->havingRaw("SUM(CASE WHEN s_stock_daily_price_data.last_price > s_stock_daily_price_data.previous_close THEN 1 ELSE 0 END) = ?", [$days])
            ->pluck('symbol');
            
        $gainerData = StockDailyPriceData::whereIn('symbol',$gainerQuery)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderby('symbol', 'asc')
            ->orderby('date', 'asc')
            ->get();
        $allGainerData = $gainerData->flatten(1); // flatten 1 level

        // Get only the dates
        $lastFewGainerDates = $allGainerData->pluck('date')->unique()->values();

        $lastFewDaysGainer = $gainerData->groupBy('symbol')
            ->map(function ($symbolGroup) {
                return $symbolGroup->keyBy('date');
            });

        $loserQuery = DB::table('s_stock_daily_price_data')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->select('s_stock_symbols.symbol')
            ->where('s_stock_symbols.is_active', true)
            ->whereBetween('s_stock_daily_price_data.date', [$startDate, $endDate])
            ->groupBy('symbol')
            ->havingRaw("SUM(CASE WHEN s_stock_daily_price_data.last_price < s_stock_daily_price_data.previous_close THEN 1 ELSE 0 END) = ?", [$days])
            ->pluck('symbol');

        $loserData = StockDailyPriceData::whereIn('symbol',$loserQuery)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderby('symbol', 'asc')
            ->orderby('date', 'asc')
            ->get();
        $allLoserData = $loserData->flatten(1); // flatten 1 level

        // Get only the dates
        $lastFewLoserDates = $allLoserData->pluck('date')->unique()->values();

        $lastFewDaysLoser = $loserData->groupBy('symbol')
            ->map(function ($symbolGroup) {
                return $symbolGroup->keyBy('date');
            });

        $todayHitUpperCP = DB::table('s_stock_daily_price_data')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_symbols.symbol', '=', 's_stock_details.symbol')
            ->where('s_stock_daily_price_data.date', $today)
            ->where('s_stock_symbols.is_active', true)
            ->whereRaw('s_stock_daily_price_data.last_price = s_stock_daily_price_data.upper_cp')
            ->orderby('s_stock_symbols.symbol', 'asc')
            ->get();
            
        $todayHitLowerCP = DB::table('s_stock_daily_price_data')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_symbols.symbol', '=', 's_stock_details.symbol')
            ->where('s_stock_daily_price_data.date', $today)
            ->where('s_stock_symbols.is_active', true)
            ->whereRaw('s_stock_daily_price_data.last_price = s_stock_daily_price_data.lower_cp')
            ->orderby('s_stock_symbols.symbol', 'asc')
            ->get();
        
        $fewDays = 3; // number of consecutive days
        list($startDate, $endDate) = (new NSEStockController)->getDateRange($fewDays);

        $lastFewDaysUpperCPQuery = DB::table('s_stock_daily_price_data')
            ->select('s_stock_symbols.symbol')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->whereBetween('s_stock_daily_price_data.date', [$startDate, $endDate])
            ->where('s_stock_symbols.is_active', true)
            ->groupBy('symbol')
            ->havingRaw("SUM(CASE WHEN s_stock_daily_price_data.last_price = s_stock_daily_price_data.upper_cp THEN 1 ELSE 0 END) = ?", [$fewDays])
            ->pluck('symbol');

        $lastFewDaysUpperCPData = DB::table('s_stock_daily_price_data')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_symbols.symbol', '=', 's_stock_details.symbol')
            ->whereIn('s_stock_symbols.symbol',$lastFewDaysUpperCPQuery)
            ->whereBetween('s_stock_daily_price_data.date', [$startDate, $endDate])
            ->orderby('s_stock_symbols.symbol', 'asc')
            ->orderby('s_stock_daily_price_data.date', 'asc')
            ->get();
        $lastFewDaysUpperCPDate = $lastFewDaysUpperCPData->flatten(1); // flatten 1 level
        $lastFewDaysUpperCPDate = $lastFewDaysUpperCPDate->pluck('date')->unique()->values();

        $lastFewDaysUpperCP = $lastFewDaysUpperCPData->groupBy('symbol')
            ->map(function ($symbolGroup) {
                return $symbolGroup->keyBy('date');
            });
        
        
        $lastFewDaysLowerCPQuery = DB::table('s_stock_daily_price_data')
            ->select('s_stock_symbols.symbol')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->whereBetween('s_stock_daily_price_data.date', [$startDate, $endDate])
            ->where('s_stock_symbols.is_active', true)
            ->groupBy('symbol')
            ->havingRaw("SUM(CASE WHEN s_stock_daily_price_data.last_price = s_stock_daily_price_data.lower_cp THEN 1 ELSE 0 END) = ?", [$fewDays])
            ->pluck('symbol');

        $lastFewDaysLowerCPData = DB::table('s_stock_daily_price_data')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_symbols.symbol', '=', 's_stock_details.symbol')
            ->whereIn('s_stock_symbols.symbol',$lastFewDaysLowerCPQuery)
            ->whereBetween('s_stock_daily_price_data.date', [$startDate, $endDate])
            ->orderby('s_stock_symbols.symbol', 'asc')
            ->orderby('s_stock_daily_price_data.date', 'asc')
            ->get();
        $lastFewDaysLowerCPDate = $lastFewDaysLowerCPData->flatten(1); // flatten 1 level
        $lastFewDaysLowerCPDate = $lastFewDaysLowerCPDate->pluck('date')->unique()->values();

        $lastFewDaysLowerCP = $lastFewDaysLowerCPData->groupBy('symbol')
            ->map(function ($symbolGroup) {
                return $symbolGroup->keyBy('date');
            });
        
        return view('last_few_days_stock',
            compact(
                'today',
                'lastFewDaysGainer',
                'lastFewGainerDates',
                'lastFewDaysLoser',
                'lastFewLoserDates',
                'todayHitUpperCP',
                'todayHitLowerCP',
                'lastFewDaysUpperCP',
                'lastFewDaysUpperCPDate',
                'lastFewDaysLowerCP',
                'lastFewDaysLowerCPDate',
            )
        );
    }

    public function stockPriceList(Request $request)
    {
        $stock_name = $request->get('stock_name') ?? null;
        $today = (new NSEStockController())->today();
        $price_min = $request->get('price_min') ?? null;
        $price_max = $request->get('price_max') ?? null;

        $defaultWatchListNames = [
            [
                'key_name' => 'price_0_0_5',
                'name' => 'Price 0-0.5',
                'condition' => 's_stock_daily_price_data.last_price > 0 AND s_stock_daily_price_data.last_price <= 0.5',
            ],
            [
                'key_name' => 'price_0_5_1',
                'name' => 'Price 0.5-1',
                'condition' => 's_stock_daily_price_data.last_price >= 0.5 AND s_stock_daily_price_data.last_price <= 1',
            ],
            [
                'key_name' => 'price_1_5',
                'name' => 'Price 1-5',
                'condition' => 's_stock_daily_price_data.last_price >= 1 AND s_stock_daily_price_data.last_price <= 5',
            ],
            [
                'key_name' => 'price_5_10',
                'name' => 'Price 5-10',
                'condition' => 's_stock_daily_price_data.last_price >= 5 AND s_stock_daily_price_data.last_price <= 10',
            ],
            [
                'key_name' => 'price_10_20',
                'name' => 'Price 10-20',
                'condition' => 's_stock_daily_price_data.last_price >= 10 AND s_stock_daily_price_data.last_price <= 20',
            ],
            [
                'key_name' => 'price_20_50',
                'name' => 'Price 20-50',
                'condition' => 's_stock_daily_price_data.last_price >= 20 AND s_stock_daily_price_data.last_price <= 50',
            ],
            [
                'key_name' => 'price_50_100',
                'name' => 'Price 50-100',
                'condition' => 's_stock_daily_price_data.last_price >= 50 AND s_stock_daily_price_data.last_price <= 100',
            ],
            [
                'key_name' => 'price_100_200',
                'name' => 'Price 100-200',
                'condition' => 's_stock_daily_price_data.last_price >= 100 AND s_stock_daily_price_data.last_price <= 200',
            ],
            [
                'key_name' => 'price_200_500',
                'name' => 'Price 200-500',
                'condition' => 's_stock_daily_price_data.last_price >= 200 AND s_stock_daily_price_data.last_price <= 500',
            ],
            [
                'key_name' => 'price_500_1000',
                'name' => 'Price 500-1000',
                'condition' => 's_stock_daily_price_data.last_price >= 500 AND s_stock_daily_price_data.last_price <= 1000',
            ],
            [
                'key_name' => 'price_1k_1_5k',
                'name' => 'Price 1000-1500',
                'condition' => 's_stock_daily_price_data.last_price >= 1000 AND s_stock_daily_price_data.last_price <= 1500',
            ],
            [
                'key_name' => 'price_1_5k_2k',
                'name' => 'Price 1500-2000',
                'condition' => 's_stock_daily_price_data.last_price >= 1500 AND s_stock_daily_price_data.last_price <= 2000',
            ],
            [
                'key_name' => 'price_2k_5k',
                'name' => 'Price 2000-5000',
                'condition' => 's_stock_daily_price_data.last_price >= 2000 AND s_stock_daily_price_data.last_price <= 5000',
            ],
            [
                'key_name' => 'price_5k_10k',
                'name' => 'Price 5000-10000',
                'condition' => 's_stock_daily_price_data.last_price >= 5000 AND s_stock_daily_price_data.last_price <= 10000',
            ],
            [
                'key_name' => 'price_10000_more',
                'name' => 'Price 10000 More',
                'condition' => 's_stock_daily_price_data.last_price > 10000',
            ]
        ];
        $watchListList = [];

        foreach($defaultWatchListNames as $defaultWatchList):
            $query = DB::table('s_stock_symbols')
                ->join('s_stock_daily_price_data', 's_stock_daily_price_data.symbol', '=', 's_stock_symbols.symbol')
                ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
                ->where('s_stock_daily_price_data.date', $today)
                ->where('s_stock_symbols.is_active', true)
                ->whereRaw($defaultWatchList['condition']); // keep this if it's dynamic

            if (!empty($stock_name)) {
                $query->where('s_stock_symbols.symbol', $stock_name);
            } elseif (!empty($price_min) && !empty($price_max)) {
                $query->whereBetween('s_stock_daily_price_data.last_price', [$price_min, $price_max]);
            }

            $stockList = $query
                ->select(
                    's_stock_symbols.symbol',
                    's_stock_details.company_name',
                    's_stock_daily_price_data.last_price',
                    's_stock_daily_price_data.change',
                    's_stock_daily_price_data.p_change',
                    's_stock_daily_price_data.previous_close',
                    's_stock_daily_price_data.open',
                    's_stock_daily_price_data.close',
                    's_stock_daily_price_data.lower_cp',
                    's_stock_daily_price_data.upper_cp',
                    's_stock_daily_price_data.intra_day_high_low_min',
                    's_stock_daily_price_data.intra_day_high_low_max',
                    's_stock_details.week_high_low_min',
                    's_stock_details.week_high_low_min_date',
                    's_stock_details.week_high_low_max',
                    's_stock_details.week_high_low_max_date'
                )
                ->orderBy('s_stock_daily_price_data.last_price')
                ->orderBy('s_stock_daily_price_data.p_change')
                ->get();

            if ($stockList->isNotEmpty()) {
                $watchListList[$defaultWatchList['key_name']] = [
                    'name' => $defaultWatchList['name'],
                    'stock_list' => $stockList,
                ];
            }
        endforeach;

        $stock_list = StockSymbol::with('details')->where('is_active', true)->get();
        return view('my_watchlist', compact('watchListList','stock_list', 'stock_name'));
    }
    
    public function nseIndexStockList(Request $request)
    {
        $stock_name = $request->get('stock_name') ?? null;
        $today = (new NSEStockController())->today();
        $price_min = $request->get('price_min') ?? null;
        $price_max = $request->get('price_max') ?? null;

        $sectorIDData = StockDetails::select('pdsectorind')
            // ->where('pdsectorind', '<>', 'NA')
            ->groupBy('pdsectorind')
            ->orderBy('pdsectorind', 'asc')
            ->get();
        $watchListList = [];
        $stockConditions = '1=1';
        if($stock_name != null){
            $stockConditions = "s_stock_symbols.symbol = '".$stock_name."'";
        } elseif (!empty($price_min) && !empty($price_max)) {
            $stockConditions = "s_stock_daily_price_data.last_price between '".$price_min."' and '".$price_max."'";
        }

        foreach($sectorIDData as $sectorID){
            $sectorIDstocks = DB::table('s_stock_details')
                ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_details.symbol')
                ->join('s_stock_daily_price_data', 's_stock_daily_price_data.symbol', '=', 's_stock_symbols.symbol')
                ->where('s_stock_daily_price_data.date', $today)
                ->where('s_stock_symbols.is_active', true)
                ->where('s_stock_details.pdsectorind', $sectorID->pdsectorind)
                ->whereRaw($stockConditions)
                ->select(
                    's_stock_details.symbol',
                    's_stock_details.company_name',
                    's_stock_daily_price_data.last_price',
                    's_stock_daily_price_data.change',
                    's_stock_daily_price_data.p_change',
                    's_stock_daily_price_data.previous_close',
                    's_stock_daily_price_data.open',
                    's_stock_daily_price_data.close',
                    's_stock_daily_price_data.lower_cp',
                    's_stock_daily_price_data.upper_cp',
                    's_stock_daily_price_data.intra_day_high_low_min',
                    's_stock_daily_price_data.intra_day_high_low_max',
                    's_stock_details.week_high_low_min',
                    's_stock_details.week_high_low_min_date',
                    's_stock_details.week_high_low_max',
                    's_stock_details.week_high_low_max_date'
                )
                ->get();
            if(count($sectorIDstocks)>0){
                $watchListList[str_replace([' ', '-','&'], '_', $sectorID->pdsectorind)]['name'] = $sectorID->pdsectorind;
                $watchListList[str_replace([' ', '-','&'], '_', $sectorID->pdsectorind)]['stock_list'] = $sectorIDstocks;
            }
        }

        $stock_list = StockSymbol::with('details')->where('is_active', true)->get();
        return view('my_watchlist', compact('watchListList','stock_list', 'stock_name'));
    }

    public function sectorStockList(Request $request)
    {
        $stock_name = $request->get('stock_name') ?? null;
        $today = (new NSEStockController())->today();
        $price_min = $request->get('price_min') ?? null;
        $price_max = $request->get('price_max') ?? null;

        $sectorData = StockDetails::select('sector')
            // ->where('sector', '<>', '')
            ->groupBy('sector')
            ->orderBy('sector', 'asc')
            ->get();
        $watchListList = [];
        $stockConditions = '1=1';
        if($stock_name != null){
            $stockConditions = "s_stock_symbols.symbol = '".$stock_name."'";
        } elseif (!empty($price_min) && !empty($price_max)) {
            $stockConditions = "s_stock_daily_price_data.last_price between '".$price_min."' and '".$price_max."'";
        }

        foreach($sectorData as $sector){
            $sectorStocks = DB::table('s_stock_details')
                ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_details.symbol')
                ->join('s_stock_daily_price_data', 's_stock_daily_price_data.symbol', '=', 's_stock_symbols.symbol')
                ->where('s_stock_daily_price_data.date', $today)
                ->where('s_stock_symbols.is_active', true)
                ->where('s_stock_details.sector', $sector->sector)
                ->whereRaw($stockConditions)
                ->select(
                    's_stock_details.symbol',
                    's_stock_details.company_name',
                    's_stock_daily_price_data.last_price',
                    's_stock_daily_price_data.change',
                    's_stock_daily_price_data.p_change',
                    's_stock_daily_price_data.previous_close',
                    's_stock_daily_price_data.open',
                    's_stock_daily_price_data.close',
                    's_stock_daily_price_data.lower_cp',
                    's_stock_daily_price_data.upper_cp',
                    's_stock_daily_price_data.intra_day_high_low_min',
                    's_stock_daily_price_data.intra_day_high_low_max',
                    's_stock_details.week_high_low_min',
                    's_stock_details.week_high_low_min_date',
                    's_stock_details.week_high_low_max',
                    's_stock_details.week_high_low_max_date'
                )
                ->get();
            if(count($sectorStocks)>0){
                $watchListList[str_replace([' ', '-', '&'], '_', $sector->sector)]['name'] = $sector->sector;
                $watchListList[str_replace([' ', '-', '&'], '_', $sector->sector)]['stock_list'] = $sectorStocks;
            }
        }

        $stock_list = StockSymbol::with('details')->where('is_active', true)->get();
        return view('my_watchlist', compact('watchListList','stock_list', 'stock_name'));
    }

    public function indexDetailView(Request $request)
    {
        $index_name = $request->get('index_name') ?? null;

        $nseIndexDataRecords = NseIndexDayRecord::where('is_active', true)
            ->where('index_symbol', $index_name)
            ->orderby('trade_date', 'desc')
            ->get();
        
        $todayDate = (new NSEStockController())->today();

        $stockDetailsRecords = "";
        $stock_list = [];
        $stockDailyPriceRecords = StockDailyPriceData::where('date', $todayDate)
            // ->whereHas('pd_sector_ind_all', $index_name)
            ->whereRaw("CONCAT(',', pd_sector_ind_all, ',') LIKE ?", ["%,$index_name,%"])
            // ->whereRaw("FIND_IN_SET(?, REPLACE(pd_sector_ind_all, ' ', ''))", [$index_name])
            ->get();
        return view('index_detail_view',
            compact(
                'index_name',
                'nseIndexDataRecords',
                'stock_list',
                'stockDetailsRecords',
                'stockDailyPriceRecords'
            )
        );
    }

    public function inActiveSymbolWeb($symbol)
    {
        $this->inActiveSymbol($symbol);
        return redirect()->back();
    }

    public function inActiveSymbol($symbol)
    {
        $response = new stdClass();
        $response->result = false;
        $response->msg = "";

        try{
            $stockSymbol = StockSymbol::where('symbol', $symbol)->first();
            if($stockSymbol){
                $stockSymbol->is_active = false;
                $stockSymbol->save();
                $response->result = true;
                $response->msg = "Successfully De-Activated";
            } else {
                $response->msg = "Invalid Stock Symbol";
            }
        } catch(Exception $e){
            $response->msg = $e;
        }

        return response()->json($response);
    }

    public function modifyStock($oldSymbol,$newSymbol)
    {
        $response = new stdClass();
        $response->result = false;
        $response->msg = "";

        try{
            $stockSymbol = StockSymbol::where('symbol', $oldSymbol)->where('is_active', true)->first();
            if($stockSymbol){
                StockDailyPriceData::where('symbol', $oldSymbol)->update(['symbol'=> $newSymbol]);
                $stockSymbol->is_active = false;
                $stockSymbol->save();
                $response->result = true;
                $response->msg = "Successfully modified";
            } else {
                $response->msg = "Invalid Stock Symbol";
            }
        } catch(Exception $e){
            $response->msg = $e;
        }

        return response()->json($response);
    }

    public function checkSuspendedStock()
    {
        $response = new stdClass();
        $response->result = false;
        $response->msg = "";

        return response()->json($response);
    }

    public function checkStock()
    {
        $stocks = DB::table('s_stock_symbols_new as sssn')
            ->whereNotIn('sssn.symbol', function ($query) {
                $query->select('symbol')
                    ->from('s_stock_symbols');
            })->skip(290)->take(20)
            ->get();
        foreach($stocks as $stock){
            $data = (new NSEStockController())->equity($stock->symbol)->getData(true);
            try{
                DB::table('s_stock_symbols_new as sssn')->where('sssn.symbol',$stock->symbol)->update(['indus'=>$data['info']['industry']]);
            } catch(Exception $e) {
                echo $stock->symbol;
                continue;
            }
            dd($data['info']['industry']);
        }
    }
}
