<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LeadReply extends Model
{
    protected $fillable = [
        'inquiry_id',
        'follow_up_draft_id',
        'channel',
        'body',
        'is_qualified',
        'ai_interest_score',
        'ai_signals',
        'status',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'is_qualified' => 'boolean',
            'ai_signals' => 'array',
            'received_at' => 'datetime',
        ];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function followUpDraft(): BelongsTo
    {
        return $this->belongsTo(FollowUpDraft::class);
    }

    public function escalation(): HasOne
    {
        return $this->hasOne(Escalation::class);
    }
}
