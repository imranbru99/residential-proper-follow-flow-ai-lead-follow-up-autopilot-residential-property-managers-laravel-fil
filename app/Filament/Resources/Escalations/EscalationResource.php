<?php

namespace App\Filament\Resources\Escalations;

use App\Filament\Resources\Escalations\Pages\ManageEscalations;
use App\Models\Escalation;
use App\Services\FollowUpWorkflowService;
use App\Services\ModuleManager;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class EscalationResource extends Resource
{
    protected static ?string $model = Escalation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static string|UnitEnum|null $navigationGroup = 'Follow-Up Autopilot';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleManager::class)->enabled('reply_escalation');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('inquiry_id')->relationship('inquiry', 'reference')->required()->searchable()->preload(),
            Select::make('assigned_to')->relationship('assignee', 'name')->searchable()->preload(),
            TextInput::make('reason')->required()->columnSpanFull(),
            Select::make('priority')->options([
                'urgent' => 'Urgent',
                'high' => 'High',
                'normal' => 'Normal',
            ])->required(),
            Select::make('status')->options([
                'open' => 'Open',
                'acknowledged' => 'Acknowledged',
                'resolved' => 'Resolved',
                'cancelled' => 'Cancelled',
            ])->required(),
            Textarea::make('resolution_notes')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable()->sortable(),
                TextColumn::make('inquiry.reference')->label('Lead'),
                TextColumn::make('reason')->limit(40),
                TextColumn::make('priority')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('assignee.name')->label('Assigned'),
                TextColumn::make('status')->badge(),
                TextColumn::make('sla_due_at')->since()->label('SLA'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'open' => 'Open',
                    'acknowledged' => 'Acknowledged',
                    'resolved' => 'Resolved',
                ]),
            ])
            ->recordActions([
                Action::make('acknowledge')
                    ->visible(fn (Escalation $record) => $record->status === 'open')
                    ->action(function (Escalation $record) {
                        $record->update(['status' => 'acknowledged']);
                        Notification::make()->title('Escalation acknowledged')->success()->send();
                    }),
                Action::make('convert')
                    ->label('Convert → CRM')
                    ->color('success')
                    ->visible(fn (Escalation $record) => in_array($record->status, ['open', 'acknowledged'], true))
                    ->requiresConfirmation()
                    ->action(function (Escalation $record) {
                        app(FollowUpWorkflowService::class)->convertEscalation($record, auth()->user());
                        Notification::make()->title('Escalation converted to opportunity')->success()->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageEscalations::route('/')];
    }
}
