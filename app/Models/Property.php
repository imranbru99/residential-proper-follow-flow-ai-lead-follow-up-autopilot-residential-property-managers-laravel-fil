<?php

namespace App\Models;

use App\Services\SlugService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'zip',
        'unit_count',
        'property_type',
        'manager_name',
        'notes',
        'slug',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Property $property): void {
            if (blank($property->slug)) {
                $property->slug = app(SlugService::class)->unique($property->name, 'properties');
            }
        });
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
