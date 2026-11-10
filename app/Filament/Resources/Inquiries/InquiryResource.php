<?php

namespace App\Filament\Resources\Inquiries;

use App\Filament\Resources\Inquiries\Pages\ManageInquiries;
use App\Models\Inquiry;
use App\Services\FollowUpWorkflowService;
use App\Services\AiFollowUpService;
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
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationLabel = 'Leads';

    protected static ?string $modelLabel = 'Lead';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Follow-Up Autopilot';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleManager::class)->enabled('inbound_lead_intake')
            || app(ModuleManager::class)->enabled('lead_follow_up_autopilot');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lead Intake')->schema([
                Select::make('source')->options([
                    'website' => 'Website',
                    'phone' => 'Phone',
                    'email' => 'Email',
                    'sms' => 'SMS',
                    'referral' => 'Referral',
                ])->required()->native(false),
                TextInput::make('contact_name')->required()->maxLength(255),
                TextInput::make('contact_email')->email(),
                TextInput::make('contact_phone')->tel(),
                TextInput::make('subject')->maxLength(255),
                TextInput::make('service_interest')->maxLength(255),
                Textarea::make('body')->required()->rows(5)->columnSpanFull(),
                Toggle::make('consent_email')->label('Email consent'),
                Toggle::make('consent_sms')->label('SMS consent'),
                Select::make('status')->options([
                    'new' => 'New',
                    'classified' => 'Classified',
                    'pending_approval' => 'Pending Approval',
                    'in_follow_up' => 'In Follow-Up',
                    'awaiting_reply' => 'Awaiting Reply',
                    'escalated' => 'Escalated',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'converted' => 'Converted',
                    'closed' => 'Closed',
                ])->native(false),
                Select::make('assigned_to')->relationship('assignee', 'name')->searchable()->preload(),
            ])->columns(2),
            Section::make('AI + Consent')->schema([
                TextInput::make('ai_category')->disabled(),
                TextInput::make('ai_priority')->disabled(),
                TextInput::make('ai_score')->numeric()->disabled(),
                TextInput::make('follow_up_step')->numeric()->disabled(),
                Textarea::make('ai_summary')->disabled()->columnSpanFull(),
            ])->columns(4)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable()->sortable(),
                TextColumn::make('source')->badge(),
                TextColumn::make('contact_name')->searchable(),
                TextColumn::make('ai_category')->label('Category')->badge()->placeholder('—'),
                TextColumn::make('ai_priority')->label('Priority')->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('follow_up_step')->label('Step')->sortable(),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending_approval', 'in_follow_up', 'awaiting_reply' => 'warning',
                        'escalated', 'approved', 'converted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('consent_email')->boolean()->label('Email OK'),
                IconColumn::make('consent_sms')->boolean()->label('SMS OK'),
                TextColumn::make('created_at')->dateTime()->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'new' => 'New',
                    'pending_approval' => 'Pending Approval',
                    'in_follow_up' => 'In Follow-Up',
                    'awaiting_reply' => 'Awaiting Reply',
                    'escalated' => 'Escalated',
                    'converted' => 'Converted',
                    'rejected' => 'Rejected',
                ]),
                SelectFilter::make('source')->options([
                    'website' => 'Website',
                    'phone' => 'Phone',
                    'email' => 'Email',
                    'sms' => 'SMS',
                    'referral' => 'Referral',
                ]),
            ])
            ->recordActions([
                Action::make('classify')
                    ->label('AI Classify')
                    ->icon('heroicon-o-sparkles')
                    ->visible(fn (Inquiry $record) => in_array($record->status, ['new', 'classified'], true))
                    ->action(function (Inquiry $record) {
                        app(AiFollowUpService::class)->classify($record);
                        Notification::make()->title('Lead classified + follow-up drafted')->success()->send();
                    }),
                Action::make('start_follow_up')
                    ->label('Approve → Follow-Up')
                    ->color('success')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (Inquiry $record) => in_array($record->status, ['pending_approval', 'classified'], true))
                    ->requiresConfirmation()
                    ->action(function (Inquiry $record) {
                        app(FollowUpWorkflowService::class)->approveAndStartFollowUp($record, auth()->user());
                        Notification::make()->title('Follow-up autopilot started')->success()->send();
                    }),
                Action::make('simulate_reply')
                    ->label('Simulate Reply')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->visible(fn (Inquiry $record) => in_array($record->status, ['awaiting_reply', 'in_follow_up', 'escalated'], true))
                    ->form([
                        Textarea::make('body')->label('Reply body')->required()
                            ->default('Yes, please schedule a tour this week — we are interested and ready to apply.'),
                        Select::make('channel')->options(['email' => 'Email', 'sms' => 'SMS'])->default('email'),
                    ])
                    ->action(function (Inquiry $record, array $data) {
                        app(FollowUpWorkflowService::class)->ingestReply($record, $data, auth()->user());
                        Notification::make()->title('Reply analyzed (escalate if qualified)')->success()->send();
                    }),
                Action::make('reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (Inquiry $record) => in_array($record->status, ['pending_approval', 'classified', 'in_follow_up'], true))
                    ->form([
                        Textarea::make('reason')->label('Rejection reason')->required(),
                    ])
                    ->action(function (Inquiry $record, array $data) {
                        app(FollowUpWorkflowService::class)->reject($record, auth()->user(), $data['reason']);
                        Notification::make()->title('Lead rejected')->warning()->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInquiries::route('/'),
        ];
    }
}
