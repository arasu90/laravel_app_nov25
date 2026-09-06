<?php

namespace App\Http\Controllers\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\StockHoliday;
use App\Models\StockSymbol;
use App\Models\StockDailyPriceData;
use App\Http\Controllers\NSEStockController;
use App\Http\Controllers\Traits\ApplicationTrait;

trait UtilityTrait
{
    use ApplicationTrait;
    /* // last tested on 21 Aug 2026 01:48 AM
    public function holidayList()
    {
        $today = (new NSEStockController())->today();
        $currentMonth = date('m', strtotime($today));
        $holidays = StockHoliday::where('year', date('Y'));
        if($currentMonth > 6):
            $holidays = $holidays->orderBy('date', 'desc');
        else:
            $holidays = $holidays->orderBy('date', 'asc');
        endif;
        $holidays = $holidays->get();
        return view('holiday_list', compact('holidays'));
    } */
   /*  // last tested on 21 Aug 2026 01:48 AM
    public function corporateInfo()
    {
        $stock_list = StockSymbol::where('is_active', true)->orderBy('symbol')->get();
        $corporateInfo = DB::table('s_stock_symbols')
            ->join('s_stock_corporate_info', 's_stock_corporate_info.symbol', '=', 's_stock_symbols.symbol')
            ->leftJoin('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
            ->where('s_stock_corporate_info.actions_type', 'corporate_actions')
            ->where('s_stock_symbols.is_active', 1)
            ->select(
                's_stock_symbols.symbol',
                's_stock_details.company_name',
                's_stock_corporate_info.actions_date',
                's_stock_corporate_info.actions_purpose'
            )
            ->orderBy('s_stock_corporate_info.actions_date', 'desc')
            ->limit(25)
            ->get();
        return view('corporate_info', compact('stock_list', 'corporateInfo'));
    } */
    
    // last tested on 06 Sep 2026 11:48 AM
    public function icons()
    {
        return view('app_icons');
    }
    // last tested on 06 Sep 2026 11:48 AM
    public function appUrl()
    {
        $stockData = StockSymbol::where('is_active', true)->orderBy('symbol')->first();
        $url_list = [
            'app_url' => [
                [
                    'title' => 'Get All Stocks & Insert to DB',
                    'app_url' => '/all-stocks',
                    'app_url_data' => '/all-stocks',
                ],
                [
                    'title' => 'Get Holidays & Insert to DB',
                    'app_url' => '/getAndUpdateHolidayList',
                    'app_url_data' => '/getAndUpdateHolidayList',
                ],
                [
                    'title' => 'For Application Available Icon',
                    'app_url' => '/icons',
                    'app_url_data' => '/icons',
                ],
                [
                    'title' => 'Update Stock One Data data',
                    'app_url' => '/update-stock-data/{symbol}',
                    'app_url_data' => '/update-stock-data/'.$stockData->symbol,
                ],
            ],
            'api_url' => [
                [
                    'title' => 'Show all the Stocks',
                    'app_url' => '/api/all-stocks',
                    'app_url_data' => 'api/all-stocks',
                ],
                [
                    'title' => 'Show top Corporate Actions',
                    'app_url' => '/api/corporate-top-actions',
                    'app_url_data' => 'api/corporate-top-actions',
                ],
                [
                    'title' => 'get holiday details',
                    'app_url' => '/api/holidays',
                    'app_url_data' => '/api/holidays',
                ],
                [
                    'title' => 'get stock day data',
                    'app_url' => '/api/stock/{symbol}',
                    'app_url_data' => 'api/stock/'.$stockData->symbol,
                ],
                [
                    'title' => 'Show corporate info for a specific stock',
                    'app_url' => '/api/corporate-info/{symbol}',
                    'app_url_data' => 'api/corporate-info/'.$stockData->symbol,
                ],
            ]
        ];
        return view('app_url', compact('url_list'));
    }

    /* public function paperTrade()
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
            ->where('p.portfolio_type',2)
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

        return view('paper_trade', compact('stock_list', 'myPortfolioStocks'));
    } */

   /*  public static function sameMonthYear($passDate)
    {
        $date = Carbon::parse($passDate);
        return $date->isSameMonth(now()) && $date->isSameYear(now());
    } */

    /* public function dbQuery()
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
            Log::channel('stock_backup')->info($logQuery);
        endforeach;
        return "Data inserted successfully";
    } */
}
