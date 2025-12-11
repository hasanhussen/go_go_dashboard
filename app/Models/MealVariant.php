<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MealVariant extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'meal_variants';

    protected $fillable = [
        'meal_id',
        'name',
        'price',
        'quantity',
    ];

    public function meal()
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }

    //     public function cartItems()
    // {
    //     return $this->belongsToMany(Cart::class,'cart_variants');
    // }
}
