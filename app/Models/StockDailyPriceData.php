<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StockSymbol;
class StockDailyPriceData extends Model
{
    protected $table = 's_stock_daily_price_data';
    protected $fillable = [
        'symbol',
        'date',
        'last_price',
        'change',
        'p_change',
        'previous_close',
        'open',
        'close',
        'lower_cp',
        'upper_cp',
        'intra_day_high_low_min',
        'intra_day_high_low_max',
        'day_reocrds',
        'is_52_week_high',
        'is_52_week_low',
        'is_52_week_high_value',
        'is_52_week_low_value',
    ];
    public $timestamps = true;
    public function symbol()
    {
        return $this->belongsTo(StockSymbol::class, 'symbol', 'symbol');
    }
    public function details()
    {
        return $this->belongsTo(StockDetails::class, 'symbol', 'symbol');
    }
}
