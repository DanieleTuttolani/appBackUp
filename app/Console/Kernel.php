<?php

namespace App\Console;

use App\Http\Controllers\DomainController;
use App\Models\Domain;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function (DomainController $domainController) {

            $domains = Domain::all();

            if (count($domains) > 0) {

                foreach ($domains as $domain) {

                    $domainController->scheduleEvent($domain);
                }
            }
        })->dailyAt('23:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
