<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable=[
        'user_id',
        'meal_id',
        'quantity',
        'old_price',
        //'old_quantity',
        'old_meal_price',
        'order_id',
        'variant_id',
    ];


    protected $appends = ['price'];

    public function meal()
{
    return $this->belongsTo(Meal::class, 'meal_id')->withTrashed();
}

public function user()
{
    return $this->belongsTo(User::class);
}

    public function additionalItems()
    {
        return $this->belongsToMany(Additional::class,'additional_cart')
        ->withPivot(['quantity','old_additional_price'])->withTimestamps()->withTrashed();
    }

    public function variant()
{
    return $this->belongsTo(MealVariant::class, 'variant_id')->withTrashed();
}


    public function order()
{
    return $this->belongsTo(Order::class);
}


public function getTotalPriceAttribute()
{
    // 1) حساب سعر المنتج أو المقاس
    $mealPrice = 0;

    if ($this->meal) {

        // إذا كان للوجبة سعر → منتج عادي
        if (!is_null($this->meal->price) && $this->meal->price > 0) {
            $mealPrice = floatval($this->meal->price) * $this->quantity;
        }
    
        else if ($this->variant_id) {
            $variant = $this->meal?->variants()->withTrashed()->find($this->variant_id);
            if ($variant) {
                $mealPrice = floatval($variant->price) * $this->quantity;
            }
        }


    }

    // 2) حساب سعر الإضافات
    $additionalPrice = $this->additionalItems->sum(function ($item) {
        return floatval($item->price) * ($item->pivot->quantity ?? 0);
    });

    // 3) المجموع
    return $mealPrice + $additionalPrice;
}


public function getPriceAttribute() {
    return $this->total_price;
}



}
