<?php

namespace App\Filament\Resources\VendorWorkOrders;

use App\Filament\Resources\VendorWorkOrders\Pages\ManageVendorWorkOrders;
use App\Models\VendorWorkOrder;
use App\Services\ModuleManager;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class VendorWorkOrderResource extends Resource
{
    protected static ?string $model = VendorWorkOrder::class;

    protected static ?string $navigationLabel = 'Work Orders';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|UnitEnum|null $navigationGroup = 'Property Ops';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleManager::class)->enabled('vendor_work_orders');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('work_order_number')->disabled()->dehydrated(false),
            Select::make('maintenance_request_id')->relationship('maintenanceRequest', 'request_number')->searchable()->preload(),
            Select::make('vendor_id')->relationship('vendor', 'name')->searchable()->preload(),
            Select::make('status')->options([
                'draft' => 'Draft',
                'dispatched' => 'Dispatched',
                'on_site' => 'On Site',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ])->native(false)->default('draft'),
            DateTimePicker::make('scheduled_start'),
            DateTimePicker::make('scheduled_end'),
            TagsInput::make('safety_checklist'),
            Textarea::make('completion_notes')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('work_order_number')->searchable(),
                TextColumn::make('maintenanceRequest.request_number')->label('Maintenance'),
                TextColumn::make('vendor.name')->label('Vendor'),
                TextColumn::make('status')->badge(),
                TextColumn::make('scheduled_start')->dateTime(),
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
        return ['index' => ManageVendorWorkOrders::route('/')];
    }
}
