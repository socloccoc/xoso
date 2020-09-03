<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        $schedule->command('calculate:start')
            ->dailyAt('18:40')->appendOutputTo(storage_path('logs/calculate.log'));
        $schedule->command('daily:start')
            ->dailyAt('18:45')->appendOutputTo(storage_path('logs/daily.log'));
        $schedule->command('checkLo:start')
            ->dailyAt('18:08')->appendOutputTo(storage_path('logs/checkLo.log'));
        $schedule->command('checkDe:start')
            ->dailyAt('18:16')->appendOutputTo(storage_path('logs/checkDe.log'));
        $schedule->command('checkResult:start')
            ->dailyAt('18:41')->appendOutputTo(storage_path('logs/checkResult.log'));
        $schedule->command('attack:start')
            ->everyFiveMinutes()->appendOutputTo(storage_path('logs/attack.log'));
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
