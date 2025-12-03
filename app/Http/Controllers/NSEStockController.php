<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NSEClient;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NSEStockController extends Controller
{
    protected $nse;

    public function __construct()
    {
        $this->nse = new NSEClient();
    }

    // GET /api/market-status
    public function marketStatus()
    {
        return response()->json($this->nse->getMarketStatus());
    }

    // GET /api/stock/{symbol}
    public function equity($symbol)
    {
        $data = $this->nse->getEquityDetails($symbol);
        return $data ? response()->json($data) : response()->json(['error' => 'Symbol not found'], 404);
    }

    // GET /api/stock/{symbol}/historical?from=dd-mm-yyyy&to=dd-mm-yyyy
    public function equityHistorical(Request $request, $symbol)
    {
        $from = $request->query('from', $this->nse->dateNDaysAgo(30)); // default last 30 days
        $to = $request->query('to', $this->nse->today());

        $data = $this->nse->getEquityHistoricalData($symbol, $from, $to);
        return $data ? response()->json($data) : response()->json(['error' => 'Data not found'], 404);
    }

    // GET /api/indices
    public function indices()
    {
        return response()->json($this->nse->getIndices());
    }

    public function test()
    {
        return response()->json(['message' => 'Test endpoint working']);
    }

    public function getAllStocksArray()
    {
        $data = $this->nse->getAllStockSymbol();
        // Log::info($data);

        $symbols = array_map(function($item) {
            return $item['metadata']['symbol'];
        }, $data['data']);
        sort($symbols);
        return $symbols;
    }

     // GET /api/all-stocks

    public function allStocks()
    {
        $symbols = $this->getAllStocksArray();
        return $symbols ? response()->json($symbols) : response()->json(['error' => 'Data not found'], 404);
    }

    public function marketHolidays($type='trading')
    {
        $data = $this->nse->getMarketHolidays($type);
        return $data ? response()->json($data) : response()->json(['error' => 'Data not found'], 404);
    }

    public function corporateInfo($symbol)
    {
        $data = $this->nse->getCorporateInfo($symbol);
        return $data ? response()->json($data) : response()->json(['error' => 'Data not found'], 404);
    }

    public function getIndexNames()
    {
        $data = $this->nse->getIndexNames();
        return $data ? response()->json($data) : response()->json(['error' => 'Data not found'], 404);
    }

    public function today()
    {
        $date = now()->isWeekend()
            ? now()->previous(Carbon::FRIDAY)
            : now();

        $finalDate = $date->format('Y-m-d');
        return $finalDate;
    }

    public function todayDateTime()
    {
        $date = now()->isWeekend()
            ? now()->previous(Carbon::FRIDAY)
            : now();

        $finalDateTime = $date->format('Y-m-d H:i:s');
        return $finalDateTime;
    }
}
