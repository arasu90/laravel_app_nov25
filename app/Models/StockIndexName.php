<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockIndexName extends Model
{
    protected $table = 's_stock_index_name';
    protected $fillable = ['index_symbol', 'index_name', 'is_active'];
    public $timestamps = true;
}
