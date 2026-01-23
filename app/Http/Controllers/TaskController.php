<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Mail\TaskReminderMail;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendTaskStartEmail;
use Carbon\Carbon;
use App\Models\RecurringTask;


class TaskController extends Controller
{
    // In TaskController.php
    public function index()
    {
        $tasks = Task::with('user')
            ->where('status', '!=', 'template') // 👈 Only show real tasks
            ->latest()
            ->paginate(10);

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $users = User::all();
        return view('tasks.create', compact('users'));
    }


    public function store(Request $request)
    {
        //  Parse start datetime safely
        try {
            $dateInput = $request->start_date;
            $timeInput = $request->start_time ?: '00:00';
            $startDateTime = Carbon::parse($dateInput . ' ' . $timeInput);
        } catch (\Exception $e) {
            $startDateTime = now();
        }

        // One-time task Logic: Create immediately as 'pending'
        if (!$request->recurring) {
            $endDateTime = $request->filled('end_date')
                ? Carbon::createFromFormat('d/m/Y H:i', $request->end_date . ' ' . ($request->end_time ?: '23:59'))
                : null;

            $task = Task::create([
                'title'       => $request->title,
                'description' => $request->description,
                'user_id'     => $request->user_id,
                'start_time'  => $startDateTime,
                'end_time'    => $endDateTime,
                'repeat_type' => 'none',
                'status'      => 'pending',
            ]);

            $sendTime = $startDateTime->copy()->subMinutes(15);
            SendTaskStartEmail::dispatch($task)->delay($sendTime->isPast() ? now() : $sendTime);
        }
        //  Recurring task Logic: Create a hidden 'template' task + the Rule
        else {
            // Create the hidden "Master" task to store Title and Description
            $masterTask = Task::create([
                'title'       => $request->title,
                'description' => $request->description,
                'user_id'     => $request->user_id,
                'start_time'  => $startDateTime,
                'status'      => 'template', // This hides it from your main list
                'repeat_type' => $request->recurring,
            ]);

            $recurringEndDate = null;
            if ($request->recurring_end_date) {
                try {
                    $recurringEndDate = Carbon::createFromFormat('d/m/Y', $request->recurring_end_date)->toDateString();
                } catch (\Exception $e) {
                    $recurringEndDate = Carbon::parse($request->recurring_end_date)->toDateString();
                }
            }

            // Store the rule linked to the master task
            RecurringTask::create([
                'task_id'      => $masterTask->id, // 👈 Link the rule to the template
                'repeat_type'  => $request->recurring,
                'start_date'   => $startDateTime->toDateString(),
                'end_date'     => $recurringEndDate,
                'daily_time'   => $request->daily_time,
                'weekly_time'  => $request->weekly_time,
                'week_days'    => $request->week_days,
                'monthly_date' => $request->monthly_date,
                'monthly_time' => $request->monthly_time,
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Schedule saved successfully.');
    }
    // private function generateRecurringTask(RecurringTask $recurring)
    // {
    //     $task = $recurring->task;
    //     $firstTaskDate = Carbon::parse($task->start_time); // the first scheduled task
    //     $endDateForDay = $firstTaskDate->copy()->addDays(3); // generate tasks up to 1 day in future
    //     $endDateForWeek = $firstTaskDate->copy()->addWeeks(3); // generate tasks up to 1 week in future
    //     $endDateForMonth = $firstTaskDate->copy()->addMonths(12); // generate tasks up to 12 months in future

    //     switch ($recurring->repeat_type) {

    //         // 🔹 Daily recurrence
    //         case 'daily':
    //             $time = $recurring->daily_time ?: '00:00';
    //             $timeCarbon = Carbon::createFromFormat('H:i', $time);
    //             $current = $firstTaskDate->copy()->addDay(); // start AFTER first task

    //             while ($current->lte($endDateForDay)) {
    //                 // Prevent duplicates
    //                 if (!Task::where('title', $task->title)
    //                     ->whereDate('start_time', $current->toDateString())
    //                     ->exists()) {

    //                     Task::create([
    //                         'title'       => $task->title,
    //                         'description' => $task->description,
    //                         'user_id'     => $task->user_id,
    //                         'start_time'  => $current->copy()->setTime($timeCarbon->hour, $timeCarbon->minute),
    //                         'repeat_type' => 'daily',
    //                         'status'      => 'pending',
    //                     ]);
    //                 }

    //                 $current->addDay();
    //             }
    //             break;

    //         // 🔹 Weekly recurrence
    //         case 'weekly':
    //             $time = $recurring->weekly_time ?: '00:00';
    //             $timeCarbon = Carbon::createFromFormat('H:i', $time);
    //             $weekDays = is_array($recurring->week_days) ? $recurring->week_days : [];

    //             foreach ($weekDays as $day) {
    //                 // Convert 1-7 (Mon-Sun) to Carbon 0-6 (Sun-Sat)
    //                 $carbonDay = ((int)$day === 7) ? 0 : (int)$day;

    //                 // Start from the first occurrence AFTER the first task
    //                 $current = $firstTaskDate->copy();
    //                 while ($current->dayOfWeek !== $carbonDay) {
    //                     $current->addDay();
    //                 }
    //                 $current->addWeek(); // ensure we start after the first task

    //                 while ($current->lte($endDateForWeek)) {
    //                     if (!Task::where('title', $task->title)
    //                         ->whereDate('start_time', $current->toDateString())
    //                         ->exists()) {

    //                         Task::create([
    //                             'title'       => $task->title,
    //                             'description' => $task->description,
    //                             'user_id'     => $task->user_id,
    //                             'start_time'  => $current->copy()->setTime($timeCarbon->hour, $timeCarbon->minute),
    //                             'repeat_type' => 'weekly',
    //                             'status'      => 'pending',
    //                         ]);
    //                     }

    //                     $current->addWeek(); // move to next week same weekday
    //                 }
    //             }
    //             break;

    //         // 🔹 Monthly recurrence
    //         case 'monthly':
    //             if ($recurring->monthly_date) {
    //                 $monthlyDate = $recurring->monthly_date instanceof Carbon
    //                     ? $recurring->monthly_date->copy()
    //                     : Carbon::createFromFormat('d/m/Y', $recurring->monthly_date);

    //                 $time = $recurring->monthly_time ?: '00:00';
    //                 $timeCarbon = Carbon::createFromFormat('H:i', $time);

    //                 $current = $monthlyDate->copy();

    //                 // Start strictly AFTER the first task
    //                 if ($current->lte($firstTaskDate)) {
    //                     $current = $firstTaskDate->copy()->addMonth();
    //                     // Keep the original day of month if possible
    //                     $current->day = $monthlyDate->day;
    //                 }

    //                 while ($current->lte($endDateForMonth)) {
    //                     if (!Task::where('title', $task->title)
    //                         ->whereDate('start_time', $current->toDateString())
    //                         ->exists()) {

    //                         Task::create([
    //                             'title'       => $task->title,
    //                             'description' => $task->description,
    //                             'user_id'     => $task->user_id,
    //                             'start_time'  => $current->copy()->setTime($timeCarbon->hour, $timeCarbon->minute),
    //                             'repeat_type' => 'monthly',
    //                             'status'      => 'pending',
    //                         ]);
    //                     }

    //                     $current->addMonth();
    //                 }
    //             }
    //             break;
    //     }
    // }

    private function generateRecurringTask(RecurringTask $recurring)
    {
        $task = $recurring->task;
        $firstTaskDate = Carbon::parse($task->start_time); // the first scheduled task

        // Set how far in the future tasks should be generated
        $endDateForDay   = $firstTaskDate->copy()->addDays(7);   // 1 week
        $endDateForWeek  = $firstTaskDate->copy()->addWeeks(4);  // 1 month
        $endDateForMonth = $firstTaskDate->copy()->addMonths(12); // 12 months

        switch ($recurring->repeat_type) {

            // 🔹 Daily recurrence
            case 'daily':
                $time = $recurring->daily_time ?: '00:00';
                $timeCarbon = Carbon::createFromFormat('H:i', $time);

                // Start AFTER the first task
                $current = $firstTaskDate->copy()->addDay()->setTime($timeCarbon->hour, $timeCarbon->minute);

                while ($current->lte($endDateForDay)) {
                    if (!Task::where('title', $task->title)
                        ->whereDate('start_time', $current->toDateString())
                        ->exists()) {

                        Task::create([
                            'title'       => $task->title,
                            'description' => $task->description,
                            'user_id'     => $task->user_id,
                            'start_time'  => $current->copy(),
                            'repeat_type' => 'daily',
                            'status'      => 'pending',
                        ]);
                    }
                    $current->addDay();
                }
                break;

            // 🔹 Weekly recurrence
            case 'weekly':
                $time = $recurring->weekly_time ?: '00:00';
                $timeCarbon = Carbon::createFromFormat('H:i', $time);
                $weekDays = is_array($recurring->week_days) ? $recurring->week_days : [];

                foreach ($weekDays as $day) {
                    $carbonDay = Carbon::parse($day)->dayOfWeek;

                    $current = $firstTaskDate->copy()->addDay(); // start AFTER first task
                    while ($current->dayOfWeek !== $carbonDay) {
                        $current->addDay();
                    }

                    while ($current->lte($endDateForWeek)) {
                        if (!Task::where('title', $task->title)
                            ->whereDate('start_time', $current->toDateString())
                            ->exists()) {

                            Task::create([
                                'title'       => $task->title,
                                'description' => $task->description,
                                'user_id'     => $task->user_id,
                                'start_time'  => $current->copy()->setTime($timeCarbon->hour, $timeCarbon->minute),
                                'repeat_type' => 'weekly',
                                'status'      => 'pending',
                            ]);
                        }
                        $current->addWeek();
                    }
                }
                break;

            // 🔹 Monthly recurrence
            case 'monthly':
                if ($recurring->monthly_date) {
                    $monthlyDate = $recurring->monthly_date instanceof Carbon
                        ? $recurring->monthly_date->copy()
                        : Carbon::parse($recurring->monthly_date);

                    $time = $recurring->monthly_time ?: '00:00';
                    $timeCarbon = Carbon::createFromFormat('H:i', $time);

                    $current = $monthlyDate->copy();

                    // Start strictly AFTER the first task
                    if ($current->lte($firstTaskDate)) {
                        $current = $firstTaskDate->copy()->addMonth();
                        $current->day = $monthlyDate->day; // keep original day
                    }

                    while ($current->lte($endDateForMonth)) {
                        if (!Task::where('title', $task->title)
                            ->whereDate('start_time', $current->toDateString())
                            ->exists()) {

                            Task::create([
                                'title'       => $task->title,
                                'description' => $task->description,
                                'user_id'     => $task->user_id,
                                'start_time'  => $current->copy()->setTime($timeCarbon->hour, $timeCarbon->minute),
                                'repeat_type' => 'monthly',
                                'status'      => 'pending',
                            ]);
                        }
                        $current->addMonth();
                    }
                }
                break;
        }
    }





    public function edit(Task $task)
    {
        $users = User::all();
        return view('tasks.edit', compact('task', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date_format:d/m/Y',
            'start_time'  => 'required',
            'end_date'    => 'nullable|date_format:d/m/Y',
            'end_time'    => 'nullable',
            'user_id'     => 'required|exists:users,id',
        ]);

        $startDateTime = Carbon::createFromFormat(
            'd/m/Y H:i',
            $request->start_date . ' ' . $request->start_time
        );

        $endDateTime = null;

        if ($request->filled('end_date')) {
            $endTime = $request->end_time ?: '23:59';
            $endDateTime = Carbon::createFromFormat(
                'd/m/Y H:i',
                $request->end_date . ' ' . $endTime
            );

            if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
                if ($startDateTime->toDateString() != $endDateTime->toDateString()) {
                    return back()->withErrors([
                        'end_date' => 'End date must be after start date.'
                    ])->withInput();
                } else {
                    return back()->withErrors([
                        'end_time' => 'End time must be after start time.'
                    ])->withInput();
                }
            }
        }

        $task->update([
            'title'       => $request->title,
            'description' => $request->description,
            'start_time'  => $startDateTime,
            'end_time'    => $endDateTime,
            'user_id'     => $request->user_id,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task Updated Successfully');
    }

    public function start(Task $task)
    {
        $task->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);

        if ($task->user) {
            Mail::to($task->user->email)->send(new TaskReminderMail($task));
        }

        return back()->with('success', 'Task started and user notified');
    }

    public function complete(Task $task)
    {
        $task->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        return back()->with('success', 'Task Completed');
    }

    public function show(Task $task)
    {
        return view('tasks.show', compact('task'));
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return back()->with('success', 'Task Deleted');
    }

    public function datatable()
    {
        $query = Task::with('user')
        ->where('status', '!=', 'template') 
        ->select('tasks.*');

        return DataTables::of($query)
            ->addColumn('user', fn($row) => $row->user->name ?? '-')
            ->editColumn('description', fn($row) => Str::limit($row->description, 80))
            ->addColumn('status', function ($row) {
                $color = $row->status == 'completed' ? 'success' : ($row->status == 'in_progress' ? 'dark' : 'primary');
                return '<span class="badge text-' . $color . ' text-capitalize text-start">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('action', function ($row) {

                $view = '<li>
                    <a class="dropdown-item text-primary" href="' . route('tasks.show', $row->id) . '">
                        <i class="bi bi-eye text-primary me-2"></i> View
                    </a>
                </li>';

                $edit = '<li>
                    <a class="dropdown-item text-primary" href="' . route('tasks.edit', $row->id) . '">
                        <i class="bi bi-pencil-square me-2"></i> Edit
                    </a>
                </li>';

                $start = '<li>
                    <form action="' . route('tasks.start', $row->id) . '" method="POST">' . csrf_field() . '
                        <button type="submit" class="dropdown-item text-primary">
                            <i class="bi bi-play-circle me-2"></i> Start
                        </button>
                    </form>
                </li>';

                $complete = '<li>
                    <form action="' . route('tasks.complete', $row->id) . '" method="POST">' . csrf_field() . '
                        <button type="submit" class="dropdown-item text-success">
                            <i class="bi bi-check-circle me-2"></i> Complete
                        </button>
                    </form>
                </li>';

                $delete = '<li>
                    <form action="' . route('tasks.destroy', $row->id) . '" method="POST" 
                          onsubmit="return confirm(\'Delete this task?\')">' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-trash me-2"></i> Delete
                        </button>
                    </form>
                </li>';

                $items = '';

                if ($row->status === 'completed') {
                    $items .= $view . $delete;
                } elseif ($row->status === 'pending') {
                    $items .= $view . $edit . $start . $delete;
                } elseif ($row->status === 'in_progress') {
                    $items .= $complete . $view . $delete;
                }

                return '<div class="dropdown text-start">
                    <button class="btn btn-sm dropdown-toggle border-0 p-0" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots text-primary fs-5"></i>
                    </button>
                    <ul class="dropdown-menu">' . $items . '</ul>
                </div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}
