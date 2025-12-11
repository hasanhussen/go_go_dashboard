<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Additional extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable=[
        'meal_id',
        'name',
        'price',
        'store_id',
        'quantity',
    ];

        public function meals(){
        return $this->belongsToMany(Meal::class,'additional_meal');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }


    public function cartItems()
    {
        return $this->belongsToMany(Cart::class,'additional_cart');
    }
}
