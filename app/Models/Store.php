<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\StoreWorkingHour;


class Store extends Model
{
    use HasFactory , SoftDeletes ;
    protected $fillable=[
        'user_id',
        'category_id',
        'city_id',
        'name',
        'delivery',
        'image',
        'address',
        'phone',
        'cover',
        'special',
        'x',
        'y',
        'appeal',
    ];

    protected $casts = [
    'ban_until' => 'datetime:d-m-Y',
];

    public function workingHours()
    {
        return $this->hasMany(StoreWorkingHour::class);
    }

    public function isOpenNow()
    {
        $now = \Carbon\Carbon::now('Asia/Damascus');
        //$day = strtolower($now->format('l')); // monday, tuesday ...
$daysMap = [
    'monday'    => 'الاثنين',
    'tuesday'   => 'الثلاثاء',
    'wednesday' => 'الأربعاء',
    'thursday'  => 'الخميس',
    'friday'    => 'الجمعة',
    'saturday'  => 'السبت',
    'sunday'    => 'الأحد',
];

$dayEn = strtolower($now->format('l'));
$dayAr = $daysMap[$dayEn] ?? null;

$work = $this->workingHours()->where('day', $dayAr)->first();
        // جلب السطر الخاص بهذا اليوم
       // $work = $this->workingHours()->where('day', $day)->first();

        if (!$work || !$work->open_at || !$work->close_at) {
            return false; // لا يوجد دوام اليوم
        }

$open  = \Carbon\Carbon::createFromFormat('H:i:s', $work->open_at, 'Asia/Damascus');
$close = \Carbon\Carbon::createFromFormat('H:i:s', $work->close_at, 'Asia/Damascus');


        return $now->between($open, $close);
        // return true;
    }



    public function user(){
        return $this->belongsTo(User::class);
    }
    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function followedByUser()
    {
        return $this->belongsToMany(User::class,'user_store');
    }

        public function meals(){
        return $this->hasMany(Meal::class);
    }

    public function additionals(){
        return $this->hasMany(Additional::class);
    }
}
