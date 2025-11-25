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

    public function dataTableView()
    {
        return view('table_data_table');
    }
}
