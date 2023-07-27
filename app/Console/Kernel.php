<?php

namespace App\Console;

use App\Jobs\CleanupTmpFolder;
use App\Jobs\CleanupPaysheetsTmpFolder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    protected $commands = [];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        $schedule->job(new CleanupTmpFolder)->dailyAt('03:30');
        $schedule->job(new CleanupPaysheetsTmpFolder)->dailyAt('03:30');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
