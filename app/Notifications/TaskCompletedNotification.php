<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Task;
use Carbon\Carbon;

class TaskCompletedNotification extends Notification
{
    use Queueable;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $completedAt = $this->task->completed_at ? Carbon::parse($this->task->completed_at)->format('d M Y, h:i A') : 'Not set';
        return (new MailMessage)
            ->subject('Task Completed')
            ->line("Your task '{$this->task->title}' is completed.")
            ->line("Completed at: {$completedAt}");
    }
}
