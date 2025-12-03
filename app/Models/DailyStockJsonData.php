<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStockJsonData extends Model
{
    protected $table = 's_daily_stock_json_data';
    protected $fillable = [
        'symbol',
        'date',
        'nse_date',
        'daily_data',
    ];
    public $timestamps = true;
    public function symbol()
    {
        return $this->belongsTo(StockSymbol::class, 'symbol', 'symbol');
    }
}
