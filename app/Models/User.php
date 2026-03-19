<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Reservation;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'number',
        'address',
        'profile_photo',   // ✅ ADD THIS
        'role',
        'last_seen',
    ];

    /**
     * Hidden attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'last_seen'         => 'datetime',
    ];

    /* ===============================
       RELATIONSHIPS
    =============================== */

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /* ===============================
       ROLE HELPERS
    =============================== */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /* ===============================
       FACEBOOK-STYLE ONLINE STATUS
    =============================== */

    /**
     * User is online if active within last 5 minutes
     */
    public function isOnline(): bool
    {
        return $this->role === 'user'
            && $this->last_seen
            && $this->last_seen->gt(now()->subMinutes(5));
    }

    /**
     * Human-readable last seen time
     */
    public function lastSeenForHumans(): string
    {
        return $this->last_seen
            ? $this->last_seen->diffForHumans()
            : 'never';
    }

    /* ===============================
       PROFILE PHOTO ACCESSOR  
    =============================== */

    /**
     * Returns full URL for profile photo
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        return $this->profile_photo
            ? asset('storage/' . $this->profile_photo)
            : asset('images/user-icon.png');
    }
}
