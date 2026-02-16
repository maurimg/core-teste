<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\LeadEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class CoolInactiveLeadsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = Carbon::now();

        $leads = Lead::whereNotNull('last_interaction_at')->get();

        foreach ($leads as $lead) {

            $days = Carbon::parse($lead->last_interaction_at)
                ->diffInDays($now);

            if ($days >= 30) {
                LeadEventService::register($lead->id, 'inactive_30_days', -40);
            } elseif ($days >= 14) {
                LeadEventService::register($lead->id, 'inactive_14_days', -20);
            } elseif ($days >= 7) {
                LeadEventService::register($lead->id, 'inactive_7_days', -10);
            }
        }
    }
}
