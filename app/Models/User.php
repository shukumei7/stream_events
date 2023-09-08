<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Follower;
use App\Models\Subscriber;
use App\Models\Donation;
use App\Models\MerchSale;
use App\Models\Flag;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'fb_id',
        'fb_token',
        'password',
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

    public function followers(): HasMany 
    {
        return $this->hasMany(Follower::class);
    }

    public function subscribers(): HasMany 
    {
        return $this->hasMany(Subscriber::class);
    }

    public function donations(): HasMany 
    {
        return $this->hasMany(Donation::class);
    }

    public function sales(): HasMany 
    {
        return $this->hasMany(MerchSale::class);
    }

    public function flags(): HasMany 
    {
        return $this->hasMany(Flag::class);
    }
}
