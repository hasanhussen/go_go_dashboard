<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
        protected $fillable=[
        'name',
        'discount',
        'count',
        'notes',
        'details',
        'status',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
    'valid_from' => 'datetime',
    'valid_to' => 'datetime',
];


        public function orders()
{
    return $this->hasMany(Order::class);
}

public function checkStatus()
{
    $now = now();

    // 🔒 إذا الكوبون معطل يدويًا من الأدمن
    if ($this->status === 'inactive') {
        return 'inactive';
    }

    if ($this->valid_from > $now) {
        return 'not_started';
    }

    if ($this->valid_to !== null && $this->valid_to < $now) {
        return 'expired';
    }

    if ($this->count <= 0) {
        return 'exhausted';
    }
    

    return 'valid';
}

}
