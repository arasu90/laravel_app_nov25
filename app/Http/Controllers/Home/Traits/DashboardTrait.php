<?php

namespace App\Http\Controllers\Home\Traits;

use Illuminate\Support\Facades\DB;
use App\Models\StockSymbol;
use App\Models\NseIndexDayRecord;
use App\Http\Controllers\NSEStockController;

trait DashboardTrait
{
    public function index()
    {
        $today = (new NSEStockController())->today();

        return view('home', [
            'totalStocks'      => StockSymbol::where('is_active', true)->count(),
            'topGainerPer'     => $this->topGainerList('percentage'),
            'topGainerChange'  => $this->topGainerList('price'),
            'topLooserPer'     => $this->topLooserList('percentage'),
            'topLooserChange'  => $this->topLooserList('price'),
            'week52High'       => $this->week52HighLow('high'),
            'week52Low'        => $this->week52HighLow('low'),
            'nifty50_index'    => NseIndexDayRecord::where('trade_date', $today)->where('index_symbol', 'NIFTY 50')->first(),
            'index_vix'        => NseIndexDayRecord::where('trade_date', $today)->where('index_symbol', 'INDIA VIX')->first(),
        ]);
    }

    public function topGainerList(string $type)
    {
        $today = (new NSEStockController())->today();
        $topGainerList = DB::table('s_stock_daily_price_data')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
            ->where('s_stock_daily_price_data.date', $today)
            ->where('s_stock_symbols.is_active', true)
            ->select(
                's_stock_daily_price_data.symbol',
                's_stock_details.company_name',
                's_stock_daily_price_data.date',
                's_stock_daily_price_data.last_price',
                's_stock_daily_price_data.change',
                's_stock_daily_price_data.p_change'
            )
            ->groupBy('s_stock_daily_price_data.symbol', 's_stock_details.company_name', 's_stock_daily_price_data.date');
            if($type == 'price')
            {
                $topGainerList = $topGainerList->orderBy('s_stock_daily_price_data.change', 'desc');
            } else {
                $topGainerList = $topGainerList->orderBy('s_stock_daily_price_data.p_change', 'desc');
            }
            
            $topGainerList = $topGainerList->limit(10)
            ->get();
        return $topGainerList;
    }

    public function topLooserList(string $type)
    {
        $today = (new NSEStockController())->today();
        $topGainerList = DB::table('s_stock_daily_price_data')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
            ->where('s_stock_daily_price_data.date', $today)
            ->where('s_stock_symbols.is_active', true)
            ->select(
                's_stock_daily_price_data.symbol',
                's_stock_details.company_name',
                's_stock_daily_price_data.date',
                's_stock_daily_price_data.last_price',
                's_stock_daily_price_data.change',
                's_stock_daily_price_data.p_change'
            )
            ->groupBy('s_stock_daily_price_data.symbol', 's_stock_details.company_name', 's_stock_daily_price_data.date');
            if($type == 'price')
            {
                $topGainerList = $topGainerList->orderBy('s_stock_daily_price_data.change', 'asc');
            } else {
                $topGainerList = $topGainerList->orderBy('s_stock_daily_price_data.p_change', 'asc');
            }
            
            $topGainerList = $topGainerList->limit(10)
            ->get();
        return $topGainerList;
    }

    public function week52HighLow(string $type)
    {

        /* WITH latest_price AS (
            SELECT dp.symbol, dp.last_price, dp.change, dp.p_change, dp.date
            FROM s_stock_daily_price_data dp
            INNER JOIN (
                -- Get the latest date per symbol
                SELECT symbol, MAX(`date`) AS latest_date
                FROM s_stock_daily_price_data
                GROUP BY symbol
            ) AS ld
            ON dp.symbol = ld.symbol AND dp.date = ld.latest_date
        )

        SELECT
            lp.symbol,
            sd.company_name,
            lp.last_price,
            lp.change,
            lp.p_change,
            sd.week_high_low_min,
            sd.week_high_low_min_date,
            sd.week_high_low_max,
            sd.week_high_low_max_date,
            CASE
                WHEN lp.last_price = sd.week_high_low_max THEN 1 ELSE 0
            END AS is_at_52week
        FROM latest_price lp
        INNER JOIN s_stock_symbols ss
            ON ss.symbol = lp.symbol
        INNER JOIN s_stock_details sd
            ON sd.symbol = lp.symbol
        WHERE ss.is_active = 1
        ORDER BY sd.week_high_low_max_date DESC, is_at_52week DESC
        */
        $orderColumn = $type === 'low'
            ? 'week_high_low_min_date'
            : 'week_high_low_max_date';

        $weekColumn = $type === 'low'
            ? 'week_high_low_min'
            : 'week_high_low_max';

        // Step 1: Get the latest daily price per symbol
        $latestPrices = DB::table('s_stock_daily_price_data as dp')
            ->select('dp.symbol', 'dp.last_price', 'dp.change', 'dp.p_change', 'dp.date')
            ->join(DB::raw('(SELECT symbol, MAX(`date`) as latest_date
                            FROM s_stock_daily_price_data
                            GROUP BY symbol) as ld'),
                function($join) {
                    $join->on('dp.symbol', '=', 'ld.symbol')
                            ->on('dp.date', '=', 'ld.latest_date');
                });

        // Step 2: Join with symbols and stock details
        return DB::table(DB::raw('(' . $latestPrices->toSql() . ') as lp'))
            ->mergeBindings($latestPrices)
            ->join('s_stock_symbols as ss', 'ss.symbol', '=', 'lp.symbol')
            ->join('s_stock_details as sd', 'sd.symbol', '=', 'lp.symbol')
            ->select(
                'lp.symbol',
                'sd.company_name',
                'lp.last_price',
                'lp.change',
                'lp.p_change',
                'sd.week_high_low_min',
                'sd.week_high_low_min_date',
                'sd.week_high_low_max',
                'sd.week_high_low_max_date',
                DB::raw("CASE WHEN lp.last_price = sd.$weekColumn THEN 1 ELSE 0 END AS is_at_52week")
            )
            ->where('ss.is_active', 1)
            ->orderBy("sd.$orderColumn", 'desc')
            ->orderBy('is_at_52week', 'desc')
            ->limit(10)
            ->get();
    }
}
