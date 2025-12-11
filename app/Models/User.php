<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasImageUpload;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as AuthCanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail,AuthCanResetPassword
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes , HasImageUpload,CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'password',
        'image',
        'status',
        'fcm_token'
    ];

        protected $casts = [
    'ban_until' => 'datetime:d-m-Y',
];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function fcmTokens(){
        return $this->hasMany(FcmToken::class);
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }

      public function stores(){
        return $this->hasMany(Store::class);
    }

    public function deliveryManOrders(){
        return $this->hasMany(Order::class, 'delivery_id');
    }

    public function activeOrders()
{
    return $this->hasMany(Order::class, 'delivery_id')->whereIn('status', ['1','2','3']);
}


    public function followStore()
    {
        return $this->belongsToMany(Store::class,'user_store');
    }

        public function mealcart()
    {
        return $this->belongsToMany(Meal::class,'carts');
    }
}
