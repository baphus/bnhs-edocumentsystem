<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
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
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the request logs created by this user.
     */
    public function requestLogs(): HasMany
    {
        return $this->hasMany(RequestLog::class);
    }

    /**
     * Get requests processed by this user.
     */
    public function processedRequests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class, 'processed_by');
    }

    /**
     * Check if user is a superadmin.
     */
    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user is a registrar.
     */
    public function isRegistrar(): bool
    {
        return $this->role === 'registrar';
    }

    /**
     * Check if user is an admin (superadmin or registrar).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['superadmin', 'registrar']);
    }

    /**
     * Get the user's name with middle names as initials.
     */
    public function getFormattedNameAttribute(): string
    {
        $parts = explode(' ', $this->name);
        if (count($parts) > 2) {
            $firstName = array_shift($parts);
            $lastName = array_pop($parts);
            $middleInitials = '';
            foreach ($parts as $part) {
                $middleInitials .= strtoupper(substr($part, 0, 1)).'. ';
            }

            return $firstName.' '.trim($middleInitials).' '.$lastName;
        }

        return $this->name;
    }
}
