<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'license_number',
        'specialties',
        'availability_status',
        'service_area',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'specialties' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendorWorkOrders(): HasMany
    {
        return $this->hasMany(VendorWorkOrder::class);
    }
}
