<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NseIndexDayRecord extends Model
{
    protected $table = 's_nes_index_day_records';

    protected $fillable = [
        'index_symbol',
        'trade_date',
        'value_last',
        'value_change',
        'value_p_change',
        'value_open',
        'day_high',
        'day_low',
        'previous_close',
        'year_high',
        'year_low',
        'declines',
        'advances',
        'unchanged',
    ];
}
