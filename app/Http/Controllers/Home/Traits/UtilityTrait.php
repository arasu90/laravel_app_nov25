<?php

namespace App\Http\Controllers\Home\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\StockHoliday;
use App\Models\StockSymbol;
use App\Models\StockDailyPriceData;
use App\Http\Controllers\NSEStockController;

trait UtilityTrait
{
    
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

    public function corporateInfo()
    {
        $stock_list = StockSymbol::where('is_active', true)->orderBy('symbol')->get();
        $corporateInfo = DB::table('s_stock_symbols')
            ->join('s_stock_corporate_info', 's_stock_corporate_info.symbol', '=', 's_stock_symbols.symbol')
            ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
            ->where('s_stock_corporate_info.actions_type', 'corporate_actions')
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
    }
    public function icons()
    {
        return view('app_icons');
    }
    public function appUrl()
    {
        $stock_list = StockSymbol::where('is_active', true)->orderBy('symbol')->get();
        return view('app_url', compact('stock_list'));
    }

    public function paperTrade() { return 'paper trade'; }

    public static function sameMonthYear($passDate)
    {
        $date = Carbon::parse($passDate);
        return $date->isSameMonth(now()) && $date->isSameYear(now());
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
            Log::channel('stock_backup')->info($logQuery);
        endforeach;
        return "Data inserted successfully";
    }
}
