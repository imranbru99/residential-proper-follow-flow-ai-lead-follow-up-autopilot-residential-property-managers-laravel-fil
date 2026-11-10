<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'request_number',
        'opportunity_id',
        'resident_id',
        'property_id',
        'resident_name',
        'address',
        'service_type',
        'priority',
        'status',
        'scheduled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MaintenanceRequest $request): void {
            if (blank($request->request_number)) {
                $request->request_number = 'MR-'.strtoupper(Str::random(8));
            }
        });
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function vendorWorkOrders(): HasMany
    {
        return $this->hasMany(VendorWorkOrder::class);
    }
}
