<?php

namespace App\Filament\Resources\Residents;

use App\Filament\Resources\Residents\Pages\ManageResidents;
use App\Models\Resident;
use App\Services\ModuleManager;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ResidentResource extends Resource
{
    protected static ?string $model = Resident::class;

    protected static ?string $navigationLabel = 'Residents';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Property Ops';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleManager::class)->enabled('residents');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')->relationship('property', 'name')->searchable()->preload(),
            TextInput::make('name')->required(),
            TextInput::make('email')->email(),
            TextInput::make('phone'),
            TextInput::make('unit_number'),
            Select::make('resident_type')->options([
                'prospect' => 'Prospect',
                'applicant' => 'Applicant',
                'resident' => 'Resident',
                'former' => 'Former',
            ])->native(false)->default('prospect'),
            TextInput::make('address'),
            TextInput::make('city'),
            TextInput::make('state')->maxLength(2),
            TextInput::make('zip'),
            TextInput::make('slug')->disabled(),
            Textarea::make('notes')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('property.name')->label('Property'),
                TextColumn::make('unit_number')->label('Unit'),
                TextColumn::make('resident_type')->badge(),
                TextColumn::make('email'),
                TextColumn::make('phone'),
                TextColumn::make('city'),
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
        return ['index' => ManageResidents::route('/')];
    }
}
