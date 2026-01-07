<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory, \App\Traits\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'category',
        'description',
        'processing_days',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'processing_days' => 'integer',
    ];

    /**
     * Get all document requests for this document type.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class);
    }
}

