<?php

namespace App\Jobs;

use App\Models\Task;
use App\Mail\TaskReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class SendTaskStartEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function handle(): void
    {
        // Safety check
        if (!$this->task || !$this->task->user) {
            return;
        }

        // Optional: prevent duplicate start emails
        if ($this->task->status !== 'pending') {
            return;
        }

        Mail::to($this->task->user->email)
            ->send(new TaskReminderMail($this->task));
        $this->task->update([
            'reminder_sent' => true,
        ]);
    }
}
