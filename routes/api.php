<?php

use App\Http\Controllers\StockController;

Route::get('/market-status', [StockController::class, 'marketStatus']);
Route::get('/stock/{symbol}', [StockController::class, 'equity']);
Route::get('/stock/{symbol}/historical', [StockController::class, 'equityHistorical']);
Route::get('/indices', [StockController::class, 'indices']);
