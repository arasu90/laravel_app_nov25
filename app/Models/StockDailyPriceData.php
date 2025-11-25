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
    ];
    public $timestamps = true;
    public function symbol()
    {
        return $this->belongsTo(StockSymmbol::class, 'symbol', 'symbol');
    }
}
