<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockDetails extends Model
{
    protected $table = 's_stock_details';

    protected $fillable = [
        'symbol',
        'company_name',
        'macro',
        'sector',
        'basic_industry',
        'industry',
        'isin',
        'listing_date',
        'status',
        'series',
        'last_update_time',
        'pdsectorind',
        'trading_status',
        'trading_segment',
        'face_value',
        'week_high_low_min',
        'week_high_low_min_date',
        'week_high_low_max',
        'week_high_low_max_date',
        'is_active',
        'surveillance_surv',
        'surveillance_desc',
        'stock_date',
        'stock_last_price',
        'stock_change',
        'stock_p_change',
    ];
    public $timestamps = true;

    public function symbol()
    {
        return $this->belongsTo(StockSymbol::class, 'symbol', 'symbol');
    }
}
