<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordCode extends Model
{
    protected $fillable = ['email','code','expires_at','attempts'];
    protected $casts = [
    'expires_at' => 'datetime',
];

}
