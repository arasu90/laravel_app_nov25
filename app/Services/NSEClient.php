<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class NSEClient
{
    protected $baseUrl = 'https://www.nseindia.com/api';
    protected $cookies = null;

    /**
     * Common headers for NSE requests
     */
    protected function getHeaders()
    {
        return [
            'User-Agent'        => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept'            => 'application/json, text/plain, */*',
            'Accept-Language'   => 'en-US,en;q=0.9',
            'Referer'           => 'https://www.nseindia.com/',
            'Origin'            => 'https://www.nseindia.com',
            'Connection'        => 'keep-alive',
        ];
    }

    /**
     * Warm-up request — NSE requires cookies
     */
    protected function getCookies()
    {
        if ($this->cookies) {
            return $this->cookies;
        }

        $response = Http::withHeaders($this->getHeaders())
            ->withOptions([
                'verify' => false,
                'version' => CURL_HTTP_VERSION_1_1,
            ])
            ->get('https://www.nseindia.com');

        $this->cookies = $response->cookies()->toArray();

        return $this->cookies;
    }

    /**
     * Centralized NSE request handler (with cookies + retry)
     */
    protected function request($url)
    {
        $cookies = $this->getCookies();

        $response = Http::retry(3, 300)
            ->withHeaders($this->getHeaders())
            ->withCookies($cookies, 'www.nseindia.com')
            ->withOptions([
                'verify' => false,
                'version' => CURL_HTTP_VERSION_1_1,
                'curl' => [
                    CURLOPT_ENCODING => 'gzip',
                ],
            ])
            ->get($url);

        return $response->ok() ? $response->json() : null;
    }


    /* ===============================
        PUBLIC API WRAPPER FUNCTIONS
       =============================== */

    public function getMarketStatus()
    {
        return $this->request($this->baseUrl . '/marketStatus');
    }

    public function getEquityDetails($symbol)
    {
        $symbol = urlencode(strtoupper($symbol));
        return $this->request($this->baseUrl . "/quote-equity?symbol={$symbol}");
    }

    public function getEquityHistoricalData($symbol, $from, $to)
    {
        $symbol = strtoupper($symbol);
        $url = $this->baseUrl . "/historical/cm/equity?symbol={$symbol}&series=[EQ]&from={$from}&to={$to}";
        return $this->request($url);
    }

    public function getIndices()
    {
        return $this->request($this->baseUrl . '/allIndices');
    }

    public function getAllStockSymbol()
    {
        return $this->request($this->baseUrl . '/market-data-pre-open?key=ALL');
    }

    public function getMarketHolidays($type='trading')
    {
        return $this->request($this->baseUrl . "/holiday-master?type={$type}");
    }

    public function getCorporateInfo($symbol)
    {
        $symbol = strtoupper($symbol);
        return $this->request($this->baseUrl . "/top-corp-info?symbol={$symbol}&market=equities");
    }

    public function today()
    {
        return Carbon::now()->format('d-m-Y');
    }

    public function dateNDaysAgo($n)
    {
        return Carbon::now()->subDays($n)->format('d-m-Y');
    }
}
