<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
     protected $fillable=[
        'order_id',
        'payment_intent_id',
        'status',
        'amount',
        'stripe_payment_method_id',
    ];

    public function order(){
    return $this->belongsTo(Order::class);
}


}
