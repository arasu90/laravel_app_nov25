<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockSymbol;
use App\Jobs\ProcessCorporateInfo;
use Illuminate\Support\Facades\Log;

class StartProcessingCommand extends Command
{
    protected $signature = 'app:start-processing-command';
    protected $description = 'Process corporate info for all stocks';

    protected $batchSize = 100;

    public function handle()
    {
        $this->info("Starting stock processing...");

        // dd(StockSymbol::count());
        StockSymbol::chunk($this->batchSize, function ($stocks) {
            foreach ($stocks as $stock) {

                // Dispatch job
                ProcessCorporateInfo::dispatch($stock->symbol);

                // Log progress
                $this->info("Queued job for {$stock->symbol}");
                Log::info("Queued job for {$stock->symbol}");
                exit();
            }
        });

        $this->info("All jobs dispatched successfully!");
    }
}
