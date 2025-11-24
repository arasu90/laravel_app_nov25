<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockSectorIndex extends Model
{
    protected $table = 's_stock_sector_index';
    protected $fillable = [
        'sector',
        'is_active',
    ];
    public $timestamps = true;
}   
