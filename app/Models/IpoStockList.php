<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpoStockList extends Model
{
    protected $table = 's_ipo_stock_lists';

    protected $fillable = [
        'symbol',
        'symbol_name',
        'security_type',
        'issue_start_date',
        'issue_end_date',
        'status',
        'issue_price',
        'issue_price_range',
        'date_of_listing',
    ];

    protected $casts = [
        'issue_start_date' => 'date',
        'issue_end_date' => 'date',
        'issue_price' => 'decimal:2',
        'date_of_listing' => 'date',
    ];
}
