<?php

namespace App\Filament\Resources\FollowUpSequences;

use App\Filament\Resources\FollowUpSequences\Pages\ManageFollowUpSequences;
use App\Models\FollowUpSequence;
use App\Services\ModuleManager;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class FollowUpSequenceResource extends Resource
{
    protected static ?string $model = FollowUpSequence::class;

    protected static ?string $navigationLabel = 'Sequences';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Follow-Up Autopilot';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleManager::class)->enabled('follow_up_sequences');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('slug')->maxLength(255),
            Select::make('channel_preference')->options([
                'email' => 'Email',
                'sms' => 'SMS',
            ])->required(),
            Toggle::make('is_active')->default(true),
            Textarea::make('description')->columnSpanFull(),
            Repeater::make('steps')->schema([
                TextInput::make('day')->numeric()->required()->default(0),
                Select::make('channel')->options(['email' => 'Email', 'sms' => 'SMS'])->required(),
                Textarea::make('template')->required()->rows(3),
            ])->columnSpanFull()->defaultItems(1),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('channel_preference')->badge(),
                TextColumn::make('steps')->label('Steps')->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('updated_at')->since(),
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
        return ['index' => ManageFollowUpSequences::route('/')];
    }
}
