<?php

namespace App\Console;

use App\Models\ScheduleSetting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
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
    protected function schedule(Schedule $schedule)
    {

        $scheduleSetting = ScheduleSetting::where('id', 1)->first();

        foreach (['18:35', '18:40', '18:45', '18:50', '18:55'] as $time) {
            $schedule->command('calculate:start')
                ->dailyAt($time)->appendOutputTo(storage_path('logs/calculate.log'));
        }

        $schedule->command('daily:start')
            ->dailyAt('18:45')->appendOutputTo(storage_path('logs/daily.log'));

        $schedule->command('checkLo:start')
            ->dailyAt(trim($scheduleSetting['lov1']))->appendOutputTo(storage_path('logs/checkLo.log'));

        $schedule->command('checkLov2:start')
            ->dailyAt(trim($scheduleSetting['lov2']))->appendOutputTo(storage_path('logs/checkLov2.log'));

        $schedule->command('checkDe:start')
            ->dailyAt(trim($scheduleSetting['dev1']))->appendOutputTo(storage_path('logs/checkDe.log'));

        $schedule->command('checkDeV2:start')
            ->dailyAt(trim($scheduleSetting['dev2']))->appendOutputTo(storage_path('logs/checkDev2.log'));

        $schedule->command('save:result')
            ->dailyAt('18:50')->appendOutputTo(storage_path('logs/result.log'));

        $schedule->command('result:get')
            ->dailyAt('18:55')->appendOutputTo(storage_path('logs/result_get.log'));

        $schedule->command('numbers:get')
            ->dailyAt('10:00')->appendOutputTo(storage_path('logs/numbers_get.log'));

        $schedule->command('de:analytic')
            ->dailyAt('10:00')->appendOutputTo(storage_path('logs/analytic_de.log'));

        $schedule->command('calculate_test:start')
            ->everyMinute()->appendOutputTo(storage_path('logs/calculate_test.log'));

        foreach (['18:36', '18:41', '18:46', '18:51', '19:00'] as $time) {
            $schedule->command('checkResult:start')
                ->dailyAt($time)->appendOutputTo(storage_path('logs/checkResult.log'));
        }

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
