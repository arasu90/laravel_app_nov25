<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class NSEClientNew
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

    ############### Not USED ###############
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
    
    ############### Not USED ###############
    
    /**
     * Centralized NSE request handler (with cookies + retry)
     */
    protected function request(string $url)
    {
        return Http::timeout(30)
            ->withHeaders($this->getHeaders())
            ->withCookies($this->getCookies(), 'www.nseindia.com')
            ->get($url)
            ->json();
    }

    public function getAllStockSymbol()
    {
        return $this->request($this->baseUrl . '/market-data-pre-open?key=ALL');
    }

    public function getMarketHolidays(string $type='trading')
    {
        return $this->request($this->baseUrl . "/holiday-master?type={$type}");
    }

    public function getMetaData(string $stockSymbol)
    {
        $encodedSymbol = rawurlencode($stockSymbol);

        return $this->request($this->baseUrl . "/NextApi/apiClient/GetQuoteApi?functionName=getMetaData&symbol={$encodedSymbol}");
    }

    public function getEquityDetails(string $stockSymbol, array $activeSeries, string $marketType)
    {
        $activeSeries = $activeSeries[0] ?? 'EQ'; // Default to 'EQ' if not available
        $encodedSymbol = rawurlencode($stockSymbol);

        return $this->request($this->baseUrl . "/NextApi/apiClient/GetQuoteApi?functionName=getSymbolData&marketType={$marketType}&series={$activeSeries}&symbol={$encodedSymbol}");
    }

    public function getCorporateTopActions()
    {
        return $this->request($this->baseUrl . '/corporates-corporateActions?index=equities');
    }

    public function getCorporateStockInfo(string $stockSymbol)
    {
        $encodedSymbol = rawurlencode($stockSymbol);

        return $this->request($this->baseUrl . "/NextApi/apiClient/GetQuoteApi?functionName=getCorpAction&symbol={$encodedSymbol}&marketApiType=equities&noOfRecords=5");
    }
}
