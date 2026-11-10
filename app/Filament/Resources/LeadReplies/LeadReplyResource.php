<?php

namespace App\Filament\Resources\LeadReplies;

use App\Filament\Resources\LeadReplies\Pages\ManageLeadReplies;
use App\Models\LeadReply;
use App\Services\ModuleManager;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

class LeadReplyResource extends Resource
{
    protected static ?string $model = LeadReply::class;

    protected static ?string $navigationLabel = 'Lead Replies';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Follow-Up Autopilot';

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleManager::class)->enabled('reply_escalation');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('inquiry_id')->relationship('inquiry', 'reference')->required()->searchable()->preload(),
            Select::make('channel')->options(['email' => 'Email', 'sms' => 'SMS'])->required(),
            Textarea::make('body')->required()->rows(5)->columnSpanFull(),
            Toggle::make('is_qualified'),
            TextInput::make('ai_interest_score')->numeric(),
            Select::make('status')->options([
                'received' => 'Received',
                'analyzed' => 'Analyzed',
                'escalated' => 'Escalated',
                'dismissed' => 'Dismissed',
            ]),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inquiry.reference')->label('Lead'),
                TextColumn::make('channel')->badge(),
                TextColumn::make('body')->limit(40),
                TextColumn::make('ai_interest_score')->label('Interest'),
                IconColumn::make('is_qualified')->boolean()->label('Qualified'),
                TextColumn::make('status')->badge(),
                TextColumn::make('received_at')->since(),
            ])
            ->defaultSort('created_at', 'desc')
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
        return ['index' => ManageLeadReplies::route('/')];
    }
}
