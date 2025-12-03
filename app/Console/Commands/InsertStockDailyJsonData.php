<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessJsonDataInsert;
use Illuminate\Support\Facades\Log;
use App\Models\StockSymbol;
use App\Http\Controllers\StockController;

class InsertStockDailyJsonData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:insert-stock-daily-json-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->totalStocks = \App\Models\StockSymbol::count();
        $this->info("Starting stock data dispatch for {$this->totalStocks} stocks...");

        \App\Models\StockSymbol::chunk($this->batchSize, function ($stocks) {
            $this->batchCount++;
            
            foreach ($stocks as $stock) {
                
                $this->info("Dispatched stock {$stock->symbol} ");
                Log::info("Dispatched stock {$this->totalStocks} of total stocks. {$stock->symbol} ");
                // Log::channel('stock_backup_json')->info("Dispatched stock {$this->totalStocks} of total stocks. {$stock->symbol} ");
                ProcessJsonDataInsert::dispatch($stock->symbol); 
                $this->totalStocks--;
                // exit();
            }

            $this->info("Dispatched batch {$this->totalStocks} of {$stock->symbol} stocks.");
        });
        
        $this->info("All {$this->totalStocks} stocks have been dispatched to the queue.");
        $this->info("Run 'php artisan queue:work' to process the jobs.");

        return 0;
    }
}
