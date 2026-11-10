<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VendorWorkOrder extends Model
{
    protected $fillable = [
        'work_order_number',
        'maintenance_request_id',
        'vendor_id',
        'status',
        'scheduled_start',
        'scheduled_end',
        'completion_notes',
        'safety_checklist',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_start' => 'datetime',
            'scheduled_end' => 'datetime',
            'safety_checklist' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (VendorWorkOrder $order): void {
            if (blank($order->work_order_number)) {
                $order->work_order_number = 'VWO-'.strtoupper(Str::random(8));
            }
        });
    }

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
