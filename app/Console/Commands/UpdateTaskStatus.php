<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;
use App\Notifications\TaskStatusNotification;

class UpdateTaskStatus extends Command
{
    protected $signature = 'tasks:update-status';
    protected $description = 'Automatically update task started_at and completed_at based on time';

    public function handle()
    {
        $now = Carbon::now();

        // Start tasks
        Task::whereNull('started_at')
            ->where('start_time', '<=', $now)
            ->chunkById(500, function ($tasks) use ($now) {
                foreach ($tasks as $task) {
                    $task->update(['started_at' => $now]);
                    if ($task->user) {
                        $task->user->notify(new TaskStatusNotification($task, 'started'));
                    }
                }
            });

        // Complete tasks
        Task::whereNotNull('started_at')
            ->whereNull('completed_at')
            ->where('end_time', '<=', $now)
            ->chunkById(500, function ($tasks) use ($now) {
                foreach ($tasks as $task) {
                    $task->update(['completed_at' => $now]);
                    if ($task->user) {
                        $task->user->notify(new TaskStatusNotification($task, 'completed'));
                    }
                }
            });

        $this->info('Task timestamps updated successfully!');
    }
}
