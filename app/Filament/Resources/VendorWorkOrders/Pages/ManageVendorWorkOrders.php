<?php

namespace App\Filament\Resources\VendorWorkOrders\Pages;

use App\Filament\Resources\VendorWorkOrders\VendorWorkOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageVendorWorkOrders extends ManageRecords
{
    protected static string $resource = VendorWorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
