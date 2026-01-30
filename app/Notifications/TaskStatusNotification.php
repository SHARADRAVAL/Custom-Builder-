<?php
// app/Notifications/TaskStatusNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Task;

class TaskStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Task $task;
    protected string $status; // 'started' or 'completed'

    /**
     * @param Task $task
     * @param string $status 'started' or 'completed'
     */
    public function __construct(Task $task, string $status)
    {
        $this->task = $task;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $userName = $notifiable->name ?? 'User';
        $title = $this->task->title;
        $description = $this->task->description ?? 'No description';

        if ($this->status === 'started') {
            $time = $this->task->start_time instanceof \Carbon\Carbon
                ? $this->task->start_time->format('d M Y, h:i A')
                : $this->task->start_time;

            return (new MailMessage)
                ->subject("Reminder: Task \"$title\" is starting soon")
                ->greeting("Hello $userName,")
                ->line("Your task is about to start. Here are the details:")
                ->line("**Task:** $title")
                ->line("**Description:** $description")
                ->line("**Start Time:** $time")
                ->line("Please make sure to be ready.");

        } elseif ($this->status === 'completed') {
            $time = $this->task->completed_at instanceof \Carbon\Carbon
                ? $this->task->completed_at->format('d M Y, h:i A')
                : $this->task->completed_at ?? 'Not set';

            return (new MailMessage)
                ->subject("Task Completed: \"$title\"")
                ->greeting("Hello $userName,")
                ->line("Your task has been completed. Here are the details:")
                ->line("**Task:** $title")
                ->line("**Description:** $description")
                ->line("**Completed At:** $time");
        }

        // Default fallback (should not happen)
        return (new MailMessage)
            ->subject("Task Update: \"$title\"")
            ->line("Your task \"$title\" has been updated.");
    }
}
