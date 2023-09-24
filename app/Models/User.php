<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstName',
        'lastName',
        'country',
        'email',
        'password',
        'user_level',
        'birthdat',
        'id_card',
        'profile_img',
        'id_back',
        'phone_number',
        'status',
        'isVerified',
        'note',
        'total_usd'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isAdmin(){
        return $this->user_level === 1;
    }

    public function isGust() {
        return $this->user_level !== 0;
    }

    public function privateKey() {
        return $this->hasOne(UserPrivate::class);
    }

    public function activitiesLogs() {

        return $this->hasMany(ActivitiesLog::class, 'user_id');
        
    }

    public function balance() {
        return $this->hasMany(UserBalance::class, 'user_id');
    }

    public function transcations() {
        return $this->hasMany(Transaction::class, 'user_id');
    }
}
