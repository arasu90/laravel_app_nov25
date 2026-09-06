<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Jobs\ProcessStockData;

class InsertStockDailyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:stock-daily-data';

    protected $batchSize = 100;

    protected $totalStocks = 0;

    protected $batchCount = 0;

    protected $stockCount = 0;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert stock daily data';

    public function handle()
    {
        $this->totalStocks = \App\Models\StockSymbol::where('is_active', '1')
            // ->where('symbol','RELINFRA')
            ->count();
        $this->info("Starting stock data dispatch for {$this->totalStocks} stocks...");

        \App\Models\StockSymbol::where('is_active', '1')
            // ->where('symbol','RELINFRA')
            ->chunk($this->batchSize, function ($stocks) {
                $this->batchCount++;
                $batchStockCount = 0;
                foreach ($stocks as $stock) {
                    $this->stockCount++;
                    $batchStockCount++;
                    $this->info("Dispatched stock {$stock->symbol} ");
                    Log::info("Dispatched stock {$this->stockCount} of total stocks {$this->totalStocks} with {$batchStockCount} of batchCount {$this->batchCount}. {$stock->symbol} ");
                    ProcessStockData::dispatch($stock->symbol);
                }
            }
        );
        
        $this->info("All {$this->totalStocks} stocks have been dispatched to the queue.");
        $this->info("Run 'php artisan queue:work' to process the jobs.");

        return 0;
    }
}
