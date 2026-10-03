<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NSEStockControllerNew;
use App\Http\Controllers\StockControllerNew;

// show the all stocks list from nse api
Route::get('/all-stocks', [NSEStockControllerNew::class, 'getAllStocksArray']);

// show the market holidays list from nse api
Route::get('/holidays', [NSEStockControllerNew::class, 'marketHolidays']);

// show the stock details for a specific stock from nse api
Route::get('/stock/{symbol}', [NSEStockControllerNew::class, 'getStockDetails']);

// show the corporate info for a specific stock from nse api
Route::get('/corporate-info/{symbol}', [NSEStockControllerNew::class, 'corporateStockInfo']);

//show the corporate info for a top announcement
Route::get('/corporate-top-actions', [NSEStockControllerNew::class, 'corporateTopActions']);

// show the ipo stock list from nse api
Route::get('/ipo-stock-list', [StockControllerNew::class, 'ShowIpoStockListFromNSE']);

// get and insert the ipo stock list into the database
Route::get('/insert-ipo-stock-list', [StockControllerNew::class, 'getIpoStockListFromNSE']);