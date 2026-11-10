<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Inquiry extends Model
{
    protected $fillable = [
        'reference',
        'source',
        'contact_name',
        'contact_email',
        'contact_phone',
        'subject',
        'body',
        'service_interest',
        'ai_category',
        'ai_priority',
        'ai_score',
        'ai_summary',
        'ai_tags',
        'status',
        'assigned_to',
        'resident_id',
        'consent_sms',
        'consent_email',
        'consent_captured_at',
        'consent_source',
        'follow_up_step',
        'next_follow_up_at',
        'last_contacted_at',
        'raw_payload',
        'classified_at',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'ai_tags' => 'array',
            'raw_payload' => 'array',
            'consent_sms' => 'boolean',
            'consent_email' => 'boolean',
            'consent_captured_at' => 'datetime',
            'next_follow_up_at' => 'datetime',
            'last_contacted_at' => 'datetime',
            'classified_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Inquiry $inquiry): void {
            if (blank($inquiry->reference)) {
                $inquiry->reference = 'RPFF-'.strtoupper(Str::random(8));
            }
        });
    }

    public function hasChannelConsent(string $channel): bool
    {
        return match ($channel) {
            'sms' => (bool) $this->consent_sms,
            'email' => (bool) $this->consent_email,
            default => false,
        };
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function opportunity(): HasOne
    {
        return $this->hasOne(Opportunity::class);
    }

    public function classifications(): HasMany
    {
        return $this->hasMany(AiClassification::class);
    }

    public function followUpDrafts(): HasMany
    {
        return $this->hasMany(FollowUpDraft::class);
    }

    public function leadReplies(): HasMany
    {
        return $this->hasMany(LeadReply::class);
    }

    public function escalations(): HasMany
    {
        return $this->hasMany(Escalation::class);
    }
}
