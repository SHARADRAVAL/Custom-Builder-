<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;

class UpdateTaskStatus extends Command
{
    protected $signature = 'tasks:update-status';
    protected $description = 'Automatically update task started_at and completed_at based on time';

    public function handle()
    {
        $now = Carbon::now();

        // ✅ Start tasks
        $startTasks = Task::whereNull('started_at')
            ->where('start_time', '<=', $now)
            ->get();

        foreach ($startTasks as $task) {
            $task->update(['started_at' => $now]);
            if ($task->user) {
                $task->user->notify(new \App\Notifications\TaskStartedNotification($task));
            }
        }

        // ✅ Complete tasks
        $endTasks = Task::whereNotNull('started_at')
            ->whereNull('completed_at')
            ->where('end_time', '<=', $now)
            ->get();

        foreach ($endTasks as $task) {
            $task->update(['completed_at' => $now]);
            if ($task->user) {
                $task->user->notify(new \App\Notifications\TaskCompletedNotification($task));
            }
        }

        $this->info('Task timestamps updated successfully!');
    }
}
