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
        $now = now();

        if ($now->isSaturday() || $now->isSunday()) {
            $date = $now->previous(Carbon::FRIDAY);

        } elseif ($now->isMonday() && $now->hour < 10) {
            // Always Friday
            $date = $now->previous(Carbon::FRIDAY);

        } elseif ($now->hour < 10) {
            // Weekday before 10 AM → previous working day
            $date = $now->previousWeekday();

        } else {
            // Weekday after 10 AM → today
            $date = $now;
        }

        return $date->format('Y-m-d');
        // return date("Y-m-11");
    }

    public function todayDateTime()
    {
        $date = now()->isWeekend()
            ? now()->previous(Carbon::FRIDAY)
            : now();

        $finalDateTime = $date->format('Y-m-d H:i:s');
        return $finalDateTime;
    }

    public function getEquityMaster()
    {
        $data = $this->nse->getEquityMaster();
        return $data ? response()->json($data) : response()->json(['error' => 'Data not found'], 404);
    }

    public function getCircular($isLatest=false)
    {
        $data = $this->nse->getCircular($isLatest);
        return $data ? response()->json($data) : response()->json(['error' => 'Data not found'], 404);
    }

    public function getHistoricalData($symbol)
    {
        $data = $this->nse->getHistoricalData($symbol);
        return $data ? response()->json($data) : response()->json(['error' => 'Data not found'], 404);
    }
    
    public function getHistoricalDataIndex($symbol)
    {
        $data = $this->nse->getHistoricalDataIndex($symbol);
        return $data ? response()->json($data) : response()->json(['error' => 'Data not found'], 404);
    }
}
