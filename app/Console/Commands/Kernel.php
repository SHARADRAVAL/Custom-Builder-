<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Console\Commands\GenerateRecurringTasks;


class Kernel extends ConsoleKernel
{
    protected $commands = [
        GenerateRecurringTasks::class,
    ];
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('recurring:generate')
            ->everyMinute()
            ->appendOutputTo(storage_path('logs/schedule.log'))
            ->onFailure(function () {
                Log::error("Scheduler failed to run recurring:generate");
            });
    }



    protected function jobs()
    {
        $this->load(__DIR__ . '/Jobs');
    }
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
