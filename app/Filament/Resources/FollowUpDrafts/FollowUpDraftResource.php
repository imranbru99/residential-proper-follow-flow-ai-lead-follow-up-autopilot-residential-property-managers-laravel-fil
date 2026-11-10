<?php

namespace App\Filament\Resources\FollowUpDrafts;

use App\Filament\Resources\FollowUpDrafts\Pages\ManageFollowUpDrafts;
use App\Models\FollowUpDraft;
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
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class FollowUpDraftResource extends Resource
{
    protected static ?string $model = FollowUpDraft::class;

    protected static ?string $navigationLabel = 'Follow-Up Drafts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Follow-Up Autopilot';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleManager::class)->enabled('lead_follow_up_autopilot')
            || app(ModuleManager::class)->enabled('ai_follow_up_writer');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('inquiry_id')->relationship('inquiry', 'reference')->required()->searchable()->preload(),
            Select::make('sequence_id')->relationship('sequence', 'name')->searchable()->preload(),
            TextInput::make('step_number')->numeric()->default(1),
            Select::make('channel')->options([
                'email' => 'Email',
                'sms' => 'SMS',
            ])->required(),
            TextInput::make('subject'),
            Textarea::make('body')->required()->rows(6)->columnSpanFull(),
            Toggle::make('consent_verified')->label('Consent verified'),
            Select::make('status')->options([
                'draft' => 'Draft',
                'pending_approval' => 'Pending Approval',
                'approved' => 'Approved',
                'sent' => 'Sent',
                'blocked_no_consent' => 'Blocked — No Consent',
                'rejected' => 'Rejected',
            ])->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inquiry.reference')->label('Lead')->searchable(),
                TextColumn::make('step_number')->label('Step'),
                TextColumn::make('channel')->badge(),
                TextColumn::make('subject')->limit(36),
                IconColumn::make('consent_verified')->boolean()->label('Consent'),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'sent', 'approved' => 'success',
                        'pending_approval' => 'warning',
                        'blocked_no_consent', 'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('scheduled_for')->since()->placeholder('—'),
                TextColumn::make('created_at')->since(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending_approval' => 'Pending Approval',
                    'sent' => 'Sent',
                    'blocked_no_consent' => 'Blocked',
                    'approved' => 'Approved',
                ]),
            ])
            ->recordActions([
                Action::make('approve_send')
                    ->label('Approve & Send')
                    ->visible(fn (FollowUpDraft $record) => in_array($record->status, ['pending_approval', 'approved'], true))
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (FollowUpDraft $record) {
                        $result = app(FollowUpWorkflowService::class)->approveSend($record, auth()->user());
                        Notification::make()
                            ->title($result->status === 'sent' ? 'Follow-up sent' : 'Follow-up '.$result->status)
                            ->success()
                            ->send();
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
        return ['index' => ManageFollowUpDrafts::route('/')];
    }
}
