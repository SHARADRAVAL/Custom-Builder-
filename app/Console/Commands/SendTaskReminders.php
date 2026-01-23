<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;
use App\Jobs\SendTaskStartEmail;
use Illuminate\Support\Facades\Log;

class SendTaskReminders extends Command
{
    protected $signature = 'tasks:send-reminders';
    protected $description = 'Send reminder emails 15 minutes before task start';

    public function handle()
    {
        $now = now();

        $this->info('Current time: ' . $now);

        $tasks = Task::where('status', 'pending')
            ->where('reminder_sent', false)
            ->whereBetween('start_time', [
                $now->copy()->addMinutes(15),
                $now->copy()->addMinutes(15),
            ])
            ->get();

        $this->info('Tasks found: ' . $tasks->count());

        foreach ($tasks as $task) {
            SendTaskStartEmail::dispatch($task);

            $task->update(['reminder_sent' => true]);

            $this->info("Reminder dispatched for Task ID: {$task->id}");
        }

        Log::info('Reminder command executed', [
            'time' => $now,
            'tasks_found' => $tasks->count(),
        ]);
    }
}
