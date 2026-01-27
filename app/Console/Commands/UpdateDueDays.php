<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;

class UpdateDueDays extends Command
{
    /**
     * The name and signature of the console command.
     * Use this to run it manually: php artisan tasks:update-due
     */
    protected $signature = 'tasks:update-due';

    /**
     * The console command description.
     */
    protected $description = 'Automatically updates the overdue days count for active tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Current Time: Jan 26, 2026
        $today = now();

        // Target only tasks that are NOT completed and NOT master templates
        $tasks = Task::whereNotIn('status', ['completed', 'template'])
                     ->whereNotNull('end_time')
                     ->get();

        $count = 0;

        foreach ($tasks as $task) {
            // Logic: Calculate the absolute whole number difference
            // This prevents the "-15.998..." decimal issue you saw earlier
            $newDiff = (int) abs($today->diffInDays($task->end_time));

            // Only update if the number has actually changed to save database resources
            if ($task->due_days !== $newDiff) {
                $task->update(['due_days' => $newDiff]);
                $count++;
            }
        }

        $this->info("Successfully updated {$count} tasks to clean integers.");
    }
}