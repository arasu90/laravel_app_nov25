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

        $response = Http::timeout(30) // Increase timeout for cookie request
            ->withHeaders($this->getHeaders())
            ->withOptions([
                'verify' => false,
                'version' => CURL_HTTP_VERSION_1_1,
            ])
            ->get('https://www.nseindia.com');

        $this->cookies = $response->cookies()->toArray();

        return $this->cookies;
    }

    /**
     * Force refresh cookies (clear cached cookies and get new ones)
     */
    protected function refreshCookies()
    {
        $this->cookies = null;
        return $this->getCookies();
    }

    /**
     * Centralized NSE request handler (with cookies + retry)
     */
    protected function request($url)
    {
        $maxAttempts = 3;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;
            $cookies = $this->getCookies();

            // Add a small delay between requests to avoid rate limiting
            usleep(500000); // 500ms delay

            $response = Http::timeout(30)
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

            if ($response->ok()) {
                return $response->json();
            }

            // If 403 Forbidden, refresh cookies and retry
            if ($response->status() === 403) {
                \Illuminate\Support\Facades\Log::warning("NSE 403 error on attempt {$attempt}, refreshing cookies...");
                $this->refreshCookies();
                // Increase delay on 403 to avoid further rate limiting
                sleep(2);
                continue;
            }

            // For other errors, throw exception with details
            if (!$response->ok()) {
                $response->throw();
            }
        }

        return null;
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

    public function getIndexNames()
    {
        return $this->request($this->baseUrl . "/index-names");
    }
}
