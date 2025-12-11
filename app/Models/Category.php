<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    protected $fillable=[
        'type',
        //'imgname',
        'image',
    ];

    public function store(){
        return $this->hasMany(Store::class);
    }
}
