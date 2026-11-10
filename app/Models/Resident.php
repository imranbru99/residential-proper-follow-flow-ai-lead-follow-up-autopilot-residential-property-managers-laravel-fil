<?php

namespace App\Models;

use App\Services\SlugService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resident extends Model
{
    protected $fillable = [
        'property_id',
        'name',
        'email',
        'phone',
        'unit_number',
        'address',
        'city',
        'state',
        'zip',
        'resident_type',
        'notes',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (Resident $resident): void {
            if (blank($resident->slug)) {
                $resident->slug = app(SlugService::class)->unique($resident->name, 'residents');
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
