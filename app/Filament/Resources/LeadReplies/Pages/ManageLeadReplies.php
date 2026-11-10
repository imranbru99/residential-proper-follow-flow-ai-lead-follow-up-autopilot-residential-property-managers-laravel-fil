<?php

namespace App\Filament\Resources\LeadReplies\Pages;

use App\Filament\Resources\LeadReplies\LeadReplyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLeadReplies extends ManageRecords
{
    protected static string $resource = LeadReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
