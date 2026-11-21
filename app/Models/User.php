<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const DEFAULT_PREFERENCES = [
        'theme' => 'dark',
        'date_format' => 'jalali',
        'start_of_week' => 6,
        'daily_goal' => 5,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
        'preferences',
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
            'preferences' => 'array',
        ];
    }

    /**
     * Accessor to merge stored preferences with default preferences.
     *
     * @param  mixed  $value
     * @return array<string, mixed>
     */
    public function getPreferencesAttribute($value): array
    {
        $decoded = is_string($value) ? (json_decode($value, true) ?: []) : ($value ?: []);

        return array_merge(self::DEFAULT_PREFERENCES, $decoded);
    }
}
