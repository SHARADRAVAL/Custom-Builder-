<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\RecurringTask;
use App\Jobs\SendTaskStartEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('users')
            ->where('status', '!=', 'template')
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
        // FIX: Make start_date and start_time required ONLY if recurring is NOT selected
        $request->validate([
            'title'      => 'required|string|max:255',
            'user_id'    => 'required|exists:users,id',
            'start_date' => 'required_without:recurring', // Required only if recurring is empty
            'start_time' => 'required_without:recurring', // Required only if recurring is empty
        ]);

        // Handle Start Date Time logic
        if ($request->filled('start_date') && $request->filled('start_time')) {
            $startDateTime = Carbon::createFromFormat('d/m/Y H:i', $request->start_date . ' ' . $request->start_time);
        } else {
            // If it's a recurring task and start_date is hidden, 
            // we default to today's date plus the specific recurring time
            $time = $request->daily_time ?: ($request->weekly_time ?: ($request->monthly_time ?: '00:00'));
            $date = $request->monthly_date ?: now()->format('d/m/Y');
            $startDateTime = Carbon::createFromFormat('d/m/Y H:i', $date . ' ' . $time);
        }

        $dueDateTime = $request->filled('end_date')
            ? Carbon::createFromFormat('d/m/Y H:i', $request->end_date . ' ' . ($request->end_time ?: '23:59'))
            : null;

        $dueDays = ($dueDateTime && now()->greaterThan($dueDateTime))
            ? (int) abs(now()->diffInDays($dueDateTime))
            : null;

        if (!$request->recurring) {
            $task = Task::create([
                'title'       => $request->title,
                'description' => $request->description,
                'user_id'     => $request->user_id,
                'start_time'  => $startDateTime,
                'end_time'    => $dueDateTime,
                'due_days'    => $dueDays,
                'repeat_type' => 'none',
                'status'      => 'pending',
            ]);

            $task->users()->sync([$request->user_id]);

            $sendTime = $startDateTime->copy()->subMinutes(15);
            SendTaskStartEmail::dispatch($task)->delay($sendTime->isPast() ? now() : $sendTime);
        } else {
            $masterTask = Task::create([
                'title'       => $request->title,
                'description' => $request->description,
                'user_id'     => $request->user_id,
                'start_time'  => $startDateTime,
                'due_days'    => $dueDays,
                'status'      => 'template',
                'repeat_type' => $request->recurring,
            ]);

            $masterTask->users()->sync([$request->user_id]);

            RecurringTask::create([
                'task_id'      => $masterTask->id,
                'repeat_type'  => $request->recurring,
                'start_date'   => $startDateTime->toDateString(),
                'end_date'     => $request->recurring_end_date
                    ? Carbon::createFromFormat('d/m/Y', $request->recurring_end_date)->toDateString()
                    : null,
                'daily_time'   => $request->daily_time,
                'weekly_time'  => $request->weekly_time,
                'week_days'    => $request->week_days, // Corrected (no json_encode)

                // FIX IS HERE: Parse the monthly_date correctly
                'monthly_date' => $request->monthly_date
                    ? Carbon::createFromFormat('d/m/Y', $request->monthly_date)->toDateString()
                    : null,

                'monthly_time' => $request->monthly_time,
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task saved successfully.');
    }
    public function datatable()
    {
        $query = Task::with('users')->where('status', '!=', 'template')->select('tasks.*');

        return DataTables::of($query)
            ->addColumn('user', function ($row) {
                return $row->users->pluck('name')->implode(', ') ?: '-';
            })
            ->addColumn('due', function ($row) {
                if (!$row->end_time) return '<span class="text-muted">-</span>';

                $now = now();
                $due = $row->end_time;

                if ($row->status !== 'completed' && $now->greaterThan($due)) {
                    $days = (int) abs($row->due_days ?? $now->diffInDays($due));
                    return '<span class="text-danger fw-semibold" title="Deadline: ' . $due->format('d M Y, h:i A') . '">
                                ' . $days . ' Days
                            </span>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->editColumn('description', fn($row) => Str::limit($row->description, 80))
            ->addColumn('status', function ($row) {
                $color = $row->status == 'completed' ? 'success' : ($row->status == 'in_progress' ? 'dark' : 'primary');
                return '<span class="badge text-' . $color . ' text-capitalize text-start">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $view = '<li><a class="dropdown-item text-primary" href="' . route('tasks.show', $row->id) . '"><i class="bi bi-eye me-2"></i> View</a></li>';
                $edit = '<li><a class="dropdown-item text-primary" href="' . route('tasks.edit', $row->id) . '"><i class="bi bi-pencil-square me-2"></i> Edit</a></li>';
                $start = '<li><button type="button" class="dropdown-item text-primary startTaskBtn" data-id="' . $row->id . '"><i class="bi bi-play-circle me-2"></i> Start</button></li>';
                $complete = '<li><form action="' . route('tasks.complete', $row->id) . '" method="POST">' . csrf_field() . '<button type="submit" class="dropdown-item text-success"><i class="bi bi-check-circle me-2"></i> Complete</button></form></li>';
              $delete = '<li>
    <form action="' . route('tasks.delete', $row->id) . '" method="POST" class="deleteTaskForm d-inline">
        ' . csrf_field() . '
        ' . method_field('DELETE') . '
        <button type="submit" class="dropdown-item text-danger">
            <i class="bi bi-trash me-2"></i> Delete
        </button>
    </form>
</li>';


                $items = '';
                if ($row->status === 'completed') $items .= $view . $delete;
                elseif ($row->status === 'pending') $items .= $view . $edit . $start . $delete;
                elseif ($row->status === 'in_progress') $items .= $complete . $view . $delete;

                return '<div class="dropdown text-start">
                            <button class="btn btn-sm dropdown-toggle border-0 p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots text-primary fs-5"></i></button>
                            <ul class="dropdown-menu">' . $items . '</ul>
                        </div>';
            })
            ->rawColumns(['due', 'status', 'action'])
            ->make(true);
    }

    public function start(Task $task)
    {
        $task->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);

        // if ($task->user) {
        //     Mail::to($task->user->email)->send(new TaskReminderMail($task));
        // }

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

    public function destroy($id)
    {
        Task::findOrFail($id)->delete();
        return back()->with('success', 'Task deleted');
    }
}
