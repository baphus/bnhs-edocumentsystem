<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, \App\Traits\Auditable;

    // Role constants
    public const ROLE_ADMIN = 'admin';
    public const ROLE_REGISTRAR = 'registrar';
    public const ROLE_GUEST = 'guest';

    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

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
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is a registrar.
     */
    public function isRegistrar(): bool
    {
        return $this->role === self::ROLE_REGISTRAR;
    }

    /**
     * Check if user is a guest (requester).
     */
    public function isGuest(): bool
    {
        return $this->role === self::ROLE_GUEST;
    }

    /**
     * Check if user has admin or registrar role.
     */
    public function hasAdminAccess(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_REGISTRAR]);
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
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
