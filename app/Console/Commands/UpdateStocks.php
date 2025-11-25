<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Log;

class UpdateStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:stocks';

    protected $batchSize = 200;

    protected $totalStocks = 0;

    protected $batchCount = 0;

    protected $stockCount = 0;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update stocks data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->stockCount = \App\Models\StockSymbol::count();
        \App\Models\StockSymbol::chunk($this->batchSize, function ($stocks) {
            $this->batchCount++;
            foreach ($stocks as $key=>$stock) {
                $this->totalStocks++;
                $data = (new StockController())->processStockData($stock->symbol);
                Log::info(date('Y-m-d H:i:s').' - Stock updated: ' . $this->stockCount. ' totalStocks: ' . $this->totalStocks . ' of batchCount: ' . $this->batchCount . ' - $key: ' . ($key+1) . ' of ' . $this->batchSize . ' - ' . $stock->symbol.' - '.$data);
                $this->info('Stock updated: ' . $this->stockCount. ' totalStocks: ' . $this->totalStocks . ' of batchCount: ' . $this->batchCount . ' - $key: ' . ($key+1) . ' of ' . $this->batchSize . ' - ' . $stock->symbol.' - '.$data);
                $this->stockCount--;
            }
        });
        
        $this->info('Stock update finished.');
    }
}
