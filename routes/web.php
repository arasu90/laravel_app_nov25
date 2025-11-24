<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StockController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/all-stocks', [StockController::class, 'allStocks'])->name('allStocks');

// routes/web.php
Route::get('/stocks/monthly', [StockController::class, 'monthlyView']);

Route::get('/trigger-stock-update', function () {
    exec('php /var/www/artisan update:stocks > /dev/null 2>&1 &');
    return "Running in background";
});

Route::get('/update-stock-data/{symbol}', [StockController::class, 'processStockData'])->where('symbol', '.*');