<?php

namespace App\Http\Controllers;

use App\Models\RecurringTask;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class RecurringTaskController extends Controller
{
    public function index()
    {
        $recurrings = RecurringTask::with('task')->latest()->paginate(10);
        $tasks = Task::all();
        // $users = User::all();

        return view('recurring_tasks.index', compact('recurrings', 'tasks'));
    }

    public function create()
    {
        return view('recurring_tasks.create', [
            'tasks' => Task::all(),
            'users' => User::all(),
        ]);
    }

    public function show(RecurringTask $recurringTask)
    {
        $task = $recurringTask->task;
        $user = $task->user;
        return view('recurring_tasks.show', compact('recurringTask', 'task', 'user'));
    }
    
    public function edit(RecurringTask $recurringTask)
    {
        $task = $recurringTask->task;
        $user = $task->user;

        $rec = $recurringTask;

        // Ensure task start/end times are Carbon instances
        $task->start_time = $task->start_time ? \Carbon\Carbon::parse($task->start_time) : null;
        $task->end_time   = $task->end_time ? \Carbon\Carbon::parse($task->end_time) : null;

        // Format recurring times for form inputs
        $rec->daily_time   = $rec->daily_time ? \Carbon\Carbon::parse($rec->daily_time)->format('H:i') : null;
        $rec->weekly_time  = $rec->weekly_time ? \Carbon\Carbon::parse($rec->weekly_time)->format('H:i') : null;
        $rec->monthly_time = $rec->monthly_time ? \Carbon\Carbon::parse($rec->monthly_time)->format('H:i') : null;
        $rec->monthly_date = $rec->monthly_date ? \Carbon\Carbon::parse($rec->monthly_date)->format('d/m/Y') : null;

        // Decode JSON week_days if stored as JSON
        $rec->week_days = $rec->week_days ? json_decode($rec->week_days) : [];

        return view('recurring_tasks.edit', [
            'task'  => $task,
            'user'  => $user,
            'rec'   => $rec,
            'users' => User::all(),   // For Assign To select
            'tasks' => Task::all(),   // If you need task list in form
        ]);
    }
    public function update(Request $request, RecurringTask $recurringTask)
    {
        $task = $recurringTask->task;

        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'user_id'       => 'required|exists:users,id',
            'recurring'     => 'nullable|in:daily,weekly,monthly',
            'daily_time'    => 'required_if:recurring,daily|date_format:H:i',
            'weekly_time'   => 'required_if:recurring,weekly|date_format:H:i',
            'week_days'     => 'required_if:recurring,weekly|array',
            'week_days.*'   => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'monthly_date'  => 'required_if:recurring,monthly|date_format:d/m/Y',
            'monthly_time'  => 'required_if:recurring,monthly|date_format:H:i',
            'start_date'    => 'required_if:recurring,NULL|date_format:d/m/Y',
            'start_time'    => 'required_if:recurring,NULL|date_format:H:i',
            'end_date'      => 'nullable|date_format:d/m/Y',
            'end_time'      => 'nullable|date_format:H:i',
        ]);

        try {
            // --- Update main task ---
            $task->title       = $request->title;
            $task->description = $request->description;
            $task->user_id     = $request->user_id;

            // For one-time tasks
            if (!$request->recurring) {
                $task->start_time = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $request->start_date . ' ' . $request->start_time);
                if ($request->end_date && $request->end_time) {
                    $task->end_time = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $request->end_date . ' ' . $request->end_time);
                }
            }

            $task->save();

            // --- Update recurring task ---
            $recurringTask->repeat_type  = $request->recurring;
            $recurringTask->daily_time   = $request->daily_time ?? null;
            $recurringTask->weekly_time  = $request->weekly_time ?? null;
            $recurringTask->monthly_time = $request->monthly_time ?? null;
            $recurringTask->monthly_date = $request->monthly_date ?? null;

            // Encode weekdays as JSON
            $recurringTask->week_days = $request->week_days ? json_encode($request->week_days) : null;

            $recurringTask->save();

            return redirect()->route('recurring-tasks.index')
                ->with('success', 'Task updated successfully');
        } catch (\Throwable $e) {
            Log::error('Recurring task update failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['time' => 'Invalid date/time input'])->withInput();
        }
    }

    

    public function store(Request $request)
    {
        $request->validate([
            'task_id'      => 'required|exists:tasks,id',
            'repeat_type'  => 'required|in:daily,weekly,monthly',
            'daily_time'   => 'required_if:repeat_type,daily|date_format:H:i',
            'weekly_time'  => 'required_if:repeat_type,weekly|date_format:H:i',
            'week_days'    => 'required_if:repeat_type,weekly|array',
            'week_days.*'  => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'monthly_date' => 'required_if:repeat_type,monthly|date_format:d/m/Y',
            'monthly_time' => 'required_if:repeat_type,monthly|date_format:H:i',
        ]);

        try {
            $data = $request->only([
                'task_id',
                'repeat_type',
                'daily_time',
                'weekly_time',
                'week_days',
                'monthly_date',
                'monthly_time',
            ]);

            // ✅ Convert numeric weekdays to strings if necessary
            if (!empty($data['week_days'])) {
                $daysMap = [
                    0 => 'Sunday',
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday',
                ];

                $data['week_days'] = array_map(fn($day) => $daysMap[(int)$day] ?? $day, $data['week_days']);
            }

            $recurring = RecurringTask::create($data);

            $this->generateRecurringTask($recurring);

            return redirect()->route('recurring-tasks.index')
                ->with('success', 'Recurring Task Created');
        } catch (\Throwable $e) {
            Log::error('Recurring task failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['time' => 'Invalid date/time input'])->withInput();
        }
    }



    private function generateRecurringTask(RecurringTask $recurring): void
    {
        $task = $recurring->task;

        match ($recurring->repeat_type) {
            'daily'   => $this->createDaily($recurring, $task),
            'weekly'  => $this->createWeekly($recurring, $task),
            'monthly' => $this->createMonthly($recurring, $task),
            default   => null,
        };
    }

    private function createDaily(RecurringTask $recurring, Task $task): void
    {
        $startTime = $recurring->daily_time
            ->copy()
            ->setDate(now()->year, now()->month, now()->day);

        Task::create([
            'title'       => $task->title,
            'description' => $task->description,
            'user_id'     => $task->user_id,
            'start_time'  => $startTime,
            'repeat_type' => 'daily',
            'status'      => 'pending',
        ]);
    }

    private function createWeekly(RecurringTask $recurring, Task $task): void
    {
        foreach ($recurring->week_days as $day) {
            $carbonDay = match ((int)$day) {
                1 => Carbon::MONDAY,
                2 => Carbon::TUESDAY,
                3 => Carbon::WEDNESDAY,
                4 => Carbon::THURSDAY,
                5 => Carbon::FRIDAY,
                6 => Carbon::SATURDAY,
                7 => Carbon::SUNDAY,
            };

            $date = now()->next($carbonDay);

            $startTime = $recurring->weekly_time
                ->copy()
                ->setDate($date->year, $date->month, $date->day);

            Task::firstOrCreate(
                [
                    'title'      => $task->title,
                    'user_id'    => $task->user_id,
                    'start_time' => $startTime,
                ],
                [
                    'description' => $task->description,
                    'repeat_type' => 'weekly',
                    'status'      => 'pending',
                ]
            );
        }
    }

    private function createMonthly(RecurringTask $recurring, Task $task): void
    {
        // Ensure monthly_date is a Carbon instance
        $date = $recurring->monthly_date instanceof \Carbon\Carbon
            ? $recurring->monthly_date
            : \Carbon\Carbon::parse($recurring->monthly_date);

        // Ensure monthly_time is a Carbon instance (parsing if stored as string)
        $time = $recurring->monthly_time instanceof \Carbon\Carbon
            ? $recurring->monthly_time
            : \Carbon\Carbon::createFromFormat('h:i A', $recurring->monthly_time); // e.g., '12:54 PM'

        // Combine date and time
        $startTime = $date->copy()
            ->setTime($time->hour, $time->minute, 0);

        // Create task if not exists
        Task::firstOrCreate(
            [
                'title'      => $task->title,
                'user_id'    => $task->user_id,
                'start_time' => $startTime,
            ],
            [
                'description' => $task->description,
                'repeat_type' => 'monthly',
                'status'      => 'pending',
            ]
        );
    }


  
    public function datatable(Request $request)
    {
        $tasks = RecurringTask::with('task.user'); // Load task and its user

        return DataTables::of($tasks)
            ->addColumn('title', fn($recurring) => $recurring->task->title ?? '-')
            ->addColumn('description', fn($recurring) => $recurring->task->description ?? '-')
            ->addColumn('user', fn($recurring) => $recurring->task->user->name ?? '-')
            ->addColumn('action', function ($recurring) {
                $task = $recurring->task;

                $actions = '<div class="dropdown">
                <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu">';

                // View
                $actions .= '<li><a class="dropdown-item" href="' . route('recurring-tasks.show', $recurring->id) . '">
                            <i class="bi bi-eye me-1"></i> View
                        </a></li>';
                // Delete
                $actions .= '<li>
                <form action="' . route('recurring-tasks.destroy', $recurring->id) . '" method="POST" onsubmit="return confirm(\'Delete this task?\')">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </li>';

                $actions .= '</ul></div>';

                return $actions;
            })
            ->rawColumns(['status', 'action']) // Render HTML
            ->make(true);
    }

    public function destroy(RecurringTask $recurringTask)
    {
        $task = $recurringTask->task;
        $recurringTask->delete();
        $task->delete();

        return redirect()->route('recurring-tasks.index')->with('success', 'Task deleted successfully');
    }
}
