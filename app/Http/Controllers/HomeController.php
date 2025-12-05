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
use App\Models\MyWatchlistItem;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function stockListTableView()
    {
        $end = now()->format('Y-m-d');              // today
        $start = now()->subDays(5)->format('Y-m-d'); // 5 days back
        DB::enableQueryLog();
        $prices = DB::table('s_stock_daily_price_data')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
            ->whereBetween('s_stock_daily_price_data.date', [$start, $end])
            // ->where('s_stock_symbols.symbol', '3MINDIA')
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
            $prevClose = null;

            foreach ($records as $rec) {
                $percent = $rec->last_price;

                $row[$rec->date]['last_price'] = round($percent, 2);
                $row[$rec->date]['change'] = round($rec->change, 2);
                $row[$rec->date]['p_change'] = round($rec->p_change, 2);
                $prevClose = $rec->last_price;
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
        $stockCount = StockDailyPriceData::where('date', $today)->count();
        if($stockCount == 0):
            $today = (new NSEStockController())->today();
            $stockCount = StockDailyPriceData::where('date', $today)->count();
        endif;
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

        $fullStockRecords = StockDailyPriceData::where('date', $today)->with('details')->get();

        return view('one_day_view', compact('day_records', 'record_date', 'fullStockRecords'));
    }

    public function holidayList()
    {
        $today = (new NSEStockController())->today();
        $currentMonth = date('m', strtotime($today));
        $holidays = StockHoliday::where('year', date('Y'));
            // ->where(DB::raw('MONTH(date)'), $currentMonth)
        if($currentMonth > 6):
            $holidays = $holidays->orderBy('date', 'desc');
        else:
            $holidays = $holidays->orderBy('date', 'asc');
        endif;
        $holidays = $holidays->get();
        return view('holiday_list', compact('holidays'));
    }

    public function stockDetailView(Request $request)
    {
        $stock_name = $request->input('stock_name') ?? 'TNTELE';
        $stock_daily_price_data = StockDailyPriceData::where('symbol', $stock_name)
        ->orderBy('date', 'desc')
        ->get();
        $stock_details = StockDetails::where('symbol', $stock_name)->first();
        $stock_list = StockSymbol::with('details')->where('is_active', true)->get();
        return view('stock_detail_view', compact('stock_daily_price_data', 'stock_details', 'stock_list', 'stock_name'));
    }

    public function dbQuery()
    {
        $data = StockDailyPriceData::get();
        foreach($data as $item):
            $symbol = $item->symbol;
            $date = $item->date;
            $last_price = $item->last_price;
            $change = $item->change;
            $p_change = $item->p_change;
            $previous_close = $item->previous_close;
            $open = $item->open;
            $close = $item->close;
            $lower_cp = $item->lower_cp;
            $upper_cp = $item->upper_cp;
            $intra_day_high_low_min = $item->intra_day_high_low_min;
            $intra_day_high_low_max = $item->intra_day_high_low_max;

            $logQuery = "INSERT INTO s_stock_daily_price_data (`symbol`, `date`, `last_price`, `change`, `p_change`, `previous_close`, `open`, `close`, `lower_cp`, `upper_cp`, `intra_day_high_low_min`, `intra_day_high_low_max`) VALUES ('$symbol', '$date', '$last_price', '$change', '$p_change', '$previous_close', '$open', '$close', '$lower_cp', '$upper_cp', '$intra_day_high_low_min', '$intra_day_high_low_max')";
            // Log::info($logQuery);
            Log::channel('stock_backup')->info($logQuery);
            // break;
        endforeach;
        return "Data inserted successfully";
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
        $after_expected_average_price = $expected_average_price;
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
        $stock_list = StockSymbol::with('details')->where('is_active', true)->get();
        // $myPortfolioStocks = MyPortfolioStock::with('stockSymbol')->with('stockDailyPriceData')
        //     ->whereHas('stockSymbol.stockDailyPriceData', function($q) {
        //         $q->where('date', now()->format('Y-m-d'));
        //     })
        //     ->orderBy('buy_date', 'asc')
        //     ->get()
        //     ->groupBy(function($item){
        //         return $item->stockSymbol->symbol; // group by symbol name
        //     });
        // echo "<pre>";
        // print_r($myPortfolioStocks);
        // echo "</pre>";
        // // exit;

        $today = (new NSEStockController())->today();
        // $today = now()->format('Y-m-03');
        $myPortfolioStocks = DB::table('s_portfolio_stocks')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_portfolio_stocks.symbol')
            ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
            ->join('s_stock_daily_price_data', 's_stock_daily_price_data.symbol', '=', 's_portfolio_stocks.symbol')
            ->where('s_stock_daily_price_data.date', $today)
            ->where('s_stock_symbols.is_active', true)
            ->select('s_portfolio_stocks.symbol', 's_stock_details.company_name', 's_portfolio_stocks.buy_price', 's_portfolio_stocks.buy_qty', 's_portfolio_stocks.buy_date', 's_stock_daily_price_data.last_price', 's_stock_daily_price_data.change', 's_stock_daily_price_data.p_change')
            ->groupBy('s_portfolio_stocks.symbol', 's_stock_details.company_name', 's_portfolio_stocks.buy_price', 's_portfolio_stocks.buy_qty', 's_portfolio_stocks.buy_date', 's_stock_daily_price_data.last_price', 's_stock_daily_price_data.change', 's_stock_daily_price_data.p_change')
            ->orderBy('s_portfolio_stocks.symbol', 'asc')
            ->get();

        return view('my_portfolio', compact('stock_list', 'myPortfolioStocks'));
    }

    public function addMyPortfolio(Request $request)
    {
        $buy_qty = $request->input('buy_qty');
        $buy_price = $request->input('buy_price');
        $buy_date = $request->input('buy_date');
        $stock_name = $request->input('stock_name');
        // $stock_daily_price_data = StockDailyPriceData::where('symbol', $stock_name)->orderBy('date', 'desc')->get();
        // $stock_details = StockDetails::where('symbol', $stock_name)->first();
        // $stock_list = StockSymbol::with('details')->get();


        $myPortfolioStock = new MyPortfolioStock();
        $myPortfolioStock->symbol = $stock_name;
        $myPortfolioStock->buy_price = $buy_price;
        $myPortfolioStock->buy_qty = $buy_qty;
        $myPortfolioStock->buy_date = date('Y-m-d', strtotime($buy_date));
        $myPortfolioStock->save();
        // return view('my_portfolio', compact('stock_list', 'stock_daily_price_data', 'stock_details'));
        return redirect()->route('myPortfolio')->with('success', 'Stock added to portfolio successfully');
    }

    public function myWatchlist()
    {
        $stock_list = $stock_daily_price_data = [];
        $stock_name = '';
        $today = (new NSEStockController())->today();

        $watchListMaster = DB::table('s_watchlist_master')->get();
        $defaultWatchListNames = [
            [
                'key_name' => 'price_0_0_5',
                'name' => 'Price 0-0.5',
                'condition' => 's_stock_daily_price_data.last_price > 0 AND s_stock_daily_price_data.last_price < 0.5',
            ],
            [
                'key_name' => 'price_0_5_1',
                'name' => 'Price 0.5-1',
                'condition' => 's_stock_daily_price_data.last_price >= 0.5 AND s_stock_daily_price_data.last_price < 1',
            ],
            [
                'key_name' => 'price_1_5',
                'name' => 'Price 1-5',
                'condition' => 's_stock_daily_price_data.last_price >= 1 AND s_stock_daily_price_data.last_price < 5',
            ],
            [
                'key_name' => 'price_5_10',
                'name' => 'Price 5-10',
                'condition' => 's_stock_daily_price_data.last_price >= 5 AND s_stock_daily_price_data.last_price < 10',
            ],
            [
                'key_name' => 'price_10_20',
                'name' => 'Price 10-20',
                'condition' => 's_stock_daily_price_data.last_price >= 10 AND s_stock_daily_price_data.last_price < 20',
            ],
            [
                'key_name' => 'price_20_50',
                'name' => 'Price 20-50',
                'condition' => 's_stock_daily_price_data.last_price >= 20 AND s_stock_daily_price_data.last_price < 50',
            ],
            [
                'key_name' => 'price_50_100',
                'name' => 'Price 50-100',
                'condition' => 's_stock_daily_price_data.last_price >= 50 AND s_stock_daily_price_data.last_price < 100',
            ],
            [
                'key_name' => 'price_100_200',
                'name' => 'Price 100-200',
                'condition' => 's_stock_daily_price_data.last_price >= 100 AND s_stock_daily_price_data.last_price < 200',
            ],
            [
                'key_name' => 'price_200_500',
                'name' => 'Price 200-500',
                'condition' => 's_stock_daily_price_data.last_price >= 200 AND s_stock_daily_price_data.last_price < 500',
            ],
            [
                'key_name' => 'price_500_1000',
                'name' => 'Price 500-1000',
                'condition' => 's_stock_daily_price_data.last_price >= 500 AND s_stock_daily_price_data.last_price < 1000',
            ],
            [
                'key_name' => 'price_1000_plus',
                'name' => 'Price >1000',
                'condition' => 's_stock_daily_price_data.last_price >= 1000',
            ]
        ];
        $watchListList = [];
        foreach($defaultWatchListNames as $defaultWatchList):
            $stockList = DB::table('s_stock_symbols')
                ->join('s_stock_daily_price_data', 's_stock_daily_price_data.symbol', '=', 's_stock_symbols.symbol')
                ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
                ->where('s_stock_daily_price_data.date', $today)
                ->where('s_stock_symbols.is_active', true)
                ->whereRaw($defaultWatchList['condition'])
                ->select('s_stock_symbols.symbol', 's_stock_details.company_name', 's_stock_daily_price_data.last_price', 's_stock_daily_price_data.change', 's_stock_daily_price_data.p_change', 's_stock_daily_price_data.previous_close', 's_stock_daily_price_data.open', 's_stock_daily_price_data.close', 's_stock_daily_price_data.lower_cp', 's_stock_daily_price_data.upper_cp', 's_stock_daily_price_data.intra_day_high_low_min', 's_stock_daily_price_data.intra_day_high_low_max', 's_stock_details.week_high_low_min', 's_stock_details.week_high_low_min_date', 's_stock_details.week_high_low_max', 's_stock_details.week_high_low_max_date')
                ->orderBy('s_stock_daily_price_data.last_price')
                ->orderBy('s_stock_daily_price_data.p_change')
                ->get();
            $watchListList[$defaultWatchList['key_name']]['name'] = $defaultWatchList['name'];
            $watchListList[$defaultWatchList['key_name']]['stock_list'] = $stockList;
            // echo "<pre>";
            // print_r($watchListList);
            // echo "</pre>";
            // break;
        endforeach;

        $watchListMaster = MyWatchListMaster::get();
        foreach($watchListMaster as $watchList):
            $watchListItems = DB::table('s_watchlist_items')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_watchlist_items.symbol')
            ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
            ->join('s_stock_daily_price_data', 's_stock_daily_price_data.symbol', '=', 's_stock_symbols.symbol')
            ->where('s_stock_daily_price_data.date', $today)
            ->where('s_stock_symbols.is_active', true)
            ->where('s_watchlist_items.watchlist_id', $watchList->id)
            ->select('s_watchlist_items.symbol', 's_stock_details.company_name', 's_stock_daily_price_data.last_price', 's_stock_daily_price_data.change', 's_stock_daily_price_data.p_change', 's_stock_daily_price_data.previous_close', 's_stock_daily_price_data.open', 's_stock_daily_price_data.close', 's_stock_daily_price_data.lower_cp', 's_stock_daily_price_data.upper_cp', 's_stock_daily_price_data.intra_day_high_low_min', 's_stock_daily_price_data.intra_day_high_low_max', 's_stock_details.week_high_low_min', 's_stock_details.week_high_low_min_date', 's_stock_details.week_high_low_max', 's_stock_details.week_high_low_max_date')
            ->get();
            $watchListList[str_replace([' ', '-'], '_', $watchList->watchlist_name)]['name'] = $watchList->watchlist_name;
            $watchListList[str_replace([' ', '-'], '_', $watchList->watchlist_name)]['stock_list'] = $watchListItems;
        endforeach;
        $stock_list = StockSymbol::with('details')->where('is_active', true)->get();
        return view('my_watchlist', compact('watchListList','stock_list'));
    }
}
