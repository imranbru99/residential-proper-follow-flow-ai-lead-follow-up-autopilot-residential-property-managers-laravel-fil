<?php

namespace App\Filament\Widgets;

use App\Models\Escalation;
use App\Models\FollowUpDraft;
use App\Models\Inquiry;
use App\Models\VendorWorkOrder;
use App\Services\ModuleManager;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TriageStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $modules = app(ModuleManager::class);

        return [
            Stat::make('Leads in Follow-Up', Inquiry::query()->whereIn('status', ['in_follow_up', 'awaiting_reply', 'pending_approval'])->count())
                ->description('Active consent-aware autopilot queue')
                ->color('warning'),
            Stat::make('Drafts Awaiting Send', FollowUpDraft::query()->where('status', 'pending_approval')->count())
                ->description('Human approval gate')
                ->color('primary'),
            Stat::make('Open Escalations', $modules->enabled('reply_escalation')
                ? Escalation::query()->whereIn('status', ['open', 'acknowledged'])->count()
                : 0)
                ->description('Qualified replies for staff')
                ->color('danger'),
            Stat::make('Open Work Orders', $modules->enabled('vendor_work_orders')
                ? VendorWorkOrder::query()->whereIn('status', ['draft', 'dispatched', 'on_site'])->count()
                : 0)
                ->description('Vendor dispatch'),
        ];
    }
}
