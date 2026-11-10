<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Escalation extends Model
{
    protected $fillable = [
        'reference',
        'inquiry_id',
        'lead_reply_id',
        'assigned_to',
        'reason',
        'priority',
        'status',
        'sla_due_at',
        'resolved_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'sla_due_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Escalation $escalation): void {
            if (blank($escalation->reference)) {
                $escalation->reference = 'ESC-'.strtoupper(Str::random(8));
            }
        });
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function leadReply(): BelongsTo
    {
        return $this->belongsTo(LeadReply::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
