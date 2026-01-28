<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\RecurringTask;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessRecurringTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected RecurringTask $recurring;
    protected Carbon $executionTime;

    public function __construct(RecurringTask $recurring, string $executionTime)
    {
        $this->recurring = $recurring;
        $this->executionTime = Carbon::parse($executionTime);
    }

    public function handle(): void
    {
        $template = $this->recurring->task()->with('users')->first();
        if (!$template) return;

        DB::transaction(function () use ($template) {

            // ✅ Duplicate check using parent_task_id + date
            $exists = Task::where('parent_task_id', $template->id)
                ->whereDate('start_time', $this->executionTime->toDateString())
                ->exists();

            if ($exists) return;

            // ✅ Check skip_dates before creating task
            if (!empty($this->recurring->skip_dates) && in_array($this->executionTime->toDateString(), $this->recurring->skip_dates)) {
                return; // Skip task for this date
            }

            // 1️⃣ Create new task with parent_task_id
            $newTask = Task::create([
                'title'          => $template->title,
                'description'    => $template->description,
                'user_id'        => $template->user_id,
                'start_time'     => $this->executionTime,
                'status'         => 'pending',
                'repeat_type'    => $this->recurring->repeat_type,
                'parent_task_id' => $template->id, // NEW
            ]);

            // 2️⃣ Sync users from the template task
            if ($template->users->isNotEmpty()) {
                $newTask->users()->sync($template->users->pluck('id'));
            }

            // 3️⃣ Update next_run_at for recurring rule
            $this->recurring->update([
                'next_run_at' => $this->recurring->calculateNextRun()
            ]);
        });
    }
}
