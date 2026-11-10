<?php

namespace App\Services;

use App\Models\AiClassification;
use App\Models\FollowUpDraft;
use App\Models\FollowUpSequence;
use App\Models\Inquiry;
use App\Models\LeadReply;

/**
 * Local equivalent of imrandevbd/laravel-ai-hub for residential property lead follow-up drafting + reply scoring.
 */
class AiFollowUpService
{
    public function __construct(
        protected ModuleManager $modules,
        protected AuditLogger $audit,
    ) {}

    public function classify(Inquiry $inquiry): Inquiry
    {
        if (! $this->modules->enabled('ai_classification')) {
            $inquiry->update(['status' => 'pending_approval']);

            return $inquiry->fresh();
        }

        $text = strtolower(($inquiry->subject ?? '').' '.$inquiry->body.' '.$inquiry->service_interest);
        $category = $this->detectCategory($text);
        $priority = $this->detectPriority($text, $category);
        $score = $this->score($text, $category, $priority);
        $tags = $this->tags($text, $category);
        $summary = sprintf(
            'AI follow-up prep: %s lead scored %d/100 as %s priority for %s. Consent email=%s sms=%s.',
            $inquiry->source,
            $score,
            $priority,
            $category,
            $inquiry->consent_email ? 'yes' : 'no',
            $inquiry->consent_sms ? 'yes' : 'no'
        );

        $response = compact('category', 'priority', 'score', 'tags', 'summary') + [
            'qualified' => $score >= 55,
        ];

        AiClassification::query()->create([
            'inquiry_id' => $inquiry->id,
            'provider' => $this->modules->enabled('ai_hub') ? 'ai-hub-local' : 'local-rules',
            'model' => 'residential-followup-v1',
            'prompt' => substr($inquiry->body, 0, 2000),
            'response' => $response,
            'confidence' => min(95, 50 + (int) floor($score / 3)),
        ]);

        $inquiry->update([
            'ai_category' => $category,
            'ai_priority' => $priority,
            'ai_score' => $score,
            'ai_summary' => $summary,
            'ai_tags' => $tags,
            'status' => $this->modules->enabled('human_approval_workflow') ? 'pending_approval' : 'classified',
            'classified_at' => now(),
            'consent_captured_at' => ($inquiry->consent_email || $inquiry->consent_sms) ? ($inquiry->consent_captured_at ?? now()) : null,
            'consent_source' => $inquiry->consent_source ?? $inquiry->source,
        ]);

        $this->audit->log('lead.classified', Inquiry::class, $inquiry->id, null, $response);

        if ($this->modules->enabled('ai_follow_up_writer') || $this->modules->enabled('lead_follow_up_autopilot')) {
            $this->enqueueFirstFollowUp($inquiry->fresh());
        }

        return $inquiry->fresh();
    }

    public function enqueueFirstFollowUp(Inquiry $inquiry): ?FollowUpDraft
    {
        if (! $this->modules->enabled('lead_follow_up_autopilot')) {
            return null;
        }

        $sequence = FollowUpSequence::query()->where('is_active', true)->orderBy('id')->first();
        $step = $sequence?->steps[0] ?? [
            'day' => 0,
            'channel' => 'email',
            'template' => 'Thanks for reaching out — we received your residential property inquiry and a leasing coordinator will confirm next steps.',
        ];

        $channel = $this->resolveChannel($inquiry, $step['channel'] ?? ($sequence->channel_preference ?? 'email'));
        $consentOk = $inquiry->hasChannelConsent($channel);

        $body = $this->composeBody($inquiry, $step, 1);

        $draft = FollowUpDraft::query()->create([
            'inquiry_id' => $inquiry->id,
            'sequence_id' => $sequence?->id,
            'step_number' => 1,
            'channel' => $channel,
            'subject' => 'Re: '.($inquiry->subject ?: 'Your residential property inquiry'),
            'body' => $body,
            'consent_verified' => $consentOk,
            'status' => $consentOk
                ? ($this->modules->enabled('human_approval_workflow') ? 'pending_approval' : 'approved')
                : 'blocked_no_consent',
            'scheduled_for' => now()->addMinutes((int) (($step['day'] ?? 0) * 24 * 60)),
        ]);

        $inquiry->update([
            'follow_up_step' => 1,
            'next_follow_up_at' => $draft->scheduled_for,
            'status' => $consentOk ? 'in_follow_up' : $inquiry->status,
        ]);

        $this->audit->log('followup.drafted', FollowUpDraft::class, $draft->id, null, $draft->toArray());

        return $draft;
    }

    public function analyzeReply(LeadReply $reply): LeadReply
    {
        $text = strtolower($reply->body);
        $signals = [];
        $score = 35;

        $buying = ['yes', 'schedule', 'tour', 'interested', 'available', 'tomorrow', 'this week', 'please call', 'ready', 'apply', 'lease', 'move-in', 'move in', 'showing'];
        $urgent = ['asap', 'urgent', 'emergency', 'flood', 'leak', 'no heat', 'no ac', 'today'];
        $negative = ['stop', 'unsubscribe', 'not interested', 'wrong number', 'remove'];

        foreach ($buying as $needle) {
            if (str_contains($text, $needle)) {
                $signals[] = 'buying:'.$needle;
                $score += 12;
            }
        }
        foreach ($urgent as $needle) {
            if (str_contains($text, $needle)) {
                $signals[] = 'urgent:'.$needle;
                $score += 10;
            }
        }
        foreach ($negative as $needle) {
            if (str_contains($text, $needle)) {
                $signals[] = 'optout:'.$needle;
                $score -= 40;
            }
        }

        $score = max(0, min(99, $score));
        $qualified = $score >= 60 && ! collect($signals)->contains(fn ($s) => str_starts_with($s, 'optout:'));

        $reply->update([
            'ai_interest_score' => $score,
            'ai_signals' => array_values(array_unique($signals)),
            'is_qualified' => $qualified,
            'status' => 'analyzed',
        ]);

        $this->audit->log('reply.analyzed', LeadReply::class, $reply->id, null, [
            'score' => $score,
            'qualified' => $qualified,
            'signals' => $signals,
        ]);

        return $reply->fresh();
    }

    protected function resolveChannel(Inquiry $inquiry, string $preferred): string
    {
        if ($preferred === 'sms' && $inquiry->consent_sms) {
            return 'sms';
        }
        if ($inquiry->consent_email) {
            return 'email';
        }
        if ($inquiry->consent_sms) {
            return 'sms';
        }

        return $preferred;
    }

    protected function composeBody(Inquiry $inquiry, array $step, int $stepNumber): string
    {
        $template = $step['template'] ?? 'Following up on your residential property inquiry.';

        return "Hi {$inquiry->contact_name},\n\n".
            "{$template}\n\n".
            "We classified your {$inquiry->source} inquiry (ref {$inquiry->reference}) as ".
            ($inquiry->ai_priority ?: 'normal').' priority for '.($inquiry->ai_category ?: 'general residential').".\n\n".
            "Reply with a preferred tour or maintenance window, or call our leasing / ops desk.\n\n".
            "— ResidentialProperFollowFlow Autopilot (step {$stepNumber})";
    }

    protected function detectCategory(string $text): string
    {
        $map = [
            'leasing_tour' => ['tour', 'showing', 'schedule a visit', 'see the unit', 'available unit', 'apartment available'],
            'lease_application' => ['application', 'apply', 'rental application', 'lease application', 'screening'],
            'renewal' => ['renew', 'renewal', 'lease renewal', 'extend lease'],
            'maintenance_emergency' => ['emergency', 'flood', 'leak', 'no heat', 'no ac', 'broken lock', 'gas smell', 'urgent repair'],
            'maintenance_request' => ['maintenance', 'work order', 'repair', 'fix', 'plumber', 'hvac', 'pest'],
            'move_in' => ['move-in', 'move in', 'keys', 'utility setup', 'welcome'],
            'move_out' => ['move-out', 'move out', 'vacate', 'notice to vacate', 'ending lease'],
            'vendor_dispatch' => ['vendor', 'contractor', 'work order', 'dispatch'],
            'resident_question' => ['parking', 'amenity', 'pet policy', 'quiet hours', 'package'],
        ];

        foreach ($map as $category => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($text, $needle)) {
                    return $category;
                }
            }
        }

        return 'general_residential';
    }

    protected function detectPriority(string $text, string $category): string
    {
        if ($category === 'maintenance_emergency' || str_contains($text, 'asap') || str_contains($text, 'urgent')) {
            return 'urgent';
        }

        if (in_array($category, ['leasing_tour', 'lease_application', 'move_in'], true) || str_contains($text, 'this week')) {
            return 'high';
        }

        return 'normal';
    }

    protected function score(string $text, string $category, string $priority): int
    {
        $score = 45;

        if (strlen($text) > 80) {
            $score += 10;
        }

        if (preg_match('/\d{3}[-.\s]?\d{3}[-.\s]?\d{4}/', $text)) {
            $score += 8;
        }

        $score += match ($priority) {
            'urgent' => 25,
            'high' => 15,
            default => 5,
        };

        $score += match ($category) {
            'maintenance_emergency', 'leasing_tour', 'lease_application' => 12,
            'renewal', 'move_in', 'maintenance_request' => 8,
            default => 3,
        };

        return max(10, min(99, $score));
    }

    protected function tags(string $text, string $category): array
    {
        $tags = [$category, 'follow_up', 'residential_property'];

        if (str_contains($text, 'fair housing') || str_contains($text, 'ada')) {
            $tags[] = 'compliance_sensitive';
        }
        if (str_contains($text, 'prospect') || str_contains($text, 'tour')) {
            $tags[] = 'prospect';
        }
        if (str_contains($text, 'resident') || str_contains($text, 'tenant')) {
            $tags[] = 'resident';
        }

        return array_values(array_unique($tags));
    }
}
