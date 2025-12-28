<?php

namespace App\Services;

use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class OtpService
{
    /**
     * Generate and send OTP to email.
     */
    public function sendOtp(string $email, string $purpose = 'request'): array
    {
        $otp = Otp::generateFor($email, $purpose);

        // Send email with OTP
        try {
            Mail::to($email)->send(new OtpMail($otp->code, $purpose));
            
            return [
                'success' => true,
                'message' => 'OTP sent successfully to your email.',
                'expires_at' => $otp->expires_at->toISOString(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify OTP.
     */
    public function verifyOtp(string $email, string $code, string $purpose = 'request'): array
    {
        $isValid = Otp::verify($email, $code, $purpose);

        if ($isValid) {
            return [
                'success' => true,
                'message' => 'OTP verified successfully.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid or expired OTP. Please request a new one.',
        ];
    }

    /**
     * Check if there's a recent valid OTP for the email.
     */
    public function hasValidOtp(string $email, string $purpose = 'request'): bool
    {
        return Otp::where('email', $email)
            ->where('purpose', $purpose)
            ->where('used', true)
            ->where('updated_at', '>', now()->subMinutes(30))
            ->exists();
    }
}

