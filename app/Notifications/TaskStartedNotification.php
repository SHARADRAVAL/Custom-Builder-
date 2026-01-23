<?php
// app/Notifications/TaskStartedNotification.php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Task;

class TaskStartedNotification extends Notification
{
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
        return (new MailMessage)
            ->subject('Reminder: Task "' . $this->task->title . '" is starting soon')
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line('Your task will start soon. Here are the details:')
            ->line('**Task:** ' . $this->task->title)
            ->line('**Description:** ' . ($this->task->description ?? 'No description'))
            ->line('**Start Time:** ' . $this->task->start_time->format('d M Y, h:i A'))
            ->line('Please make sure to be ready.');
    }
}
