<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StockSymbol;
use App\Models\MyWatchList;
class MyWatchlistItem extends Model
{
    protected $table = 's_watchlist_items';

    protected $fillable = [
        'watchlist_id',
        'symbol',
        'is_active',
    ];

    public $timestamps = true;

    public function watchlist()
    {
        return $this->belongsTo(MyWatchList::class, 'watchlist_id', 'id');
    }
    
    public function stockSymbol()
    {
        return $this->belongsTo(StockSymbol::class, 'symbol', 'symbol');
    }
}
