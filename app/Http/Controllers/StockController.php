<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockSymbol;
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
}
