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
        foreach (['18:40', '18:55'] as $time) {
            $schedule->command('calculate:start')
                ->dailyAt($time)->appendOutputTo(storage_path('logs/calculate.log'));
        }

        $schedule->command('daily:start')
            ->dailyAt('18:45')->appendOutputTo(storage_path('logs/daily.log'));
        $schedule->command('checkLo:start')
            ->dailyAt('18:11')->appendOutputTo(storage_path('logs/checkLo.log'));
        $schedule->command('checkDe:start')
            ->dailyAt('18:20')->appendOutputTo(storage_path('logs/checkDe.log'));

        $schedule->command('save:result')
            ->dailyAt('18:50')->appendOutputTo(storage_path('logs/result.log'));

        $schedule->command('result:get')
            ->dailyAt('18:55')->appendOutputTo(storage_path('logs/result_get.log'));

        foreach (['18:41', '19:00'] as $time) {
            $schedule->command('checkResult:start')
                ->dailyAt($time)->appendOutputTo(storage_path('logs/checkResult.log'));
        }

//        $schedule->command('attack:start')
//            ->everyFiveMinutes()->appendOutputTo(storage_path('logs/attack.log'));
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
