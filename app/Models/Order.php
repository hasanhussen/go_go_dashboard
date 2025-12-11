<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'user_id',
        'address',
        'status',
        'price',
        'notes',
        'x',
        'y',
        'delivery_price',
        'delivery_id',
        'coupon_id',
        'discount',
        'total_price',
        'payment_method',
        'is_paid',
        'cart_count',
        'coupon_name',
        'linked_order_id',
        'total_before_discount'
    ];

    protected $appends = ['calculated_total_price']; 

    public function getCalculatedTotalPriceAttribute()
    {
        return $this->carts->sum(function ($cart) {
            return $cart->old_price; // price من Cart Model
        });
    }

    public function user(){
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function delivery(){
        return $this->belongsTo(User::class,'delivery_id')->withTrashed();
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function logs()
{
    return $this->hasMany(OrderLog::class);
}

}
