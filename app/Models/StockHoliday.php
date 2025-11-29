<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockHoliday extends Model
{
    protected $table = 's_stock_holiday_list';
    protected $fillable = ['year', 'date', 'week_day', 'description'];
}
