<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockSymbol;
use App\Jobs\UpdateCorporateInfo;
use Illuminate\Support\Facades\Log;

class StartGetCorporateInfo extends Command
{
    protected $signature = 'app:start-get-corporate-info';
    protected $description = 'Dispatch corporate info jobs for all stocks';
    protected $batchSize = 100;

    protected $runningStock = 0;
    protected $batchCount = 0;
    protected $totalStock = 0;

    public function handle()
    {
        Log::info("==== Corporate Info JOB STARTED ====");

        $this->totalStock = StockSymbol::where('is_active', true)->count();
        $this->info("Total stocks: $this->totalStock");
        Log::info("Total stocks: $this->totalStock");

        StockSymbol::chunk($this->batchSize, function ($stocks) {
            $this->batchCount++;
            foreach ($stocks as $stock) {
                $this->runningStock++;
                UpdateCorporateInfo::dispatch($stock->symbol);
                $this->info("Queued job for {$this->runningStock} out of {$this->totalStock} with Batch {$this->batchCount} {$stock->symbol}");
                Log::info("Queued job for {$this->runningStock} out of {$this->totalStock} with Batch {$this->batchCount} {$stock->symbol}");
                // exit();
            }
        });

        Log::info("==== Corporate Info JOB DISPATCH COMPLETE ====");
        $this->info("All jobs dispatched.");
    }
}
