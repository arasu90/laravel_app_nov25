<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyPortfolioStock extends Model
{
    protected $table = 's_portfolio_stocks';
    protected $fillable = [
        'symbol',
        'buy_price',
        'buy_qty',
        'buy_date',
    ];
    public $timestamps = true;
    public function stockSymbol()
    {
        return $this->belongsTo(StockSymbol::class, 'symbol', 'symbol');
    }
    public function details()
    {
        return $this->belongsTo(StockDetails::class, 'symbol', 'symbol');
    }
    public function stockDailyPriceData()
    {
        return $this->belongsTo(StockDailyPriceData::class, 'symbol', 'symbol');
    }
}
