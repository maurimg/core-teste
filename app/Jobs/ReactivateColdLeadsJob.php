<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\LeadEventService;
use App\Services\LeadReactivationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class ReactivateColdLeadsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $limitDate = Carbon::now()->subDays(7);

        $leads = Lead::where('temperature', 'cold')
            ->whereNotNull('last_interaction_at')
            ->where('last_interaction_at', '<=', $limitDate)
            ->get();

        foreach ($leads as $lead) {

            // Limite máximo de tentativas
            if ($lead->reactivation_attempts >= 3) {
                continue;
            }

            // Intervalo mínimo entre tentativas
            if ($lead->last_reactivation_at &&
                Carbon::parse($lead->last_reactivation_at)
                    ->diffInDays(Carbon::now()) < 7) {
                continue;
            }

            $message = LeadReactivationService::generateMessage($lead);

            LeadEventService::register(
                $lead->id,
                'reactivation_attempt',
                5,
                [
                    'generated_message' => $message
                ]
            );

            // Atualiza contador
            $lead->reactivation_attempts += 1;
            $lead->last_reactivation_at = Carbon::now();
            $lead->save();
        }
    }
}
