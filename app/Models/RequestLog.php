<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_request_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'description',
    ];

    /**
     * Get the document request this log belongs to.
     */
    public function documentRequest(): BelongsTo
    {
        return $this->belongsTo(DocumentRequest::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

