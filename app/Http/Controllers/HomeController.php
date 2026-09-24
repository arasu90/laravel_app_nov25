<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use stdClass;

use App\Http\Controllers\Traits\UtilityTrait;
use App\Http\Controllers\Traits\DashboardTrait;
use App\Http\Controllers\Traits\ApplicationTrait;
use App\Http\Controllers\NSEStockControllerNew;
use App\Services\HelperServices;

use App\Models\StockSymbol;
use App\Models\StockHoliday;
use App\Models\StockDetails;
use App\Models\StockDailyPriceData;
use App\Models\MyWatchList;
use App\Models\MyPortfolioStock;

class HomeController extends Controller
{
    use UtilityTrait;

    protected NSEStockControllerNew $nseStockController;
    protected string $today;

    // last tested on 06 Sep 2026 11:48 AM
    public function __construct(NSEStockControllerNew $nseStockController)
    {
        $this->nseStockController = $nseStockController;
        $this->today = $this->nseStockController->today();
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function index()
    {
        return view('home', [
            'totalStocks'      => StockSymbol::where('is_active', true)->count(),
            'topGainerPer'     => [],
            'topGainerChange'  => [],
            'topLooserPer'     => [],
            'topLooserChange'  => [],
            'week52High'       => [],
            'week52Low'        => [],
            'nifty50Index'    => 0,
            'indexVix'        => 0,
        ]);
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function holidayList()
    {
        $currentMonth = date('m');
        $holidays = StockHoliday::where('year', date('Y'));
        if ($currentMonth > 6):
            $holidays = $holidays->orderBy('date', 'desc');
        else:
            $holidays = $holidays->orderBy('date', 'asc');
        endif;
        $holidays = $holidays->get();
        return view('holiday_list', compact('holidays'));
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function todayStock()
    {
        $today = $this->today;

        $todayAddedStock = StockSymbol::whereDate('created_at', $today)
            ->where('is_active', true)
            ->orderBy('symbol', 'asc')
            ->get();

        $currentHour = now()->hour;

        $todayMissedStock = DB::table('s_stock_symbols as sss')
            ->whereNotIn('sss.symbol', function ($query) use ($today, $currentHour) {
                $query->select('symbol')
                    ->from('s_stock_daily_price_data')
                    ->whereDate('date', $today)
                    ->when($currentHour > 15, function ($query) {
                        $query->whereTime('updated_at', '>', '15:00:00');
                    });
            })
            ->where('is_active', true)
            ->get();

        [$start, $end] = $this->nseStockController->getDateRange(5);

        $recentAddedStock = StockSymbol::where('is_active', true)
            ->whereBetween('created_at', [
                $start . ' 00:00:00',
                $end . ' 23:59:59',
            ])
            ->orderBy('created_at', 'desc')
            ->orderBy('symbol', 'asc')
            ->get();

        $recentSuspendedStock = StockDetails::where('trading_status', 'Suspended')
            ->whereHas('symbol', function ($q) {
                $q->where('is_active', true);
            })
            ->where('is_active', true)
            ->orderBy('last_update_time', 'asc')
            ->get();

        return view('today-stock', compact(
            'todayAddedStock',
            'todayMissedStock',
            'recentAddedStock',
            'today',
            'recentSuspendedStock'
        ));
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function corporateActions()
    {
        $stockList = StockSymbol::where('is_active', true)
            ->orderBy('symbol')
            ->get();

        $corporateInfo = DB::table('s_stock_symbols')
            ->join(
                's_stock_corporate_info',
                's_stock_corporate_info.symbol',
                '=',
                's_stock_symbols.symbol'
            )
            ->leftJoin(
                's_stock_details',
                's_stock_details.symbol',
                '=',
                's_stock_symbols.symbol'
            )
            ->where('s_stock_corporate_info.actions_type', 'corporate_actions')
            ->where('s_stock_symbols.is_active', true)
            ->select([
                's_stock_symbols.symbol',
                's_stock_details.company_name',
                's_stock_corporate_info.actions_date',
                's_stock_corporate_info.actions_purpose',
            ])
            ->orderByDesc('s_stock_corporate_info.actions_date')
            ->limit(25)
            ->get();

        return view('corporate_info', compact('stockList', 'corporateInfo'));
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function oneDayView(Request $request)
    {
        $stockName = $request->input('stock_name');
        $sortByKey = $request->input('sort_by') ?? 'name_az';
        $sortConfig = $this->getOneDaySortConfig($sortByKey);
        $today = $this->nseStockController->today();
        $currentHour = now()->hour;
        $dayRecords = DB::table('s_stock_daily_price_data')
            ->where('date', $today)
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
            ->where('s_stock_symbols.is_active', true)
            ->when($currentHour > 15, function ($query) {
                $query->whereTime('s_stock_daily_price_data.updated_at', '>', '15:00:00');
            })
            ->select(
                's_stock_daily_price_data.symbol',
                's_stock_details.company_name',
                's_stock_daily_price_data.date',
                's_stock_daily_price_data.last_price',
                's_stock_daily_price_data.change',
                's_stock_daily_price_data.p_change'
            )
            ->whereRaw($sortConfig['where'] ?? '1=1')
            ->groupBy(
                's_stock_daily_price_data.symbol',
                's_stock_details.company_name',
                's_stock_daily_price_data.date',
                's_stock_daily_price_data.last_price',
                's_stock_daily_price_data.change',
                's_stock_daily_price_data.p_change'
            )
            ->orderBy($sortConfig['column'], $sortConfig['order']);

        if (!empty($stockName)) {
            $dayRecords = $dayRecords
                ->where('s_stock_symbols.symbol', 'like', '%' . $stockName . '%')
                ->orWhere('s_stock_details.company_name', 'like', '%' . $stockName . '%');
        }

        $stockCount = StockSymbol::where('is_active', true)->count();
        $stockList = StockSymbol::with('details')->where('is_active', true)->orderBy('symbol')->get();

        return view('one_day_view', [
            'day_records' => $dayRecords->get(),
            'record_date' => $today,
            'stockCount' => $stockCount,
            'stock_list' => $stockList,
            'sort_by' => $sortByKey,
            'sort_options' => $this->getOneDaySortConfig(),
        ]);
    }

    // last tested on 06 Sep 2026 11:48 AM
    protected function getOneDaySortConfig(?string $sortKey = null): array
    {
        $options = [
            'name_az' => [
                'label' => 'Name A-Z',
                'column' => 's_stock_symbols.symbol',
                'order' => 'asc',
                'where' => null,
            ],
            'name_za' => [
                'label' => 'Name Z-A',
                'column' => 's_stock_symbols.symbol',
                'order' => 'desc',
                'where' => null,
            ],
            'low_price' => [
                'label' => 'Price Low to High',
                'column' => 's_stock_daily_price_data.last_price',
                'order' => 'asc',
                'where' => null,
            ],
            'high_price' => [
                'label' => 'Price High to Low',
                'column' => 's_stock_daily_price_data.last_price',
                'order' => 'desc',
                'where' => null,
            ],
            'p_change_asc' => [
                'label' => 'Percentage Low to High',
                'column' => 's_stock_daily_price_data.p_change',
                'order' => 'asc',
                'where' => null,
            ],
            'p_change_desc' => [
                'label' => 'Percentage High to Low',
                'column' => 's_stock_daily_price_data.p_change',
                'order' => 'desc',
                'where' => null,
            ],
            'low_price_zero' => [
                'label' => 'Price Low to High (except 0)',
                'column' => 's_stock_daily_price_data.last_price',
                'order' => 'asc',
                'where' => 's_stock_daily_price_data.last_price != 0',
            ],
            'high_price_zero' => [
                'label' => 'Price High to Low (except 0)',
                'column' => 's_stock_daily_price_data.last_price',
                'order' => 'desc',
                'where' => 's_stock_daily_price_data.last_price != 0',
            ],
            'p_change_asc_gt_zero' => [
                'label' => 'Percentage Low to High (only > 0)',
                'column' => 's_stock_daily_price_data.p_change',
                'order' => 'asc',
                'where' => 's_stock_daily_price_data.p_change > 0',
            ],
            'p_change_desc_gt_zero' => [
                'label' => 'Percentage High to Low (only > 0)',
                'column' => 's_stock_daily_price_data.p_change',
                'order' => 'desc',
                'where' => 's_stock_daily_price_data.p_change > 0',
            ],
            'p_change_asc_lt_zero' => [
                'label' => 'Percentage Low to High (only < 0)',
                'column' => 's_stock_daily_price_data.p_change',
                'order' => 'asc',
                'where' => 's_stock_daily_price_data.p_change < 0',
            ],
            'p_change_desc_lt_zero' => [
                'label' => 'Percentage High to Low (only < 0)',
                'column' => 's_stock_daily_price_data.p_change',
                'order' => 'desc',
                'where' => 's_stock_daily_price_data.p_change < 0',
            ],
            'low_price_price' => [
                'label' => 'Price Change Low to High',
                'column' => 's_stock_daily_price_data.change',
                'order' => 'asc',
                'where' => null,
            ],
            'high_price_price' => [
                'label' => 'Price Change High to Low',
                'column' => 's_stock_daily_price_data.change',
                'order' => 'desc',
                'where' => null,
            ],
            'p_change_price_asc_gt_zero' => [
                'label' => 'Price Change Low to High (only > 0)',
                'column' => 's_stock_daily_price_data.change',
                'order' => 'asc',
                'where' => 's_stock_daily_price_data.change > 0',
            ],
            'p_change_price_desc_gt_zero' => [
                'label' => 'Price Change High to Low (only > 0)',
                'column' => 's_stock_daily_price_data.change',
                'order' => 'desc',
                'where' => 's_stock_daily_price_data.change > 0',
            ],
            'p_change_price_asc_lt_zero' => [
                'label' => 'Price Change Low to High (only < 0)',
                'column' => 's_stock_daily_price_data.change',
                'order' => 'asc',
                'where' => 's_stock_daily_price_data.change < 0',
            ],
            'p_change_price_desc_lt_zero' => [
                'label' => 'Price Change High to Low (only < 0)',
                'column' => 's_stock_daily_price_data.change',
                'order' => 'desc',
                'where' => 's_stock_daily_price_data.change < 0',
            ],
        ];

        if ($sortKey === null) {
            return array_map(fn (array $item) => $item['label'], $options);
        }

        return $options[$sortKey] ?? $options['name_az'];
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function stockDetailView(Request $request)
    {
        $activeStock = StockSymbol::where('is_active', true)->first();
        $stock_name = $request->input('stock_name') ?? $activeStock->symbol;
        $stock_daily_price_data = StockDailyPriceData::where('symbol', $stock_name)
            ->orderBy('date', 'desc')
            ->get();
        $stock_details = StockDetails::where('symbol', $stock_name)->first();
        $stock_list = StockSymbol::with('details')->where('is_active', true)->orderBy('symbol')->get();

        if ($stock_details) {
            $stock_details->company_name = $this->valueOrNA($stock_details->company_name);
            $stock_details->symbol = $this->valueOrNA($stock_details->symbol);
            $stock_details->sector = $this->valueOrNA($stock_details->sector);
            $stock_details->industry = $this->valueOrNA($stock_details->industry);
            $stock_details->status = $this->valueOrNA($stock_details->status);
            $stock_details->listing_date = $this->formatDate($stock_details->listing_date);
            $stock_details->trading_status = $this->valueOrNA($stock_details->trading_status);
            $stock_details->trading_segment = $this->valueOrNA($stock_details->trading_segment);
            $stock_details->face_value = $this->valueOrNA($stock_details->face_value);
            $stock_details->surveillance_desc = $this->valueOrNA($stock_details->surveillance_desc);
            $stock_details->week_high_low_min = $this->valueOrNA($stock_details->week_high_low_min);
            $stock_details->week_high_low_min_date = $this->formatDate($stock_details->week_high_low_min_date);
            $stock_details->week_high_low_max = $this->valueOrNA($stock_details->week_high_low_max);
            $stock_details->week_high_low_max_date = $this->formatDate($stock_details->week_high_low_max_date);
            $stock_details->stock_last_price = $this->valueOrNA($stock_details->stock_last_price);
            $stock_details->series = $this->valueOrNA($stock_details->series);
            $stock_details->market_type = $this->valueOrNA($stock_details->market_type);
        }

        $lineLabel = $stock_daily_price_data
            ->take(5)
            ->pluck('date')
            ->reverse()
            ->map(fn ($d) => Carbon::parse($d)->format('d-M'))
            ->values();

        $lineData = $stock_daily_price_data
            ->take(5)
            ->pluck('last_price')
            ->reverse()
            ->values();

        $lineData_1 = $stock_daily_price_data
            ->take(5)
            ->pluck('open')
            ->reverse()
            ->values();

        $chartData['line']['label'] = $lineLabel;
        $chartData['line']['data'] = $lineData;
        $chartData['line']['data_1'] = $lineData_1;

        return view('stock_detail_view', compact(
            'stock_daily_price_data',
            'stock_details',
            'stock_list',
            'stock_name',
            'chartData'
        ));
    }

    // last tested on 06 Sep 2026 11:48 AM
    protected function valueOrNA($value, string $default = 'N/A')
    {
        return ($value === null || $value === '') ? $default : $value;
    }

    // last tested on 06 Sep 2026 11:48 AM
    protected function formatDate(?string $value, string $default = 'N/A'): string
    {
        return $value
            ? HelperServices::datetimeFormat($value, 'd-M-Y')
            : $default;
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function stockListTableView()
    {
        list($start, $end) = $this->nseStockController->getDateRange(15);
        
        $prices = DB::table('s_stock_daily_price_data')
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
            ->whereBetween('s_stock_daily_price_data.date', [$start, $end])
            ->where('s_stock_symbols.is_active', true)
            ->select(
                's_stock_daily_price_data.symbol',
                's_stock_details.company_name',
                's_stock_daily_price_data.date',
                's_stock_daily_price_data.last_price',
                's_stock_daily_price_data.change',
                's_stock_daily_price_data.p_change'
            )
            ->groupBy('s_stock_daily_price_data.symbol', 's_stock_details.company_name', 's_stock_daily_price_data.date', 's_stock_daily_price_data.last_price', 's_stock_daily_price_data.change', 's_stock_daily_price_data.p_change')
            ->orderBy('s_stock_daily_price_data.symbol')
            ->orderBy('s_stock_daily_price_data.date')
            ->get();

        // 3️⃣ Transform into pivot data
        $grouped = $prices->groupBy('symbol');
        $dates =  $prices->unique('date')->pluck('date')->toArray();

        $result = [];
        foreach ($grouped as $symbol => $records) {
            $row = ['symbol' => $symbol, 'company_name' => $records->first()->company_name];

            foreach ($records as $rec) {
                $percent = $rec->last_price;

                $row[$rec->date]['last_price'] = round($percent, 2);
                $row[$rec->date]['change'] = round($rec->change, 2);
                $row[$rec->date]['p_change'] = round($rec->p_change, 2);
            }

            $result[] = $row;
        }
        return view('stock_list_table', compact('dates', 'result'));
    }

    public static function sameMonthYear($date): bool
    {
        return $date
            && Carbon::parse($date)->isSameMonth(now())
            && Carbon::parse($date)->isSameYear(now());
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function averageStock(Request $request)
    {
        $inputNames = [
            'current_total_quantity', 'current_average_price', 'new_buy_price',
            'expected_average_price', 'calculator_type', 'new_buy_quantity',
            'qty_profit_loss', 'qty_live_price', 'avg_profit_loss', 'avg_live_price',
        ];
        $inputs = array_replace(
            array_fill_keys($inputNames, null),
            $request->only($inputNames)
        );

        $quantity = (float) ($inputs['current_total_quantity'] ?? 0);
        $averagePrice = (float) ($inputs['current_average_price'] ?? 0);
        $buyPrice = (float) ($inputs['new_buy_price'] ?? 0);
        $buyQuantity = (float) ($inputs['new_buy_quantity'] ?? 0);
        $expectedPrice = (float) ($inputs['expected_average_price'] ?? 0);
        $newBuyQuantityAverage = 0;
        $newBuyPriceAverage = 0;

        if ($inputs['calculator_type'] === 'average_stock' && $expectedPrice > 0) {
            $targetPrice = $expectedPrice - 0.004;
            $denominator = $targetPrice - $buyPrice;
            $newBuyQuantityAverage = $denominator == 0
                ? 0
                : round($quantity * ($averagePrice - $targetPrice) / $denominator);
        }

        if ($inputs['calculator_type'] === 'buy_quantity_calculator' && $buyPrice > 0) {
            $denominator = $quantity + $buyQuantity;
            $newBuyPriceAverage = $denominator == 0
                ? 0
                : round(($quantity * $averagePrice + $buyPrice * $buyQuantity) / $denominator, 2);
        }

        return view('average_stock', compact('inputs', 'newBuyQuantityAverage', 'newBuyPriceAverage'));
    }

    // last tested on 24 Sep 2026 11:48 AM
    public function paperTrade()
    {
        $stock_list = StockSymbol::with('details')
            ->where('is_active', true)
            ->orderBy('symbol')
            ->get();

        $today = $this->today;

        $myPortfolioStocks = DB::table('s_portfolio_stocks as p')
            ->join('s_stock_symbols as s', 's.symbol', '=', 'p.symbol')
            ->join('s_stock_details as d', 'd.symbol', '=', 's.symbol')
            ->join('s_stock_daily_price_data as dp', function($join) use ($today) {
                $join->on('dp.symbol', '=', 'p.symbol')
                    ->where('dp.date', $today);
            })
            ->where('s.is_active', true)
            ->where('p.portfolio_type',2)
            ->select(
                'p.symbol',
                'd.company_name',
                DB::raw('SUM(p.buy_qty) as total_qty'),
                DB::raw('ROUND(SUM(p.buy_qty * p.buy_price)/SUM(p.buy_qty), 2) as avg_buy_price'),
                'dp.last_price',
                'dp.change',
                'dp.p_change'
            )
            ->groupBy('p.symbol', 'd.company_name', 'dp.last_price', 'dp.change', 'dp.p_change')
            ->orderBy('p.symbol', 'asc')
            ->get();

        return view('paper_trade', compact('stock_list', 'myPortfolioStocks'));
    }

    // last tested on 23 Sep 2026 11:48 AM
    public function myWatchList(Request $request)
    {
        $stock_name = $request->get('stock_name') ?? null;
        $today = $this->today;
        $price_min = $request->get('price_min') ?? null;
        $price_max = $request->get('price_max') ?? null;

        $watchListMaster = MyWatchList::get();
        $watchListList = [];
        foreach($watchListMaster as $watchList):
            $query = DB::table('s_watch_list_items')
                ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_watch_list_items.symbol')
                ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
                ->join('s_stock_daily_price_data', 's_stock_daily_price_data.symbol', '=', 's_stock_symbols.symbol')
                ->where('s_stock_daily_price_data.date', $today)
                ->where('s_stock_symbols.is_active', true)
                ->where('s_watch_list_items.watch_list_id', $watchList->id);

            if (!empty($stock_name)) {
                $query->where('s_stock_symbols.symbol', $stock_name);
            } elseif (!empty($price_min) && !empty($price_max)) {
                $query->whereBetween('s_stock_daily_price_data.last_price', [$price_min, $price_max]);
            }

            $watchListItems = $query->select(
                's_watch_list_items.symbol',
                's_stock_details.company_name',
                's_stock_daily_price_data.last_price',
                's_stock_daily_price_data.change',
                's_stock_daily_price_data.p_change',
                's_stock_daily_price_data.previous_close',
                's_stock_daily_price_data.open',
                's_stock_daily_price_data.close',
                's_stock_daily_price_data.lower_cp',
                's_stock_daily_price_data.upper_cp',
                's_stock_daily_price_data.intra_day_high_low_min',
                's_stock_daily_price_data.intra_day_high_low_max',
                's_stock_details.week_high_low_min',
                's_stock_details.week_high_low_min_date',
                's_stock_details.week_high_low_max',
                's_stock_details.week_high_low_max_date'
            )->get();

            if ($watchListItems->isNotEmpty()) {
                $key = str_replace([' ', '-'], '_', $watchList->watch_list_name);
                $watchListList[$key] = [
                    'name' => $watchList->watch_list_name,
                    'stock_list' => $watchListItems,
                ];
            }
        endforeach;
        
        $stock_list = StockSymbol::whereHas('watchListItems', function ($q) {
                $q->where('is_active', true);
            })
            ->with('details')
            // ->limit(5)
            ->get();
        return view('my_watch_list', compact('watchListList','stock_list', 'stock_name'));
    }

    // last tested on 06 Sep 2026 11:48 AM
    public function addMyPortfolio(Request $request)
    {
        $portfolio_type = $request->input('portfolio_type') ?? 1;
        $request->validate([
            'stock_name' => 'required',
            'buy_qty' => 'required|numeric|min:1',
            'buy_price' => 'required|numeric|min:0',
            'buy_date' => 'required|date'
        ]);

        MyPortfolioStock::create([
            'symbol' => $request->stock_name,
            'buy_price' => $request->buy_price,
            'buy_qty' => $request->buy_qty,
            'buy_date' => date('Y-m-d', strtotime($request->buy_date)),
            'portfolio_type' => $portfolio_type
        ]);
        
        return redirect()->back()
            ->with('success', 'Stock added to portfolio successfully');
    }

    // last tested on 23 Sep 2026 11:48 AM
    public function lastFewDays()
    {
        $today = $this->today;
        $lastFewDays = 5;

        [$startDate, $endDate] = $this->nseStockController->getDateRange($lastFewDays);

        $consecutiveSymbols = function (int $numberOfDays, string $condition, string $from, string $to) {
            return DB::table('s_stock_daily_price_data')
                ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
                ->join('s_stock_details', 's_stock_symbols.symbol', '=', 's_stock_details.symbol')
                ->where('s_stock_symbols.is_active', true)
                ->whereBetween('s_stock_daily_price_data.date', [$from, $to])
                ->groupBy('s_stock_symbols.symbol')
                ->havingRaw("SUM(CASE WHEN {$condition} THEN 1 ELSE 0 END) = ?", [$numberOfDays])
                ->pluck('s_stock_symbols.symbol');
        };

        $groupedData = function ($symbols, string $from, string $to, bool $includeDetails = false) {
            $data = StockDailyPriceData::query()
                ->whereIn('symbol', $symbols)
                ->when($includeDetails, function ($query) {
                    $query->with('details');
                })
                ->whereBetween('date', [$from, $to])
                ->orderBy('symbol')
                ->orderBy('date')
                ->get();

            return [
                $data->groupBy('symbol')->map(fn ($group) => $group->keyBy('date')),
                $data->pluck('date')->unique()->values(),
            ];
        };

        $todayCircuitData = function (string $column) use ($today) {
            return DB::table('s_stock_daily_price_data')
                ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
                ->join('s_stock_details', 's_stock_symbols.symbol', '=', 's_stock_details.symbol')
                ->where('s_stock_daily_price_data.date', $today)
                ->where('s_stock_symbols.is_active', true)
                ->whereRaw("s_stock_daily_price_data.last_price = s_stock_daily_price_data.{$column}")
                ->orderBy('s_stock_symbols.symbol')
                ->get();
        };

        [$lastFewDaysGainer, $lastFewGainerDates] = $groupedData(
            $consecutiveSymbols($lastFewDays, 's_stock_daily_price_data.last_price > s_stock_daily_price_data.previous_close', $startDate, $endDate),
            $startDate,
            $endDate
        );
        [$lastFewDaysLoser, $lastFewLoserDates] = $groupedData(
            $consecutiveSymbols($lastFewDays, 's_stock_daily_price_data.last_price < s_stock_daily_price_data.previous_close', $startDate, $endDate),
            $startDate,
            $endDate
        );

        $todayHitUpperCP = $todayCircuitData('upper_cp');
        $todayHitLowerCP = $todayCircuitData('lower_cp');

        [$startDate, $endDate] = $this->nseStockController->getDateRange($lastFewDays);
        [$lastFewDaysUpperCP, $lastFewDaysUpperCPDate] = $groupedData(
            $consecutiveSymbols($lastFewDays, 's_stock_daily_price_data.last_price = s_stock_daily_price_data.upper_cp', $startDate, $endDate),
            $startDate,
            $endDate,
            true
        );
        [$lastFewDaysLowerCP, $lastFewDaysLowerCPDate] = $groupedData(
            $consecutiveSymbols($lastFewDays, 's_stock_daily_price_data.last_price = s_stock_daily_price_data.lower_cp', $startDate, $endDate),
            $startDate,
            $endDate,
            true
        );

        $compactData = compact(
            'today',
            'lastFewDaysGainer',
            'lastFewGainerDates',
            'lastFewDaysLoser',
            'lastFewLoserDates',
            'todayHitUpperCP',
            'todayHitLowerCP',
            'lastFewDaysUpperCP',
            'lastFewDaysUpperCPDate',
            'lastFewDaysLowerCP',
            'lastFewDaysLowerCPDate'
        );

        return view('last_few_days_stock', $compactData);
    }

    // last tested on 23 Sep 2026 11:48 AM
    public function myPortfolio()
    {
        $stock_list = StockSymbol::with('details')
            ->where('is_active', true)
            ->orderBy('symbol')
            ->get();

        $today = $this->today;

        $myPortfolioStocks = DB::table('s_portfolio_stocks as p')
            ->join('s_stock_symbols as s', 's.symbol', '=', 'p.symbol')
            ->join('s_stock_details as d', 'd.symbol', '=', 's.symbol')
            ->join('s_stock_daily_price_data as dp', function($join) use ($today) {
                $join->on('dp.symbol', '=', 'p.symbol')
                    ->where('dp.date', $today);
            })
            ->where('s.is_active', true)
            ->where('p.portfolio_type',1)
            ->select(
                'p.symbol',
                'd.company_name',
                DB::raw('SUM(p.buy_qty) as total_qty'),
                DB::raw('ROUND(SUM(p.buy_qty * p.buy_price)/SUM(p.buy_qty), 2) as avg_buy_price'),
                'dp.last_price',
                'dp.change',
                'dp.p_change'
            )
            ->groupBy('p.symbol', 'd.company_name', 'dp.last_price', 'dp.change', 'dp.p_change')
            ->orderBy('p.symbol', 'asc')
            ->get();

        return view('my_portfolio', compact('stock_list', 'myPortfolioStocks'));
    }

    // last tested on 23 Sep 2026 11:48 AM
    public function inActiveSymbolWeb(string $symbol)
    {
        try {
            $response = $this->inActiveSymbol($symbol);
            if ($response->getStatusCode() !== 200) {
                return response()->json([
                    'result' => false,
                    'msg' => 'The symbol could not be inactivated.',
                ], $response->getStatusCode());
            }
        } catch (Exception $e) {
            return response()->json([
                'result' => false,
                'msg' => $e->getMessage(),
            ], 500);
        }

        return redirect()->back();
    }

    // last tested on 23 Sep 2026 11:48 AM
    public function inActiveSymbol(string $symbol)
    {
        $response = new stdClass();
        $response->result = false;
        $response->msg = '';

        try {
            $stockSymbol = StockSymbol::where('symbol', $symbol)->first();

            if ($stockSymbol) {
                $tradingStatus = $stockSymbol->details?->trading_status;

                if ($tradingStatus !== 'Suspended') {
                    $response->msg = 'Stock Symbol is not Suspended, cannot De-Activate';
                    return response()->json($response, 400);
                }

                $stockSymbol->is_active = false;
                $stockSymbol->save();
                $response->result = true;
                $response->msg = 'Successfully De-Activated';
            } else {
                $response->msg = 'Invalid Stock Symbol';
            }
        } catch (Exception $e) {
            $response->msg = $e->getMessage();
        }

        return response()->json($response);
    }

    // last tested on 23 Sep 2026 11:48 AM
    public function allStockList(Request $request)
    {
        $filter_type = $request->input('filter_type', 'price');

        $stockName = $request->input('stock_name');
        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');
        $today = $this->today;

        $selectColumns = [
            's_stock_symbols.symbol',
            's_stock_details.company_name',
            's_stock_daily_price_data.last_price',
            's_stock_daily_price_data.change',
            's_stock_daily_price_data.p_change',
            's_stock_daily_price_data.previous_close',
            's_stock_daily_price_data.open',
            's_stock_daily_price_data.close',
            's_stock_daily_price_data.lower_cp',
            's_stock_daily_price_data.upper_cp',
            's_stock_daily_price_data.intra_day_high_low_min',
            's_stock_daily_price_data.intra_day_high_low_max',
            's_stock_details.week_high_low_min',
            's_stock_details.week_high_low_min_date',
            's_stock_details.week_high_low_max',
            's_stock_details.week_high_low_max_date',
        ];

        $baseQuery = DB::table('s_stock_symbols')
            ->join(
                's_stock_daily_price_data',
                's_stock_daily_price_data.symbol',
                '=',
                's_stock_symbols.symbol'
            )
            ->join(
                's_stock_details',
                's_stock_details.symbol',
                '=',
                's_stock_symbols.symbol'
            )
            ->where('s_stock_daily_price_data.date', $today)
            ->where('s_stock_symbols.is_active', true)
            ->when($stockName, function ($query) use ($stockName) {
                $query->where('s_stock_symbols.symbol', $stockName);
            })
            ->when(
                !$stockName && $priceMin !== null && $priceMax !== null,
                function ($query) use ($priceMin, $priceMax) {
                    $query->whereBetween(
                        's_stock_daily_price_data.last_price',
                        [$priceMin, $priceMax]
                    );
                }
            );

        $watchListList = [];
        $filterTypeList = [
            'price' => 'Price List',
            'sector' => 'Sector List',
            'macro' => 'Macro List',
            'industry' => 'Industry List',
            'basic_industry' => 'Basic Industry List',
            'sector_index' => 'Index List',
        ];
        $nullTextValue = '-NA-';

        if ($filter_type !== 'price') {
            $allowedGroupColumns = [
                'sector',
                'macro',
                'industry',
                'basic_industry',
                'sector_index',
            ];

            // dd($filter_type, $allowedGroupColumns);
            $groupColumn = $filter_type ?? 'sector';
            $groups = StockDetails::query()
               
                ->selectRaw(
                    "COALESCE(NULLIF($groupColumn, ?), ?) AS $groupColumn",
                    ['', $nullTextValue]
                )
                ->distinct()
                ->orderBy($groupColumn)
                ->pluck($groupColumn);

            foreach ($groups as $group) {

                $groupCond = $group === $nullTextValue ? true : false;
                // dd($groupCond);
                $stockList = (clone $baseQuery)
                    ->when(!$groupCond, function ($query) use($groupColumn, $group) {
                        $query->where("s_stock_details.$groupColumn", $group);
                    })
                    ->when($groupCond, function ($query) use($groupColumn) {
                        $query->whereNull($groupColumn);
                    })
                    ->select($selectColumns)
                    ->orderBy('s_stock_daily_price_data.last_price')
                    ->orderBy('s_stock_daily_price_data.p_change')
                    ->get();

                if ($stockList->isNotEmpty()) {
                    $key = Str::slug($group, '_');

                    $watchListList[$key] = [
                        'name' => $group,
                        'stock_list' => $stockList,
                    ];
                }
            }

        } else {
            $priceGroups = [
                [
                    'key' => 'price_0_0_5',
                    'name' => 'Price 0-0.5',
                    'min' => 0.0000001,
                    'max' => 0.5,
                ],
                [
                    'key' => 'price_0_5_1',
                    'name' => 'Price 0.5-1',
                    'min' => 0.5,
                    'max' => 1,
                ],
                [
                    'key' => 'price_1_5',
                    'name' => 'Price 1-5',
                    'min' => 1,
                    'max' => 5,
                ],
                [
                    'key' => 'price_5_10',
                    'name' => 'Price 5-10',
                    'min' => 5,
                    'max' => 10,
                ],
                [
                    'key' => 'price_10_20',
                    'name' => 'Price 10-20',
                    'min' => 10,
                    'max' => 20,
                ],
                [
                    'key' => 'price_20_50',
                    'name' => 'Price 20-50',
                    'min' => 20,
                    'max' => 50,
                ],
                [
                    'key' => 'price_50_100',
                    'name' => 'Price 50-100',
                    'min' => 50,
                    'max' => 100,
                ],
                [
                    'key' => 'price_100_200',
                    'name' => 'Price 100-200',
                    'min' => 100,
                    'max' => 200,
                ],
                [
                    'key' => 'price_200_500',
                    'name' => 'Price 200-500',
                    'min' => 200,
                    'max' => 500,
                ],
                [
                    'key' => 'price_500_1000',
                    'name' => 'Price 500-1000',
                    'min' => 500,
                    'max' => 1000,
                ],
                [
                    'key' => 'price_1k_1_5k',
                    'name' => 'Price 1000-1500',
                    'min' => 1000,
                    'max' => 1500,
                ],
                [
                    'key' => 'price_1_5k_2k',
                    'name' => 'Price 1500-2000',
                    'min' => 1500,
                    'max' => 2000,
                ],
                [
                    'key' => 'price_2k_5k',
                    'name' => 'Price 2000-5000',
                    'min' => 2000,
                    'max' => 5000,
                ],
                [
                    'key' => 'price_5k_10k',
                    'name' => 'Price 5000-10000',
                    'min' => 5000,
                    'max' => 10000,
                ],
                [
                    'key' => 'price_10000_more',
                    'name' => 'Price 10000 More',
                    'min' => 10000,
                    'max' => null,
                ],
            ];

            foreach ($priceGroups as $group) {
                $query = clone $baseQuery;

                if ($group['max'] === null) {
                    $query->where(
                        's_stock_daily_price_data.last_price',
                        '>',
                        $group['min']
                    );
                } else {
                    $query->whereBetween(
                        's_stock_daily_price_data.last_price',
                        [$group['min'], $group['max']]
                    );
                }

                $stockList = $query
                    ->select($selectColumns)
                    ->orderBy('s_stock_daily_price_data.last_price')
                    ->orderBy('s_stock_daily_price_data.p_change')
                    ->get();

                if ($stockList->isNotEmpty()) {
                    $watchListList[$group['key']] = [
                        'name' => $group['name'],
                        'stock_list' => $stockList,
                    ];
                }
            }
        }

        $stockList = StockSymbol::with('details')
            ->where('is_active', true)
            ->get();

        return view('all_stock_list', [
            'watchListList' => $watchListList,
            'stock_list' => $stockList,
            'stock_name' => $stockName,
            'filterTypeList' => $filterTypeList,
            'filterSelected' => $filter_type
        ]);
    }

}
