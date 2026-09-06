<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Services\NSEClientNew;
use App\Services\HelperServices;
use App\Models\StockHoliday;
use App\Http\Controllers\Traits\ApplicationTrait;
use stdClass;

class NSEStockControllerNew extends Controller
{
    use ApplicationTrait;

    protected const NO_DATA_FOUND = "Data not found";

    protected NSEClientNew $nseClient;

    // last tested on 06 Sep 2026 11:48 AM
    public function __construct(NSEClientNew $nseClient)
    {
        $this->nseClient = $nseClient;
    }

    public function today()
    {
        $date = now();

        if ($date->hour < 10) {
            $date->subDay();
        }

        while ($this->isHolidayOrWeekend($date)) {
            $date->subDay();
        }

        return $date->format('Y-m-d');
    }

    protected function isHolidayOrWeekend(Carbon $date): bool
    {
        return $date->isWeekend()
            || StockHoliday::whereDate('date', $date->toDateString())->exists();
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function getDateRange($numDays = 5)
    {
        $endDate = Carbon::parse($this->today());
        $startDate = $endDate->copy();

        $count = 1;

        while ($count < $numDays) {
            $startDate->subDay();

            if (!$this->isHolidayOrWeekend($startDate)) {
                $count++;
            }
        }

        return [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
        ];
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function getAllStocksArray()
    {
        try {
            $response = $this->nseClient->getAllStockSymbol();

            if (isset($response['data']) && is_array($response['data'])) {
                $symbols = array_map(function ($item) {
                    return $item['metadata']['symbol'] ?? null;
                }, $response['data']);

                // Remove invalid/null symbols
                $symbols = array_filter($symbols);

                // Sort alphabetically
                sort($symbols);

                return $symbols;
            }

            $this->appLog([
                'message' => 'Unexpected response structure from NSE API',
                'response' => $response,
            ], 'error');

            return null;

        } catch (\Exception $e) {
            $this->appLog([
                'message' => 'Error fetching all stock symbols from NSE API',
                'error' => $e->getMessage(),
            ], 'error');

            return null;
        }

    }

    // last tested on 06 Sep 2026 11:48 AM
    public function marketHolidays($type='trading')
    {
        try {
            $response = $this->nseClient->getMarketHolidays($type);

            if (isset($response['CM']) && is_array($response['CM'])) {
                return response()->json(['CM' => $response['CM']]);
            }

            $this->appLog([
                'message' => 'Unexpected response structure from NSE API for market holidays',
                'response' => $response,
            ], 'error');

            return response()->json(['error' => self::NO_DATA_FOUND], 404);

        } catch (\Exception $e) {
            $this->appLog([
                'message' => 'Error fetching market holidays from NSE API',
                'error' => $e->getMessage(),
            ], 'error');

            return response()->json(['error' => self::NO_DATA_FOUND], 404);
        }
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function getStockDetails(string $stockSymbol)
    {
        try {
            $metaData = $this->nseClient->getMetaData($stockSymbol);
            $activeSeries = $metaData['activeSeries'];
            $marketType = $metaData['marketType'];
            $equityDetails = $this->nseClient->getEquityDetails($stockSymbol, $activeSeries, $marketType);
            $generatedStockData = $this->generateStockData($metaData, $equityDetails);
            return $this->toJson($generatedStockData);

        } catch (\Exception $e) {
            $this->appLog([
                'message' => 'Error fetching stock details from NSE API',
                'stockSymbol' => $stockSymbol,
                'error' => $e->getMessage(),
            ], 'error');

            return response()->json(['error' => self::NO_DATA_FOUND], 404);
        }
    }

    protected function generateStockData(array $metaData, array $equityDetails)
    {
        $stockData  = new stdClass();
        $metaDataInfo = new stdClass();
        $metaDataInfo->stockSymbol = $metaData['symbol'] ?? null;
        $metaDataInfo->companyName = $metaData['companyName'] ?? null;
        $metaDataInfo->marketType = $metaData['marketType'] ?? null;
        $metaDataInfo->isin = $metaData['isin'] ?? null;
        $metaDataInfo->activeSeries = implode(',', $metaData['activeSeries'] ?? []);
        $metaDataInfo->isSuspended = $metaData['isSuspended'] ?? null;
        $metaDataInfo->isDelisted = $metaData['isDelisted'] ?? null;
        
        $stockData->metaData = $metaDataInfo;

        $equityMetaData = $equityDetails['equityResponse'][0]['metaData'];
        $securityInfo = $equityDetails['equityResponse'][0]['secInfo'];
        $priceInfo = $equityDetails['equityResponse'][0]['priceInfo'];
        $tradeInfo = $equityDetails['equityResponse'][0]['tradeInfo'];
        $lastUpdateTime = $equityDetails['equityResponse'][0]['lastUpdateTime'] ?? null;
        $securityDataInfo = new stdClass();
        $securityDataInfo->tradingSegment = $securityInfo['tradingSegment'] ?? 'N/A';
        $securityDataInfo->industryInfo = $securityInfo['industryInfo'] ?? null;
        $securityDataInfo->basicIndustry = $securityInfo['basicIndustry'] ?? null;
        $securityDataInfo->sector = $securityInfo['sector'] ?? null;
        $securityDataInfo->macro = $securityInfo['macro'] ?? null;
        $securityDataInfo->listingDate = $this->datetimeFormat($securityInfo['listingDate'] ?? null, 'Y-m-d');
        $securityDataInfo->status = $securityInfo['secStatus'] ?? null;
        $securityDataInfo->index = $securityInfo['index'] ?? null;
        $securityDataInfo->indexList = implode(',', $securityInfo['indexList'] ?? []);
        $securityDataInfo->tradingStatus = $securityInfo['isSuspended'] ?? null;
        $securityDataInfo->surveillanceSurv = $securityInfo['surveillance_surv'] ?? null;
        $securityDataInfo->surveillanceDesc = $securityInfo['surveillance_desc'] ?? null;
        $securityDataInfo->lastUpdateTime = $this->datetimeFormat($lastUpdateTime);

        $stockData->securityData = $securityDataInfo;

        $priceDataInfo = new stdClass();
        //priceInfo
        $priceDataInfo->yearHightDt = $this->datetimeFormat($priceInfo['yearHightDt'] ?? null, 'Y-m-d');
        $priceDataInfo->yearLowDt = $this->datetimeFormat($priceInfo['yearLowDt'] ?? null, 'Y-m-d');
        $priceDataInfo->yearHigh = $this->twoDecimals($priceInfo['yearHigh'] ?? null);
        $priceDataInfo->yearLow = $this->twoDecimals($priceInfo['yearLow'] ?? null);
        //tradeInfo
        $priceDataInfo->lastPrice = $this->twoDecimals($tradeInfo['lastPrice'] ?? null);
        $priceDataInfo->faceValue = $this->twoDecimals($tradeInfo['faceValue'] ?? null);
        //equityMetaData
        $priceDataInfo->open = $this->twoDecimals($equityMetaData['open'] ?? null);
        $priceDataInfo->dayHigh = $this->twoDecimals($equityMetaData['dayHigh'] ?? null);
        $priceDataInfo->dayLow = $this->twoDecimals($equityMetaData['dayLow'] ?? null);
        $priceDataInfo->previousClose = $this->twoDecimals($equityMetaData['previousClose'] ?? null);
        $priceDataInfo->change = $this->twoDecimals($equityMetaData['change'] ?? null);
        $priceDataInfo->pChange = $this->twoDecimals($equityMetaData['pChange'] ?? null);
        $priceDataInfo->closePrice = $this->twoDecimals($equityMetaData['closePrice'] ?? null);

        [$minPrice, $maxPrice] = array_pad(explode('-', (string) ($priceInfo['priceBand'] ?? '0-0')), 2, '0');

        $priceDataInfo->lowerCP = $this->twoDecimals($minPrice);
        $priceDataInfo->upperCP = $this->twoDecimals($maxPrice);



        $stockData->priceData = $priceDataInfo;

       return $stockData;
    }

    public function toJson($data)
    {
        return response()->json($data);
    }

    public function twoDecimals(float|string|int $value): float
    {
        return HelperServices::twoDecimals($value);
    }

    public function datetimeFormat(?string $value, string $format = 'Y-m-d H:i:s'): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return HelperServices::datetimeFormat($value, $format);
    }

    public function corporateTopActions()
    {
        try {
            $response = $this->nseClient->getCorporateTopActions();
            
            if (is_array($response)) {
                return response()->json($response);
            }

            $this->appLog([
                'message' => 'Unexpected response structure from NSE API for corporate info',
                'response' => $response,
            ], 'error');

            return response()->json(['error' => self::NO_DATA_FOUND], 404);

        } catch (\Exception $e) {
            $this->appLog([
                'message' => 'Error fetching corporate info from NSE API',
                'error' => $e->getMessage(),
            ], 'error');

            return response()->json(['error' => self::NO_DATA_FOUND], 404);
        }
    }

    public function corporateStockInfo(string $stockSymbol)
    {
        try {
            $response = $this->nseClient->getCorporateStockInfo($stockSymbol);
            
            if (is_array($response)) {
                return response()->json($response);
            }

            $this->appLog([
                'message' => "Unexpected response structure from NSE API for corporate info {$stockSymbol}",
                'response' => $response,
            ], 'error');

            return response()->json(['error' => self::NO_DATA_FOUND], 404);

        } catch (\Exception $e) {
            $this->appLog([
                'message' => "Error fetching corporate info from NSE API for stock {$stockSymbol}",
                'error' => $e->getMessage(),
            ], 'error');

            return response()->json(['error' => self::NO_DATA_FOUND], 404);
        }
    }

}
