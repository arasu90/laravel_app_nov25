<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NSEClient;

class StockController extends Controller
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
}
