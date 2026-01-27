<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Console\Commands\GenerateRecurringTasks;
use App\Console\Commands\UpdateDueDays;



class Kernel extends ConsoleKernel
{
    protected $commands = [
        GenerateRecurringTasks::class,
        UpdateDueDays::class,

    ];
    protected function schedule(Schedule $schedule)
    {
        //Generate recurring tasks
        $schedule->command('recurring:generate')
            ->everyMinute()
            ->withoutOverlapping() 
            ->appendOutputTo(storage_path('logs/schedule.log'));
        
        //Update due days 
        $schedule->command('tasks:update-due')
            ->daily()
            ->appendOutputTo(storage_path('logs/schedule.log'));
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
