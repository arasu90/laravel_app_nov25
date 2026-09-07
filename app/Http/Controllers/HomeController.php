<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        $dayRecords = DB::table('s_stock_daily_price_data')
            ->where('date', $today)
            ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
            ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
            ->where('s_stock_symbols.is_active', true)
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

    public function lastFewDays()
    {
        $today = $this->today;
        $days = 5;
        [$startDate, $endDate] = $this->nseStockController->getDateRange($days);

        $consecutiveSymbols = function (int $numberOfDays, string $condition, string $from, string $to) {
            return DB::table('s_stock_daily_price_data')
                ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
                ->where('s_stock_symbols.is_active', true)
                ->whereBetween('s_stock_daily_price_data.date', [$from, $to])
                ->groupBy('s_stock_symbols.symbol')
                ->havingRaw("SUM(CASE WHEN {$condition} THEN 1 ELSE 0 END) = ?", [$numberOfDays])
                ->pluck('s_stock_symbols.symbol');
        };

        $groupedData = function ($symbols, string $from, string $to, bool $includeDetails = false) {
            $query = $includeDetails
                ? DB::table('s_stock_daily_price_data')
                    ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_daily_price_data.symbol')
                    ->join('s_stock_details', 's_stock_symbols.symbol', '=', 's_stock_details.symbol')
                    ->whereIn('s_stock_symbols.symbol', $symbols)
                : StockDailyPriceData::whereIn('symbol', $symbols);

            $data = $query
                ->whereBetween($includeDetails ? 's_stock_daily_price_data.date' : 'date', [$from, $to])
                ->orderBy($includeDetails ? 's_stock_symbols.symbol' : 'symbol')
                ->orderBy($includeDetails ? 's_stock_daily_price_data.date' : 'date')
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
            $consecutiveSymbols($days, 's_stock_daily_price_data.last_price > s_stock_daily_price_data.previous_close', $startDate, $endDate),
            $startDate,
            $endDate
        );
        [$lastFewDaysLoser, $lastFewLoserDates] = $groupedData(
            $consecutiveSymbols($days, 's_stock_daily_price_data.last_price < s_stock_daily_price_data.previous_close', $startDate, $endDate),
            $startDate,
            $endDate
        );

        $todayHitUpperCP = $todayCircuitData('upper_cp');
        $todayHitLowerCP = $todayCircuitData('lower_cp');

        $circuitDays = 3;
        [$startDate, $endDate] = $this->nseStockController->getDateRange($circuitDays);
        [$lastFewDaysUpperCP, $lastFewDaysUpperCPDate] = $groupedData(
            $consecutiveSymbols($circuitDays, 's_stock_daily_price_data.last_price = s_stock_daily_price_data.upper_cp', $startDate, $endDate),
            $startDate,
            $endDate,
            true
        );
        [$lastFewDaysLowerCP, $lastFewDaysLowerCPDate] = $groupedData(
            $consecutiveSymbols($circuitDays, 's_stock_daily_price_data.last_price = s_stock_daily_price_data.lower_cp', $startDate, $endDate),
            $startDate,
            $endDate,
            true
        );

        return view('last_few_days_stock', compact(
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
        ));
    }

    public function stockPriceList(Request $request)
    {
        $stock_name = $request->get('stock_name') ?? null;
        $today = $this->today;
        $price_min = $request->get('price_min') ?? null;
        $price_max = $request->get('price_max') ?? null;

        $defaultWatchListNames = [
            [
                'key_name' => 'price_0_0_5',
                'name' => 'Price 0-0.5',
                'condition' => 's_stock_daily_price_data.last_price > 0 AND s_stock_daily_price_data.last_price <= 0.5',
            ],
            [
                'key_name' => 'price_0_5_1',
                'name' => 'Price 0.5-1',
                'condition' => 's_stock_daily_price_data.last_price >= 0.5 AND s_stock_daily_price_data.last_price <= 1',
            ],
            [
                'key_name' => 'price_1_5',
                'name' => 'Price 1-5',
                'condition' => 's_stock_daily_price_data.last_price >= 1 AND s_stock_daily_price_data.last_price <= 5',
            ],
            [
                'key_name' => 'price_5_10',
                'name' => 'Price 5-10',
                'condition' => 's_stock_daily_price_data.last_price >= 5 AND s_stock_daily_price_data.last_price <= 10',
            ],
            [
                'key_name' => 'price_10_20',
                'name' => 'Price 10-20',
                'condition' => 's_stock_daily_price_data.last_price >= 10 AND s_stock_daily_price_data.last_price <= 20',
            ],
            [
                'key_name' => 'price_20_50',
                'name' => 'Price 20-50',
                'condition' => 's_stock_daily_price_data.last_price >= 20 AND s_stock_daily_price_data.last_price <= 50',
            ],
            [
                'key_name' => 'price_50_100',
                'name' => 'Price 50-100',
                'condition' => 's_stock_daily_price_data.last_price >= 50 AND s_stock_daily_price_data.last_price <= 100',
            ],
            [
                'key_name' => 'price_100_200',
                'name' => 'Price 100-200',
                'condition' => 's_stock_daily_price_data.last_price >= 100 AND s_stock_daily_price_data.last_price <= 200',
            ],
            [
                'key_name' => 'price_200_500',
                'name' => 'Price 200-500',
                'condition' => 's_stock_daily_price_data.last_price >= 200 AND s_stock_daily_price_data.last_price <= 500',
            ],
            [
                'key_name' => 'price_500_1000',
                'name' => 'Price 500-1000',
                'condition' => 's_stock_daily_price_data.last_price >= 500 AND s_stock_daily_price_data.last_price <= 1000',
            ],
            [
                'key_name' => 'price_1k_1_5k',
                'name' => 'Price 1000-1500',
                'condition' => 's_stock_daily_price_data.last_price >= 1000 AND s_stock_daily_price_data.last_price <= 1500',
            ],
            [
                'key_name' => 'price_1_5k_2k',
                'name' => 'Price 1500-2000',
                'condition' => 's_stock_daily_price_data.last_price >= 1500 AND s_stock_daily_price_data.last_price <= 2000',
            ],
            [
                'key_name' => 'price_2k_5k',
                'name' => 'Price 2000-5000',
                'condition' => 's_stock_daily_price_data.last_price >= 2000 AND s_stock_daily_price_data.last_price <= 5000',
            ],
            [
                'key_name' => 'price_5k_10k',
                'name' => 'Price 5000-10000',
                'condition' => 's_stock_daily_price_data.last_price >= 5000 AND s_stock_daily_price_data.last_price <= 10000',
            ],
            [
                'key_name' => 'price_10000_more',
                'name' => 'Price 10000 More',
                'condition' => 's_stock_daily_price_data.last_price > 10000',
            ]
        ];
        $watchListList = [];

        foreach($defaultWatchListNames as $defaultWatchList):
            $query = DB::table('s_stock_symbols')
                ->join('s_stock_daily_price_data', 's_stock_daily_price_data.symbol', '=', 's_stock_symbols.symbol')
                ->join('s_stock_details', 's_stock_details.symbol', '=', 's_stock_symbols.symbol')
                ->where('s_stock_daily_price_data.date', $today)
                ->where('s_stock_symbols.is_active', true)
                ->whereRaw($defaultWatchList['condition']); // keep this if it's dynamic

            if (!empty($stock_name)) {
                $query->where('s_stock_symbols.symbol', $stock_name);
            } elseif (!empty($price_min) && !empty($price_max)) {
                $query->whereBetween('s_stock_daily_price_data.last_price', [$price_min, $price_max]);
            }

            $stockList = $query
                ->select(
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
                    's_stock_details.week_high_low_max_date'
                )
                ->orderBy('s_stock_daily_price_data.last_price')
                ->orderBy('s_stock_daily_price_data.p_change')
                ->get();

            if ($stockList->isNotEmpty()) {
                $watchListList[$defaultWatchList['key_name']] = [
                    'name' => $defaultWatchList['name'],
                    'stock_list' => $stockList,
                ];
            }
        endforeach;

        $stock_list = StockSymbol::with('details')->where('is_active', true)->get();
        return view('my_watch_list', compact('watchListList','stock_list', 'stock_name'));
    }

    public function sectorStockList(Request $request)
    {
        $stock_name = $request->get('stock_name') ?? null;
        $today = $this->today;
        $price_min = $request->get('price_min') ?? null;
        $price_max = $request->get('price_max') ?? null;

        $sectorData = StockDetails::select('sector')
            // ->where('sector', '<>', '')
            ->groupBy('sector')
            ->orderBy('sector', 'asc')
            ->get();
        $watchListList = [];
        $stockConditions = '1=1';
        if($stock_name != null){
            $stockConditions = "s_stock_symbols.symbol = '".$stock_name."'";
        } elseif (!empty($price_min) && !empty($price_max)) {
            $stockConditions = "s_stock_daily_price_data.last_price between '".$price_min."' and '".$price_max."'";
        }

        foreach($sectorData as $sector){
            $sectorStocks = DB::table('s_stock_details')
                ->join('s_stock_symbols', 's_stock_symbols.symbol', '=', 's_stock_details.symbol')
                ->join('s_stock_daily_price_data', 's_stock_daily_price_data.symbol', '=', 's_stock_symbols.symbol')
                ->where('s_stock_daily_price_data.date', $today)
                ->where('s_stock_symbols.is_active', true)
                ->where('s_stock_details.sector', $sector->sector)
                ->whereRaw($stockConditions)
                ->select(
                    's_stock_details.symbol',
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
                )
                ->get();
            if(count($sectorStocks)>0){
                $watchListList[str_replace([' ', '-', '&'], '_', $sector->sector)]['name'] = $sector->sector;
                $watchListList[str_replace([' ', '-', '&'], '_', $sector->sector)]['stock_list'] = $sectorStocks;
            }
        }

        $stock_list = StockSymbol::with('details')->where('is_active', true)->get();
        return view('my_watch_list', compact('watchListList','stock_list', 'stock_name'));
    }

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

    public function inActiveSymbolWeb(string $symbol)
    {
        try {
            $this->inActiveSymbol($symbol);
        } catch (Exception $e) {
            return response()->json([
                'result' => false,
                'msg' => $e->getMessage(),
            ], 500);
        }

        return redirect()->back();
    }

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
}
