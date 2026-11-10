<?php

namespace App\Services;

use App\Models\FollowUpDraft;
use App\Models\Inquiry;
use App\Models\Opportunity;
use App\Models\User;

/**
 * Back-compat facade used by Filament actions; delegates to FollowUpWorkflowService.
 */
class InquiryWorkflowService
{
    public function __construct(protected FollowUpWorkflowService $workflow) {}

    public function intake(array $data, ?User $actor = null): Inquiry
    {
        return $this->workflow->intake($data, $actor);
    }

    public function approve(Inquiry $inquiry, User $actor): Opportunity|FollowUpDraft
    {
        $draft = $this->workflow->approveAndStartFollowUp($inquiry, $actor);

        if ($inquiry->fresh()->status === 'escalated' || $inquiry->fresh()->opportunity) {
            return $inquiry->fresh()->opportunity ?? $draft;
        }

        return $draft;
    }

    public function reject(Inquiry $inquiry, User $actor, ?string $reason = null): Inquiry
    {
        return $this->workflow->reject($inquiry, $actor, $reason);
    }
}
