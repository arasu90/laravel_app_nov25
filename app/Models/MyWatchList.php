<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class MyWatchList extends Model
{
    protected $table = 's_watchlist_master';
    
    protected $fillable = [
        'watchlist_name',
        'user_id',
        'is_active',
    ];

    public $timestamps = true;

    public function watchlistItems()
    {
        return $this->hasMany(MyWatchlistItem::class, 'watchlist_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
