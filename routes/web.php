<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Artisan;

// home
Route::get('/', [HomeController::class, 'index'])->name('home');

// stock detail view /*** Stock Detail View ***/
Route::get('/stock-detail-view', [HomeController::class, 'stockDetailView'])->name('stockDetailView');

// all stocks table view /*** View All Stocks ***/
Route::get('/stock-table', [HomeController::class, 'stockListTableView'])->name('stockListTableView');

// one day view all stocks changes /*** One Day Stock ***/
Route::get('/one-day-view', [HomeController::class, 'oneDayView'])->name('oneDayView');

// one day view all index changes /*** One Day Index ***/
Route::get('/one-day-index', [HomeController::class, 'oneDayIndex'])->name('oneDayIndex');

// get view all index day records /*** View All Index ***/
Route::get('/view-all-index', [HomeController::class, 'viewAllIndex'])->name('viewAllIndex');

// holiday list /*** Holiday List ***/
Route::get('/holiday-list', [HomeController::class, 'holidayList'])->name('holidayList');

/*** Today Stock ***/
Route::get('/today-stock', [HomeController::class, 'todayStock'])->name('todayStock');

// last few days stock list /** Last Few Days Stocks ***/
Route::get('/last-few-days-stock', [HomeController::class, 'lastFewDays'])->name('lastFewDays');

/*** Average Stock ***/
Route::get('/average-stock', [HomeController::class, 'averageStock'])->name('averageStock');

/*** My Portfolio ***/
Route::get('/my-portfolio', [HomeController::class, 'myPortfolio'])->name('myPortfolio');
Route::post('/add-my-portfolio', [HomeController::class, 'addMyPortfolio'])->name('addMyPortfolio');

/*** Stock Price List ***/
Route::get('/stock-price-list', [HomeController::class, 'stockPriceList'])->name('stockPriceList');

/*** NSE Index Stock List ***/
Route::get('/nse-index-stock-list', [HomeController::class, 'nseIndexStockList'])->name('nseIndexStockList');

/*** Sector Stock List ***/
Route::get('/sector-stock-list', [HomeController::class, 'sectorStockList'])->name('sectorStockList');

/*** My Watchlist ***/
Route::get('/my-watchlist', [HomeController::class, 'myWatchList'])->name('myWatchList');

/*** Paper Trade ***/
Route::get('/paper-trade', [HomeController::class, 'PaperTrade'])->name('PaperTrade');

/*** Corporate Info ***/
Route::get('/corporate-info', [HomeController::class, 'corporateInfo'])->name('corporateInfo');

/*** Available URL ***/
Route::get('/available-url', [HomeController::class, 'appUrl'])->name('appUrl');


/******* TESTING URL *******/
// data table view
Route::get('/table/data-table', [HomeController::class, 'dataTableView'])->name('dataTableView');

/******* Invisible Web URLs *******/

// insert all stocks into database
Route::get('/all-stocks', [StockController::class, 'allStocks'])->name('allStocks');

// get holiday list from nse api
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

// get index names
Route::get('/get-index-names', [StockController::class, 'getIndexNames'])->name('getIndexNames');

// list index names
Route::get('/index-list', [HomeController::class, 'indexList'])->name('indexList');


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
Route::get('/my-watchlist', [HomeController::class, 'myWatchList'])->name('myWatchList');
Route::get('/available-url', [HomeController::class, 'appUrl'])->name('appUrl');
Route::get('/update-corporate-info/{symbol}', [StockController::class, 'updateCorporateInfo'])->name('updateCorporateInfo');

// update all index on day based
Route::get('/update-all-index', [StockController::class, 'updateAllIndex'])->name('updateAllIndex');

// paper trade for watching stocks
Route::get('/icons', [HomeController::class, 'icons'])->name('icons');

Route::get('/index-detail-view', [HomeController::class, 'indexDetailView'])->name('indexDetailView');

// de-active stocks
Route::get('/inactive-stocks-web/{symbol}', [HomeController::class, 'inActiveSymbolWeb'])->name('inActiveSymbolWeb');
Route::get('/inactive-stocks/{symbol}', [HomeController::class, 'inActiveSymbol'])->name('inActiveSymbol');
// modify stocks symbol old to new
Route::get('/modify-stocks/{oldSymbol}/{newSymbol}', [HomeController::class, 'modifyStock'])->name('modifyStock');
// recheck the suspended stock to active
Route::get('/check-suspended-stock', [HomeController::class, 'checkSuspendedStock'])->name('checkSuspendedStock');
// execute records stocks
Route::get('/check-stock', [HomeController::class, 'checkStock'])->name('checkStock');

