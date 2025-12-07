<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\StockService;
use App\Http\Controllers\StockController;

class UpdateCorporateInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public string $symbol;

    public function __construct(string $symbol)
    {
        $this->symbol = $symbol;
    }

    public function handle()
    {
        try {
            (new StockController())->updateCorporateInfo($this->symbol);
            Log::info("Job completed for {$this->symbol}");
        } catch (\Throwable $e) {
            Log::error("Job failed for {$this->symbol}: " . $e->getMessage());
            throw $e;
        }
    }
}
