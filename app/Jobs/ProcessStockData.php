<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\StockControllerNew;

class ProcessStockData implements ShouldQueue
{
    use Queueable;
    
    public $stockSymbol;

    /**
     * Number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [5, 15, 30];

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public $maxExceptions = 2;

    public function __construct(string $stockSymbol)
    {
        $this->stockSymbol = $stockSymbol;
    }

    public function handle(StockControllerNew $stockController)
    {
        try {
            Log::info("Processing stocks data: {$this->stockSymbol}");
            $stockController->processStockData($this->stockSymbol);
            Log::info("Stock processed Done: {$this->stockSymbol}");
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Handle HTTP errors (403, 429, etc.) - will be retried
            Log::error("HTTP error for stock {$this->stockSymbol}: ".$e->getMessage());
            throw $e;
        } catch (\Throwable $e) {
            Log::error("Job failed for stock {$this->stockSymbol}: ".$e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function retryAfter(): int
    {
        return 10; // Wait 10 seconds before retry
    }
}
