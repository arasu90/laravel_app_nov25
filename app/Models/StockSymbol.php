<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockSymbol extends Model
{
    protected $table = 's_stock_symbols';

    protected $fillable = [
        'symbol'
    ];
    public $timestamps = true;

    public function details()
    {
        return $this->hasOne(StockDetails::class, 'symbol', 'symbol');
    }
}
