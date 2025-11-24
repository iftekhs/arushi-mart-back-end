<?php

namespace App\Models;

use App\Traits\UserOtpUtilities;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, UserOtpUtilities;

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code_hash',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'otp_expires_at' => 'datetime',
        'otp_requested_at' => 'datetime',
        'otp_used_at' => 'datetime',
    ];

    public function shippingAddresses(): HasMany
    {
        return $this->hasMany(ShippingAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
