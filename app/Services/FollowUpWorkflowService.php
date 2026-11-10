<?php

namespace App\Services;

use App\Models\Escalation;
use App\Models\FollowUpDraft;
use App\Models\Inquiry;
use App\Models\LeadReply;
use App\Models\MaintenanceRequest;
use App\Models\Opportunity;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FollowUpWorkflowService
{
    public function __construct(
        protected AiFollowUpService $ai,
        protected ModuleManager $modules,
        protected AuditLogger $audit,
    ) {}

    public function intake(array $data, ?User $actor = null): Inquiry
    {
        $inquiry = Inquiry::query()->create([
            'source' => $data['source'] ?? 'website',
            'contact_name' => $data['contact_name'],
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'],
            'service_interest' => $data['service_interest'] ?? null,
            'consent_sms' => (bool) ($data['consent_sms'] ?? false),
            'consent_email' => (bool) ($data['consent_email'] ?? false),
            'consent_captured_at' => (($data['consent_sms'] ?? false) || ($data['consent_email'] ?? false)) ? now() : null,
            'consent_source' => $data['source'] ?? 'website',
            'raw_payload' => $data,
            'status' => 'new',
            'assigned_to' => $data['assigned_to'] ?? null,
        ]);

        $this->audit->log('lead.intake', Inquiry::class, $inquiry->id, null, $inquiry->toArray(), $actor?->id);

        return $this->ai->classify($inquiry);
    }

    public function approveAndStartFollowUp(Inquiry $inquiry, User $actor): FollowUpDraft
    {
        $before = $inquiry->toArray();
        $inquiry->update([
            'status' => 'in_follow_up',
            'approved_at' => now(),
            'approved_by' => $actor->id,
        ]);

        $draft = $inquiry->followUpDrafts()->latest()->first()
            ?? $this->ai->enqueueFirstFollowUp($inquiry->fresh());

        if (! $draft) {
            throw new RuntimeException('Follow-up autopilot module is disabled.');
        }

        if ($draft->status === 'blocked_no_consent') {
            $this->audit->log('followup.blocked_no_consent', FollowUpDraft::class, $draft->id, $before, $draft->toArray(), $actor->id);

            return $draft;
        }

        $draft->update([
            'status' => 'pending_approval',
            'consent_verified' => $inquiry->hasChannelConsent($draft->channel),
        ]);

        $this->audit->log('followup.queue_started', Inquiry::class, $inquiry->id, $before, [
            'draft_id' => $draft->id,
            'status' => 'in_follow_up',
        ], $actor->id);

        return $draft->fresh();
    }

    public function approveSend(FollowUpDraft $draft, User $actor): FollowUpDraft
    {
        $inquiry = $draft->inquiry;

        if ($this->modules->enabled('messaging_consent') && ! $inquiry->hasChannelConsent($draft->channel)) {
            $draft->update(['status' => 'blocked_no_consent', 'consent_verified' => false]);
            $this->audit->log('followup.blocked_no_consent', FollowUpDraft::class, $draft->id, null, $draft->toArray(), $actor->id);

            return $draft->fresh();
        }

        if ($this->modules->enabled('quiet_hours') && ! app()->runningInConsole() && $this->inQuietHours()) {
            $draft->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $actor->id,
                'scheduled_for' => now()->setTime(9, 0)->addDay(),
            ]);
            $this->audit->log('followup.deferred_quiet_hours', FollowUpDraft::class, $draft->id, null, $draft->toArray(), $actor->id);

            return $draft->fresh();
        }

        $before = $draft->toArray();
        $draft->update([
            'status' => 'sent',
            'consent_verified' => true,
            'approved_at' => now(),
            'approved_by' => $actor->id,
            'sent_at' => now(),
        ]);

        $inquiry->update([
            'status' => 'awaiting_reply',
            'last_contacted_at' => now(),
            'next_follow_up_at' => now()->addDays(2),
        ]);

        $this->audit->log('followup.sent', FollowUpDraft::class, $draft->id, $before, $draft->toArray(), $actor->id);

        return $draft->fresh();
    }

    public function ingestReply(Inquiry $inquiry, array $data, ?User $actor = null): LeadReply
    {
        $reply = LeadReply::query()->create([
            'inquiry_id' => $inquiry->id,
            'follow_up_draft_id' => $data['follow_up_draft_id'] ?? $inquiry->followUpDrafts()->where('status', 'sent')->latest()->value('id'),
            'channel' => $data['channel'] ?? 'email',
            'body' => $data['body'],
            'received_at' => now(),
            'status' => 'received',
        ]);

        $reply = $this->ai->analyzeReply($reply);

        if ($reply->is_qualified && $this->modules->enabled('reply_escalation')) {
            $this->escalate($inquiry, $reply, $actor);
        }

        return $reply->fresh();
    }

    public function escalate(Inquiry $inquiry, LeadReply $reply, ?User $actor = null): Escalation
    {
        $assignee = User::query()
            ->whereIn('role', ['owner', 'admin', 'staff'])
            ->where('is_active', true)
            ->get()
            ->sortBy(fn (User $u) => match ($u->role) {
                'owner' => 0,
                'admin' => 1,
                default => 2,
            })
            ->first();

        $escalation = Escalation::query()->create([
            'inquiry_id' => $inquiry->id,
            'lead_reply_id' => $reply->id,
            'assigned_to' => $assignee?->id ?? $actor?->id,
            'reason' => 'Qualified buying-signal reply (score '.$reply->ai_interest_score.')',
            'priority' => ($inquiry->ai_priority === 'urgent') ? 'urgent' : 'high',
            'status' => 'open',
            'sla_due_at' => now()->addHours($inquiry->ai_priority === 'urgent' ? 2 : 8),
        ]);

        $reply->update(['status' => 'escalated']);
        $inquiry->update(['status' => 'escalated', 'assigned_to' => $escalation->assigned_to]);

        $this->audit->log('reply.escalated', Escalation::class, $escalation->id, null, $escalation->toArray(), $actor?->id);

        return $escalation;
    }

    public function convertEscalation(Escalation $escalation, User $actor): Opportunity
    {
        return DB::transaction(function () use ($escalation, $actor) {
            $inquiry = $escalation->inquiry;
            $before = $inquiry->toArray();

            $resident = Resident::query()->firstOrCreate(
                ['email' => $inquiry->contact_email ?: 'lead+'.$inquiry->id.'@example.local'],
                [
                    'name' => $inquiry->contact_name,
                    'phone' => $inquiry->contact_phone,
                    'resident_type' => 'prospect',
                    'notes' => 'Auto-created from escalated follow-up '.$inquiry->reference,
                ]
            );

            $opportunity = Opportunity::query()->create([
                'inquiry_id' => $inquiry->id,
                'resident_id' => $resident->id,
                'owner_id' => $actor->id,
                'title' => ($inquiry->ai_category ?: 'Residential property lead').' — '.$inquiry->contact_name,
                'stage' => 'qualified',
                'value_estimate' => $this->estimateValue($inquiry),
                'probability' => max(40, min(95, (int) ($inquiry->ai_score ?? 60))),
                'status' => 'open',
                'notes' => $inquiry->ai_summary,
            ]);

            if ($this->modules->enabled('maintenance_requests')) {
                MaintenanceRequest::query()->create([
                    'opportunity_id' => $opportunity->id,
                    'resident_id' => $resident->id,
                    'property_id' => $resident->property_id,
                    'resident_name' => $inquiry->contact_name,
                    'service_type' => $inquiry->ai_category,
                    'priority' => $inquiry->ai_priority ?? 'normal',
                    'status' => 'intake',
                    'notes' => $inquiry->body,
                ]);
            }

            $inquiry->update([
                'status' => 'converted',
                'resident_id' => $resident->id,
                'approved_at' => now(),
                'approved_by' => $actor->id,
            ]);

            $escalation->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolution_notes' => 'Converted to opportunity #'.$opportunity->id,
            ]);

            $this->audit->log(
                'escalation.converted',
                Escalation::class,
                $escalation->id,
                $before,
                ['opportunity_id' => $opportunity->id],
                $actor->id
            );

            return $opportunity;
        });
    }

    public function reject(Inquiry $inquiry, User $actor, ?string $reason = null): Inquiry
    {
        $before = $inquiry->toArray();
        $inquiry->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        $this->audit->log('lead.rejected', Inquiry::class, $inquiry->id, $before, $inquiry->toArray(), $actor->id);

        return $inquiry->fresh();
    }

    protected function inQuietHours(): bool
    {
        $hour = (int) now()->format('G');

        return $hour < 8 || $hour >= 20;
    }

    protected function estimateValue(Inquiry $inquiry): float
    {
        return match ($inquiry->ai_category) {
            'leasing_tour' => 1800,
            'lease_application' => 2200,
            'renewal' => 1500,
            'maintenance_emergency' => 450,
            'maintenance_request' => 275,
            'move_in' => 900,
            'move_out' => 650,
            'vendor_dispatch' => 520,
            default => 750,
        };
    }
}
