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

    // public function handle(): int
    // {
    //     // 1. Eager load the relationship
    //     $recurringTasks = RecurringTask::with('task')->get();

    //     // 2. DEBUG: This will show up in storage/logs/laravel.log
    //     Log::info("Recurring Generator Heartbeat: " . now()->toDateTimeString());

    //     if ($recurringTasks->isEmpty()) {
    //         $this->info('No recurring rules found in the database.');
    //         return Command::SUCCESS;
    //     }

    //     $generatedCount = 0;

    //     foreach ($recurringTasks as $recurring) {
    //         // 3. Extra Debugging: Log which task we are checking
    //         $this->info("Checking task: " . ($recurring->task->title ?? 'Unknown'));

    //         if ($this->process($recurring)) {
    //             $generatedCount++;
    //         }
    //     }

    //     if ($generatedCount === 0) {
    //         $this->info('No scheduled tasks due at this time. (Current Server Time: ' . now()->format('H:i') . ')');
    //     } else {
    //         $this->info("Success! {$generatedCount} task(s) generated.");
    //     }

    //     return Command::SUCCESS;
    // }

    public function handle(): int
    {
        $logPath = storage_path('logs/schedule.log');
        $status = "--- Automation Triggered at " . now()->toDateTimeString() . " ---\n";

        // Force write to the file immediately
        file_put_contents($logPath, $status, FILE_APPEND);
        $this->info($status);

        $recurringTasks = RecurringTask::with('task')->get();

        foreach ($recurringTasks as $recurring) {
            $checkMsg = "Checking Rule ID: {$recurring->id} | Type: {$recurring->repeat_type}\n";
            file_put_contents($logPath, $checkMsg, FILE_APPEND);

            if ($this->process($recurring)) {
                $successMsg = "CREATED: Task for Rule {$recurring->id}\n";
                file_put_contents($logPath, $successMsg, FILE_APPEND);
            } else {
                $skipMsg = "SKIPPED: Rule {$recurring->id} (Condition not met)\n";
                file_put_contents($logPath, $skipMsg, FILE_APPEND);
            }
        }

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
    private function weekly(RecurringTask $recurring): bool
    {
        $todayName = strtolower(now()->format('l'));
        $allowedDays = array_map('strtolower', $recurring->week_days ?? []);

        if (!in_array($todayName, $allowedDays)) {
            return false;
        }

        $time = Carbon::parse($recurring->weekly_time ?? '00:00');
        $dateTime = now()->setTime($time->hour, $time->minute, 0);

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
