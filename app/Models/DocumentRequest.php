<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocumentRequest extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tracking_id',
        'email',
        'first_name',
        'middle_name',
        'last_name',
        'lrn',
        'grade_level',
        'section',
        'track_strand',
        'school_year_last_attended',
        'photo_path',
        'document_type_id',
        'purpose',
        'quantity',
        'signature',
        'status',
        'estimated_completion_date',
        'completed_at',
        'admin_remarks',
        'processed_by',
        'otp_code',
        'otp_expires_at',
        'otp_verified',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'otp_expires_at' => 'datetime',
        'otp_verified' => 'boolean',
        'estimated_completion_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (DocumentRequest $request) {
            if (empty($request->tracking_id)) {
                $request->tracking_id = static::generateTrackingId();
            }
        });

        static::created(function (DocumentRequest $request) {
            $request->logs()->create([
                'action' => 'request_created',
                'description' => 'Document request has been submitted and is pending verification.',
                'new_value' => 'Pending',
            ]);
        });
    }

    /**
     * Generate a unique tracking ID in format: BNHS-XXXXXXXX (8 random alphanumeric characters)
     * This format is not easily guessable compared to sequential numbers.
     */
    public static function generateTrackingId(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Excluded confusing chars: 0, O, I, 1
        $maxAttempts = 100;
        $attempt = 0;

        do {
            $code = 'BNHS-';
            for ($i = 0; $i < 8; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }

            // Check if this tracking ID already exists
            $exists = static::where('tracking_id', $code)->exists();
            $attempt++;

            if (!$exists) {
                return $code;
            }
        } while ($attempt < $maxAttempts);

        // Fallback: if we can't generate a unique one after max attempts, append a timestamp
        return $code . '-' . substr(str_replace(['-', ':'], '', now()->toDateTimeString()), -6);
    }

    /**
     * Get the full name of the student.
     */
    public function getFullNameAttribute(): string
    {
        $parts = [$this->first_name];
        if (! empty($this->middle_name)) {
            $parts[] = strtoupper(substr($this->middle_name, 0, 1)).'.';
        }
        $parts[] = $this->last_name;

        return implode(' ', array_filter($parts));
    }

    /**
     * Get the document type for this request.
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Get the admin who processed this request.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get all logs for this request.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(RequestLog::class);
    }

    /**
     * Get all email logs for this request.
     */
    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    /**
     * Generate and set OTP for this request.
     */
    public function generateOtp(): string
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'otp_verified' => false,
        ]);

        return $otp;
    }

    /**
     * Verify the OTP.
     */
    public function verifyOtp(string $code): bool
    {
        if ($this->otp_code !== $code) {
            return false;
        }

        if ($this->otp_expires_at && $this->otp_expires_at->isPast()) {
            return false;
        }

        $this->update(['otp_verified' => true]);
        
        return true;
    }

    /**
     * Update status with logging.
     */
    public function updateStatus(string $newStatus, ?User $user = null, ?string $notes = null): bool
    {
        $oldStatus = $this->status;
        
        $this->status = $newStatus;
        
        if ($notes) {
            $this->admin_remarks = $notes;
        }
        
        if ($user) {
            $this->processed_by = $user->id;
        }
        
        $this->save();

        // Log the change
        $this->logs()->create([
            'user_id' => $user?->id,
            'action' => 'status_change',
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
            'description' => $notes ?: "Status changed from {$oldStatus} to {$newStatus}",
        ]);

        return true;
    }

    /**
     * Scope for searching by email.
     */
    public function scopeByEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Scope for searching by tracking ID.
     */
    public function scopeByTrackingId($query, string $trackingId)
    {
        return $query->where('tracking_id', $trackingId);
    }

    /**
     * Check if track/strand is required based on grade level.
     */
    public static function requiresTrackStrand(string $gradeLevel): bool
    {
        return in_array($gradeLevel, ['Grade 11', 'Grade 12']);
    }
}

