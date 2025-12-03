<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Log;

class ProcessJsonDataInsert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $stockSymbol;

    public function __construct($stockSymbol)
    {
        Log::info("JOB CONSTRUCTOR CALLED: " . $stockSymbol);
        $this->stockSymbol = $stockSymbol;
    }

    public function handle()
    {
        try {
            Log::info("Processing stocks json data: {$this->stockSymbol}");
            // Log::channel('stock_backup_json')->info("Processing stock: {$this->stockSymbol}");
            (new StockController())->insertStockDailyData($this->stockSymbol);
            Log::info("Stock processed: {$this->stockSymbol}");
        } catch (\Throwable $e) {
            Log::error("Job failed for stock {$this->stockSymbol}: ".$e->getMessage());
            // Optional: rethrow to mark job as failed in queue
            throw $e;
        }
    }   
}
