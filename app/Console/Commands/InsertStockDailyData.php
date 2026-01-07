<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Log;

// use App\Jobs\ProcessJsonDataInsert;
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

    /**
     * Execute the console command.
     */
    // public function handle()
    // {
    //     $this->stockCount = \App\Models\StockSymbol::count();
    //     $failedStocks = 0;
    //     $successfulStocks = 0;
        
    //     \App\Models\StockSymbol::chunk($this->batchSize, function ($stocks) use (&$failedStocks, &$successfulStocks) {
    //         $this->batchCount++;
    //         foreach ($stocks as $key=>$stock) {
    //             $this->totalStocks++;
                
    //             try {
    //                 $data = (new StockController())->insertStockDailyData($stock->symbol);
    //                 $successfulStocks++;
                    
    //                 Log::info(date('Y-m-d H:i:s').' - Stock updated: ' . $this->stockCount. ' totalStocks: ' . $this->totalStocks . ' of batchCount: ' . $this->batchCount . ' - $key: ' . ($key+1) . ' of ' . $this->batchSize . ' - ' . $stock->symbol.' - '.$data);
    //                 $this->info('✓ Stock updated: ' . $this->stockCount. ' totalStocks: ' . $this->totalStocks . ' of batchCount: ' . $this->batchCount . ' - $key: ' . ($key+1) . ' of ' . $this->batchSize . ' - ' . $stock->symbol.' - '.$data);
                    
    //             } catch (\Illuminate\Http\Client\ConnectionException $e) {
    //                 $failedStocks++;
    //                 Log::error(date('Y-m-d H:i:s').' - Connection timeout for stock: ' . $stock->symbol . ' - ' . $e->getMessage());
    //                 $this->error('✗ Connection timeout: ' . $stock->symbol . ' (skipping)');
                    
    //             } catch (\Exception $e) {
    //                 $failedStocks++;
    //                 Log::error(date('Y-m-d H:i:s').' - Error processing stock: ' . $stock->symbol . ' - ' . $e->getMessage());
    //                 $this->error('✗ Error processing: ' . $stock->symbol . ' - ' . $e->getMessage());
    //             }
                
    //             $this->stockCount--;
                
    //             // Add a small delay between requests to avoid overwhelming the API
    //             // usleep(100); // 100ms delay
    //         }
    //     });
        
    //     $this->info('Stock update finished.');
    //     $this->info("Summary: {$successfulStocks} successful, {$failedStocks} failed");
    // }

    public function handle()
    {
        $this->totalStocks = \App\Models\StockSymbol::where('is_active', '1')
            ->where('symbol','RELINFRA')
            ->count();
        $this->info("Starting stock data dispatch for {$this->totalStocks} stocks...");

        \App\Models\StockSymbol::where('is_active', '1')
            ->where('symbol','RELINFRA')
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
