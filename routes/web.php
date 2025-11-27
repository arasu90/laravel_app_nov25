<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StockController;

// Route::get('/', function () {
//     return view('welcome');
// });

// home
Route::get('/', [HomeController::class, 'index'])->name('home');
// all stocks table view
Route::get('/stock-table', [HomeController::class, 'stockListTableView'])->name('stockListTableView');
// data table view
Route::get('/table/data-table', [HomeController::class, 'dataTableView'])->name('dataTableView');


// insert all stocks into database
Route::get('/all-stocks', [StockController::class, 'allStocks'])->name('allStocks');

// monthly view
Route::get('/stocks/monthly', [StockController::class, 'monthlyView']);

// get daily data for all stocks from nse api
Route::get('/trigger-stock-update', function () {
    exec('php /var/www/artisan update:stocks > /dev/null 2>&1 &');
    return "Running in background";
});

// get daily data for a specific stock from nse api
Route::get('/update-stock-data/{symbol}', [StockController::class, 'processStockData'])->where('symbol', '.*');

// one day view all stocks changes
Route::get('/one-day-view', [HomeController::class, 'oneDayView'])->name('oneDayView');