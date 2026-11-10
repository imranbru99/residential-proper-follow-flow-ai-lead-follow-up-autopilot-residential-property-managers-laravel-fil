<?php

namespace App\Services;

use App\Models\Inquiry;

/**
 * Back-compat alias — ResidentialProperFollowFlow uses AiFollowUpService as the primary AI engine.
 */
class AiTriageService
{
    public function __construct(protected AiFollowUpService $ai) {}

    public function classify(Inquiry $inquiry): Inquiry
    {
        return $this->ai->classify($inquiry);
    }
}
