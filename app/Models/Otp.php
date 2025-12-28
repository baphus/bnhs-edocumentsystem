<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'code',
        'purpose',
        'expires_at',
        'used',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Generate a new OTP for an email.
     */
    public static function generateFor(string $email, string $purpose = 'request'): self
    {
        // Invalidate any existing OTPs for this email and purpose
        static::where('email', $email)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->update(['used' => true]);

        // Generate new 6-digit OTP
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        return static::create([
            'email' => $email,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    /**
     * Verify an OTP.
     */
    public static function verify(string $email, string $code, string $purpose = 'request'): bool
    {
        $otp = static::where('email', $email)
            ->where('code', $code)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->update(['used' => true]);
        
        return true;
    }

    /**
     * Check if OTP is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP is valid.
     */
    public function isValid(): bool
    {
        return !$this->used && !$this->isExpired();
    }
}

