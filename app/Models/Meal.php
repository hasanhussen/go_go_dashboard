<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Meal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        //'note',
        'points',
        'price',
        'is_active',
        'image',
        'quantity',
        'appeal',
    ];

        protected $casts = [
    'ban_until' => 'datetime:d-m-Y',
];


        public function store(){
        return $this->belongsTo(Store::class)->withTrashed();
    }

        public function additionals(){
        return $this->belongsToMany(Additional::class,'additional_meal');
    }

    public function additionalsWithTrashed()
{
    return $this->belongsToMany(Additional::class,'additional_meal')->withTrashed();
}

        public function usercart()
    {
        return $this->belongsToMany(User::class,'carts');
    }

    public function variants()
{
    // return $this->hasMany(MealVariant::class, 'meal_id');
    return $this->hasMany(MealVariant::class);
}

public function variantsWithTrashed()
{
    return $this->hasMany(MealVariant::class)->withTrashed();
}


public function refreshTotalQuantity()
{
    // لو المنتج ما عندو مقاسات، لا تعمل شي
    if ($this->variants()->count() == 0) {
        return; 
    }

    $total = $this->variants()->sum('quantity');

    $this->quantity = $total;
    $this->save();
}


}
