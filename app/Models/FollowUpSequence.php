<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FollowUpSequence extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'channel_preference',
        'steps',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'steps' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FollowUpSequence $sequence): void {
            if (blank($sequence->slug)) {
                $sequence->slug = Str::slug($sequence->name).'-'.Str::lower(Str::random(4));
            }
        });
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(FollowUpDraft::class, 'sequence_id');
    }
}
