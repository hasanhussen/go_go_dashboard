<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $fillable = [
        'description', 'phone', 'email', 'address',
        'facebook', 'instagram', 'whatsapp'
    ];
}

