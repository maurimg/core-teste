<?php

namespace App\Services;

use App\Models\LeadEvent;
use App\Models\Lead;

class LeadEventService
{
    /**
     * Registra um evento e recalcula score do lead
     */
    public static function register(
        int $leadId,
        string $eventType,
        int $scoreDelta = 0,
        array $metadata = []
    ): void {

        LeadEvent::create([
            'lead_id'     => $leadId,
            'event_type'  => $eventType,
            'score_delta' => $scoreDelta,
            'metadata'    => empty($metadata) ? null : $metadata,
        ]);

        self::updateLastInteraction($leadId);

        self::recalculateScore($leadId);
    }

    /**
     * Atualiza última interação do lead
     */
    public static function updateLastInteraction(int $leadId): void
    {
        Lead::where('id', $leadId)->update([
            'last_interaction_at' => now()
        ]);
    }

    /**
     * Recalcula score total do lead
     */
    public static function recalculateScore(int $leadId): void
    {
        $lead = Lead::find($leadId);

        if (!$lead) {
            return;
        }

        $score = LeadEvent::where('lead_id', $leadId)
            ->sum('score_delta');

        $temperature = 'cold';

        if ($score >= 70) {
            $temperature = 'hot';
        } elseif ($score >= 40) {
            $temperature = 'warm';
        }

        $lead->score_current = $score;
        $lead->temperature = $temperature;
        $lead->save();
    }
}
