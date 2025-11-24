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
}
