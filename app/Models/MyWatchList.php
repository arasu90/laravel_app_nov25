<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class MyWatchList extends Model
{
    protected $table = 's_watch_list_master';
    
    protected $fillable = [
        'watch_list_name',
        'user_id',
        'is_active',
    ];

    public $timestamps = true;

    public function watchListItems()
    {
        return $this->hasMany(MyWatchListItem::class, 'watch_list_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
