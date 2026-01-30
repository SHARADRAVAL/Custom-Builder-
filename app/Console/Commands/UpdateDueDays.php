<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Task;

class UpdateDueDays extends Command
{
    protected $signature = 'tasks:update-due';
    protected $description = 'Automatically updates the overdue days count for active tasks using a single bulk query';

    public function handle()
    {
        // Perform a single bulk update using DB::raw to calculate absolute days difference
        $updated = Task::whereNotIn('status', ['completed', 'template'])
            ->whereNotNull('end_time')
            ->update([
                'due_days' => DB::raw('ABS(DATEDIFF(NOW(), end_time))')
            ]);

        $this->info("Successfully updated due_days for {$updated} tasks.");
    }
}
