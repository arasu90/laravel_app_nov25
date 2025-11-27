<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function stockListTableView()
    {
        $end = now()->format('Y-m-d');              // today
        $start = now()->subMonth()->format('Y-m-d'); // 1 month back
        DB::enableQueryLog();
        // 1️⃣ Fetch price data for selected month
        $prices = DB::table('s_stock_daily_price_data')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
            ->whereBetween('s_stock_daily_price_data.date', [$start, $end])
            // ->where('s_stock_symbols.symbol', '3MINDIA')
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

        // $prices = DB::getQueryLog();
        // echo "<pre>";
        // print_r($prices);
        // echo "</pre>";
        // exit;
        // $dates = [];
        // $period = new \DatePeriod(
        //     new \DateTime($start),
        //     new \DateInterval('P1D'),
        //     (new \DateTime($end))->modify('+1 day')
        // );
        // foreach ($period as $dt) {
        //     $dates[] = $dt->format("Y-m-d");
        // }
        // 3️⃣ Transform into pivot data
        $grouped = $prices->groupBy('symbol');
        $dates =  $prices->unique('date')->pluck('date')->toArray();
        // echo "<pre>";
        // print_r($dates);
        // echo "</pre>";
        // exit();
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
            // break;
        }
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        // exit;
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
        $day_records = DB::table('s_stock_daily_price_data')
            ->where('date', now()->format('Y-m-d'))
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
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
        // echo "<pre>";
        // print_r($day_records);
        // echo "</pre>";
        // exit;
        return view('one_day_view', compact('day_records'));
    }
}
