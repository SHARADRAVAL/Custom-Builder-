<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecurringTask;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateRecurringTasks extends Command
{
    protected $signature = 'recurring:generate';
    protected $description = 'Generate tasks from recurring rules';



    public function handle(): int
    {
        $logPath = storage_path('logs/schedule.log');
        $this->info("--- Automation Triggered at " . now()->toDateTimeString() . " ---");

        // 1. Use chunk() to prevent memory exhaustion if you have many tasks
        // 2. We use with('task') to avoid N+1 query issues
        RecurringTask::with('task')->chunk(100, function ($recurringTasks) use ($logPath) {
            $logBuffer = "";

            foreach ($recurringTasks as $recurring) {
                // Log local progress to buffer instead of writing to disk every loop
                $logBuffer .= "Checking Rule ID: {$recurring->id} | Type: {$recurring->repeat_type}\n";

                if ($this->process($recurring)) {
                    $logBuffer .= "CREATED: Task for Rule {$recurring->id}\n";
                } else {
                    $logBuffer .= "SKIPPED: Rule {$recurring->id} (Condition not met)\n";
                }
            }

            // Write to disk once per 100 records instead of 100 times
           Log::channel('daily')->info($logBuffer);

        });

        return Command::SUCCESS;
    }

    /**
     * Redirects to the specific logic based on repeat_type
     */
    private function process(RecurringTask $rule): bool
    {
        // 3. IMPORTANT: Use your specific helper methods instead of duplicate code
        return match ($rule->repeat_type) {
            'daily'   => $this->daily($rule),
            'weekly'  => $this->weekly($rule),
            'monthly' => $this->monthly($rule),
            default   => false,
        };
    }

    /* ---------------- DAILY ---------------- */
    private function daily(RecurringTask $recurring): bool
    {
        if ($recurring->daily_time) {
            $time = Carbon::parse($recurring->daily_time);
        } else {
            // Fallback to the template task's start time
            $time = Carbon::parse($recurring->task->start_time);
        }

        $dateTime = now()->setTime($time->hour, $time->minute, 0);

        if (now()->lt($dateTime)) {
            return false;
        }

        return $this->createIfNotExists($recurring, $dateTime);
    }

    /* ---------------- WEEKLY ---------------- */
    /* ---------------- WEEKLY ---------------- */
    private function weekly(RecurringTask $recurring): bool
    {
        // 1. Ensure the task is within its active date range (start_date to end_date)
        if (!$recurring->isActive()) {
            return false;
        }

        $todayName = strtolower(now()->format('l'));

        // 2. Handle the "Double-Encoding" gracefully if it's already in the DB
        $days = $recurring->week_days;
        if (is_string($days)) {
            $days = json_decode($days, true) ?: [];
        }

        $allowedDays = array_map('strtolower', (array)$days);

        if (!in_array($todayName, $allowedDays)) {
            return false;
        }

        // 3. Determine the specific time to run
        // Priority: recurring weekly_time -> task start_time -> midnight
        $timeString = $recurring->weekly_time ?: ($recurring->task->start_time ?? '00:00');
        $time = Carbon::parse($timeString);

        // Create the execution point for TODAY
        $dateTime = now()->setTime($time->hour, $time->minute, 0);

        // 4. Don't create if the scheduled time hasn't passed yet today
        if (now()->lt($dateTime)) {
            return false;
        }

        return $this->createIfNotExists($recurring, $dateTime);
    }

    /* ---------------- MONTHLY ---------------- */
    private function monthly(RecurringTask $recurring): bool
    {
        if (!$recurring->monthly_date) {
            return false;
        }

        $targetDay = Carbon::parse($recurring->monthly_date)->day;

        if (now()->day !== $targetDay) {
            return false;
        }

        $time = Carbon::parse($recurring->monthly_time ?? '00:00');
        $dateTime = now()->setTime($time->hour, $time->minute, 0);

        if (now()->lt($dateTime)) {
            return false;
        }

        return $this->createIfNotExists($recurring, $dateTime);
    }

    /* ---------------- COMMON CREATION LOGIC ---------------- */
    private function createIfNotExists(RecurringTask $recurring, Carbon $dateTime): bool
    {
        // 4. Load the "Master" template task from the relationship
        $base = $recurring->task;

        // 5. Safety Check: This prevents the "SQLSTATE[23000]: Column 'title' cannot be null" error
        if (!$base || empty($base->title)) {
            return false;
        }

        // 6. Use firstOrCreate to prevent creating multiple tasks for the same rule at the same time
        $newTask = Task::firstOrCreate(
            [
                'title'      => $base->title,      // Data source is the template
                'user_id'    => $base->user_id,    // Data source is the template
                'start_time' => $dateTime->toDateTimeString(),
                'status'     => 'pending',         // Generated tasks are pending/visible
            ],
            [
                'description' => $base->description,
                'repeat_type' => $recurring->repeat_type,
            ]
        );

        return $newTask->wasRecentlyCreated;
    }
}
