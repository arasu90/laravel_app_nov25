<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class NSEClient
{
    protected $baseUrl = 'https://www.nseindia.com/api';

    /**
     * Common headers for NSE requests
     */
    protected function getHeaders()
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Referer' => 'https://www.nseindia.com/',
            'Accept' => 'application/json, text/plain, */*',
            'Origin' => 'https://www.nseindia.com',
            'Connection' => 'keep-alive',
        ];
    }

    /**
     * Fetch market status
     */
    public function getMarketStatus()
    {
        $url = $this->baseUrl . '/marketStatus';
        $response = Http::withHeaders($this->getHeaders())->get($url);

        return $response->ok() ? $response->json() : null;
    }

    /**
     * Fetch equity details by symbol
     */
    public function getEquityDetails($symbol)
    {
        $url = $this->baseUrl . '/quote-equity?symbol=' . urlencode(strtoupper($symbol));
        $response = Http::withHeaders($this->getHeaders())->get($url);

        return $response->ok() ? $response->json() : null;
    }

    /**
     * Fetch historical equity data
     * $from and $to format: 'dd-mm-yyyy'
     */
    public function getEquityHistoricalData($symbol, $from, $to)
    {
        $url = $this->baseUrl . "/historical/cm/equity?symbol=" . strtoupper($symbol) .
               "&series=[EQ]&from={$from}&to={$to}";

        $response = Http::withHeaders($this->getHeaders())->get($url);

        return $response->ok() ? $response->json() : null;
    }

    /**
     * Fetch indices data
     */
    public function getIndices()
    {
        $url = $this->baseUrl . '/allIndices';
        $response = Http::withHeaders($this->getHeaders())->get($url);

        return $response->ok() ? $response->json() : null;
    }

    /**
     * Convenience function: today's date in dd-mm-yyyy
     */
    public function today()
    {
        return Carbon::now()->format('d-m-Y');
    }

    /**
     * Convenience function: last N days
     */
    public function dateNDaysAgo($n)
    {
        return Carbon::now()->subDays($n)->format('d-m-Y');
    }

    /**
     * Fetch all stock symbols
     */
    public function getAllStockSymbol()
    {
        $url = $this->baseUrl . '/market-data-pre-open?key=ALL';
        $response = Http::withHeaders($this->getHeaders())->get($url);
        
        return $response->ok() ? $response->json() : null;
    }
    

    /**
     * Fetch market holidays by type
     */
    public function getMarketHolidays($type='trading')
    {
        $url = $this->baseUrl . '/holiday-master?type=' . $type;
        $response = Http::withHeaders($this->getHeaders())->get($url);

        return $response->ok() ? $response->json() : null;
    }

    /**
     * Fetch corporate information by symbol
     */
    public function getCorporateInfo($symbol)
    {
        $url = $this->baseUrl . '/top-corp-info?symbol=' . strtoupper($symbol).'&market=equities';
        $response = Http::withHeaders($this->getHeaders())->get($url);

        return $response->ok() ? $response->json() : null;
    }
}
