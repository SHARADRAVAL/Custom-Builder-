<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecurringTask;
use App\Jobs\ProcessRecurringTask;
use Carbon\Carbon;

class GenerateRecurringTasks extends Command
{
    protected $signature = 'recurring:generate';
    protected $description = 'Generate tasks from recurring templates using next_run_at queue';

    public function handle(): int
    {
        $now = now();

        // Fetch due recurring tasks using next_run_at
        RecurringTask::with('task.users')
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', $now)
            ->chunk(50, function ($rules) use ($now) {

                foreach ($rules as $recurring) {

                    $template = $recurring->task;
                    if (!$template || !$recurring->isActive()) continue;

                    $executionTime = $this->getExecutionTime($recurring, $now);
                    if (!$executionTime) continue;

                    // Dispatch job
                    ProcessRecurringTask::dispatch($recurring, $executionTime->format('Y-m-d H:i:s'));
                    $this->info("Dispatched generation for: {$template->title}");
                }
            });

        return self::SUCCESS;
    }

    protected function getExecutionTime(RecurringTask $recurring, Carbon $now): ?Carbon
    {
        $timeStr = match ($recurring->repeat_type) {
            'daily'   => $recurring->daily_time,
            'weekly'  => (is_array($recurring->week_days) && in_array($now->format('l'), $recurring->week_days)) ? $recurring->weekly_time : null,
            'monthly' => ($recurring->monthly_date && $now->day == Carbon::parse($recurring->monthly_date)->day) ? $recurring->monthly_time : null,
            default   => null,
        };

        if (!$timeStr) return null;

        $target = Carbon::parse($now->toDateString() . ' ' . $timeStr);

        // Return time only if we have reached the scheduled moment
        return $now->gte($target) ? $target : null;
    }
}
