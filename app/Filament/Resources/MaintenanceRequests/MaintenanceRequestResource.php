<?php

namespace App\Filament\Resources\MaintenanceRequests;

use App\Filament\Resources\MaintenanceRequests\Pages\ManageMaintenanceRequests;
use App\Models\MaintenanceRequest;
use App\Services\ModuleManager;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class MaintenanceRequestResource extends Resource
{
    protected static ?string $model = MaintenanceRequest::class;

    protected static ?string $navigationLabel = 'Maintenance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Property Ops';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleManager::class)->enabled('maintenance_requests');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('request_number')->disabled()->dehydrated(false),
            Select::make('property_id')->relationship('property', 'name')->searchable()->preload(),
            Select::make('resident_id')->relationship('resident', 'name')->searchable()->preload(),
            Select::make('opportunity_id')->relationship('opportunity', 'title')->searchable()->preload(),
            TextInput::make('resident_name')->required(),
            TextInput::make('address'),
            TextInput::make('service_type'),
            Select::make('priority')->options([
                'low' => 'Low',
                'normal' => 'Normal',
                'high' => 'High',
                'urgent' => 'Urgent',
            ])->native(false)->default('normal'),
            Select::make('status')->options([
                'intake' => 'Intake',
                'scheduled' => 'Scheduled',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ])->native(false)->default('intake'),
            DateTimePicker::make('scheduled_at'),
            Textarea::make('notes')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')->searchable(),
                TextColumn::make('resident_name')->label('Resident'),
                TextColumn::make('property.name')->label('Property'),
                TextColumn::make('service_type'),
                TextColumn::make('priority')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('scheduled_at')->dateTime(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageMaintenanceRequests::route('/')];
    }
}
