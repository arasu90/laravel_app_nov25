<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StockControllerNew;
use Illuminate\Support\Facades\Artisan;

############### MENU ###############
// home/dashboard
Route::get('/', [HomeController::class, 'index'])->name('home');

// holiday list /*** Holiday List ***/
Route::get('/holiday-list', [HomeController::class, 'holidayList'])->name('holidayList');

/*** Today Stock ***/
Route::get('/today-stock', [HomeController::class, 'todayStock'])->name('todayStock');

/*** Corporate Info Menu***/
Route::get('/corporate-actions', [HomeController::class, 'corporateActions'])->name('corporateActions');

/*** Available URL ***/
Route::get('/available-url', [HomeController::class, 'appUrl'])->name('appUrl');

// one day view all stocks changes /*** One Day Stock ***/
Route::get('/one-day-view', [HomeController::class, 'oneDayView'])->name('oneDayView');

// stock detail view /*** Stock Detail View ***/
Route::get('/stock-detail-view', [HomeController::class, 'stockDetailView'])->name('stockDetailView');

// all stocks table view /*** View All Stocks ***/
Route::get('/stock-table', [HomeController::class, 'stockListTableView'])->name('stockListTableView');

/*** Average Stock ***/
Route::get('/average-stock', [HomeController::class, 'averageStock'])->name('averageStock');

/*** Paper Trade ***/
Route::get('/paper-trade', [HomeController::class, 'PaperTrade'])->name('PaperTrade');

/*** My Portfolio ***/
Route::get('/my-portfolio', [HomeController::class, 'myPortfolio'])->name('myPortfolio');

// add my portfolio stocks
Route::post('/add-my-portfolio', [HomeController::class, 'addMyPortfolio'])->name('addMyPortfolio');

/*** My WatchList ***/
Route::get('/my-watch-list', [HomeController::class, 'myWatchList'])->name('myWatchList');

// last few days stock list /** Last Few Days Stocks ***/
Route::get('/last-few-days-stock', [HomeController::class, 'lastFewDays'])->name('lastFewDays');

/*** Stock Price List ***/
Route::get('/stock-price-list', [HomeController::class, 'stockPriceList'])->name('stockPriceList');

/*** Sector Stock List ***/
Route::get('/sector-stock-list', [HomeController::class, 'sectorStockList'])->name('sectorStockList');

/******* TESTING URL *******/
// data table view
// Route::get('/table/data-table', [HomeController::class, 'dataTableView'])->name('dataTableView');

############### Invisible Web URLs ###############

// insert all stocks into database
Route::get('/all-stocks', [StockControllerNew::class, 'allStocks'])->name('allStocks');

// get & insert holiday list from nse api
Route::get('/getAndUpdateHolidayList', [StockControllerNew::class, 'getAndUpdateHolidayList'])->name('getAndUpdateHolidayList');

// application available icons list
Route::get('/icons', [HomeController::class, 'icons'])->name('icons');

// get daily data for a specific stock from nse api
Route::get('/update-stock-data/{symbol}', [StockControllerNew::class, 'processStockData'])->where('symbol', '.*');

// get records insert into database for all stocks from nse api
Route::get('/insert-stock-daily-data', function () {
    exec('php /var/www/artisan insert:stock-daily-data > /dev/null 2>&1 &');
    return "Running in background";
});
