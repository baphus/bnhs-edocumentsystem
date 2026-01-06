<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_role',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Relationship to the user who performed the action
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Dynamic relationship to the subject model
    public function subject()
    {
        return $this->morphTo('model');
    }
}
