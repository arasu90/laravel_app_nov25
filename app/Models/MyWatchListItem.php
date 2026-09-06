<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StockSymbol;
use App\Models\MyWatchList;
class MyWatchListItem extends Model
{
    protected $table = 's_watch_list_items';

    protected $fillable = [
        'watch_list_id',
        'symbol',
        'is_active',
    ];

    public $timestamps = true;

    public function watchList()
    {
        return $this->belongsTo(MyWatchList::class, 'watch_list_id', 'id');
    }
    
    public function stockSymbol()
    {
        return $this->belongsTo(StockSymbol::class, 'symbol', 'symbol');
    }
}
