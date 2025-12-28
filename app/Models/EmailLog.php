<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_request_id',
        'recipient_email',
        'subject',
        'status',
        'error_message',
        'sent_at',
        'delivered_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Get the document request this email log belongs to.
     */
    public function documentRequest(): BelongsTo
    {
        return $this->belongsTo(DocumentRequest::class);
    }
}
