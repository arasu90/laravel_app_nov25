<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Artisan;

// home
Route::get('/', [HomeController::class, 'index'])->name('home');
// all stocks table view
Route::get('/stock-table', [HomeController::class, 'stockListTableView'])->name('stockListTableView');
// data table view
Route::get('/table/data-table', [HomeController::class, 'dataTableView'])->name('dataTableView');


// insert all stocks into database
Route::get('/all-stocks', [StockController::class, 'allStocks'])->name('allStocks');
Route::get('/get-holiday-list', [StockController::class, 'getHolidayList'])->name('getHolidayList');

// monthly view
// Route::get('/stocks/monthly', [StockController::class, 'monthlyView']);

// get daily data for all stocks from nse api
Route::get('/trigger-stock-update', function () {
    exec('php /var/www/artisan update:stocks > /dev/null 2>&1 &');
    return "Running in background";
});

// get daily data for a specific stock from nse api
Route::get('/update-stock-data/{symbol}', [StockController::class, 'processStockData'])->where('symbol', '.*');

// one day view all stocks changes
Route::get('/one-day-view', [HomeController::class, 'oneDayView'])->name('oneDayView');

// holiday list
Route::get('/holiday-list', [HomeController::class, 'holidayList'])->name('holidayList');

// get index names
Route::get('/get-index-names', [StockController::class, 'getIndexNames'])->name('getIndexNames');

// list index names
Route::get('/index-list', [HomeController::class, 'indexList'])->name('indexList');

// stock detail view
Route::get('/stock-detail-view', [HomeController::class, 'stockDetailView'])->name('stockDetailView');


// get records insert into database for all stocks from nse api
Route::get('/insert-stock-daily-data', function () {
    exec('php /var/www/artisan insert:stock-daily-data > /dev/null 2>&1 &');
    return "Running in background";
});


// get records insert into database for all stocks from nse api
// Route::get('/insert-corporate-info', function () {
//     exec('php /var/www/artisan app:start-processing-command > /dev/null 2>&1 &');
//     return "Running in background Corporate Info";
// });


Route::get('/get-corporate-info', function () {
    Artisan::call('app:start-get-corporate-info');
    return "Corporate Info Processing Triggered!";
});



Route::get('/db-query', [HomeController::class, 'dbQuery'])->name('dbQuery');

Route::get('/average-stock', [HomeController::class, 'averageStock'])->name('averageStock');

Route::get('/my-portfolio', [HomeController::class, 'myPortfolio'])->name('myPortfolio');
Route::post('/add-my-portfolio', [HomeController::class, 'addMyPortfolio'])->name('addMyPortfolio');
Route::get('/my-watchlist', [HomeController::class, 'myWatchList'])->name('myWatchList');
Route::get('/available-url', [HomeController::class, 'appUrl'])->name('appUrl');
Route::get('/corporate-info', [HomeController::class, 'corporateInfo'])->name('corporateInfo');
Route::get('/update-corporate-info/{symbol}', [StockController::class, 'updateCorporateInfo'])->name('updateCorporateInfo');

// update all index on day based
Route::get('/update-all-index', [StockController::class, 'updateAllIndex'])->name('updateAllIndex');

// get view all index day records
Route::get('/view-all-index', [HomeController::class, 'viewAllIndex'])->name('viewAllIndex');
Route::get('/today-stock', [HomeController::class, 'todayStock'])->name('todayStock');

// one day view all index changes
Route::get('/one-day-index', [HomeController::class, 'oneDayIndex'])->name('oneDayIndex');

// last few days stock list
Route::get('/last-few-days-stock', [HomeController::class, 'lastFewDays'])->name('lastFewDays');

// paper trade for watching stocks
Route::get('/paper-trade', [HomeController::class, 'PaperTrade'])->name('PaperTrade');
Route::get('/stock-price-list', [HomeController::class, 'stockPriceList'])->name('stockPriceList');
Route::get('/nse-index-stock-list', [HomeController::class, 'nseIndexStockList'])->name('nseIndexStockList');
Route::get('/sector-stock-list', [HomeController::class, 'sectorStockList'])->name('sectorStockList');
Route::get('/icons', [HomeController::class, 'icons'])->name('icons');

Route::get('/index-detail-view', [HomeController::class, 'indexDetailView'])->name('indexDetailView');

