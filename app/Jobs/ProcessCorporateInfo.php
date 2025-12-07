<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log; // <-- Add this line
use Illuminate\Foundation\Queue\Queueable;
use App\Services\StockService;

class ProcessCorporateInfo implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public $symbol;

    public function __construct($symbol)
    {
        $this->symbol = $symbol;
    }

    public function handle()
    {
        try {
            (new StockService())->updateCorporateInfo($this->symbol);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}