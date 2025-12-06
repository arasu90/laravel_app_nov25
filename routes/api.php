<?php

use App\Http\Controllers\NSEStockController;

Route::get('/market-status', [NSEStockController::class, 'marketStatus']);
Route::get('/stock/{symbol}', [NSEStockController::class, 'equity']);
Route::get('/stock/{symbol}/historical', [NSEStockController::class, 'equityHistorical']);
Route::get('/indices', [NSEStockController::class, 'indices']);
Route::get('/all-stocks', [NSEStockController::class, 'allStocks']);
Route::get('/holidays', [NSEStockController::class, 'marketHolidays']);
Route::get('/corporate-info/{symbol}', [NSEStockController::class, 'corporateInfo']);
Route::get('/all-index-names', [NSEStockController::class, 'getIndexNames']);
Route::get('/equity-master', [NSEStockController::class, 'getEquityMaster']);
Route::get('/circular', [NSEStockController::class, 'getCircular']);
Route::get('/historical-data/{symbol}', [NSEStockController::class, 'getHistoricalData']);
Route::get('/historical-data-index/{symbol}', [NSEStockController::class, 'getHistoricalDataIndex']);