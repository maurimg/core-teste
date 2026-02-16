<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\CoolInactiveLeadsJob;
use App\Jobs\ReactivateColdLeadsJob;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Esfria leads inativos diariamente
        $schedule->job(new CoolInactiveLeadsJob())->daily();

        // Tenta reativar leads frios diariamente
        $schedule->job(new ReactivateColdLeadsJob())->dailyAt('10:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
