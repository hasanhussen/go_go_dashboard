<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreWorkingHour extends Model
{
    protected $fillable = [
        'store_id',
        'day',
        'open_at',
        'close_at',
        // 'is_open',
        // 'is_24',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}

