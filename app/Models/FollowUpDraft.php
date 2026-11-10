<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FollowUpDraft extends Model
{
    protected $fillable = [
        'inquiry_id',
        'sequence_id',
        'created_by',
        'step_number',
        'channel',
        'subject',
        'body',
        'consent_verified',
        'status',
        'scheduled_for',
        'approved_at',
        'sent_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'consent_verified' => 'boolean',
            'scheduled_for' => 'datetime',
            'approved_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(FollowUpSequence::class, 'sequence_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(LeadReply::class);
    }
}
