<?php

namespace App\Http\Controllers;

//Models 
use App\Models\{Task, User, RecurringTask};

// Email 
use App\Jobs\SendTaskStartEmail;

//Datatable
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TaskController extends Controller
{
    // Task Index
    public function index()
    {
        $tasks = Task::with(['users:id,name'])
            ->select('id', 'title', 'status', 'created_at')
            ->where('status', '!=', 'template')
            ->latest()
            ->paginate(10);


        return view('tasks.index', compact('tasks'));
    }

    // search user to do not chack 10000 every user 
    public function search(Request $request)
    {
        $q = $request->get('q', '');

        $users = User::select('id', 'name')
            ->where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json($users);
    }

    // Create Task
    public function create()
    {
        // Only fetch id and name, sorted by name
        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('tasks.create', compact('users'));
    }

    // Store Task
    public function store(Request $request)
    {
        // ------------------------
        // Validation
        // ------------------------
        $request->validate([
            'title'      => 'required|string|max:255',
            'user_ids'   => 'required_without:recurring|array|min:1', // only required if not recurring
            'user_ids.*' => 'exists:users,id',
            'user_id'    => 'required_if:recurring,true|exists:users,id', // required for recurring
            'start_date' => 'required_without:recurring',
            'start_time' => 'required_without:recurring',
        ]);


        // ------------------------
        // Determine task type
        // ------------------------
        $isRecurring = $request->filled('recurring');
        $mainUserId  = $isRecurring ? $request->user_id : $request->user_ids[0];

        // ------------------------
        // Calculate start & due datetimes
        // ------------------------
        $startDateTime = $this->getStartDateTime($request);
        $dueDateTime   = $this->getDueDateTime($request);
        $dueDays       = $this->calculateDueDays($dueDateTime);

        // ------------------------
        // Create Task
        // ------------------------
        $task = Task::create([
            'title'       => $request->title,
            'description' => $request->description,
            'user_id'     => $mainUserId,
            'start_time'  => $startDateTime,
            'end_time'    => $dueDateTime,
            'due_days'    => $dueDays,
            'repeat_type' => $isRecurring ? $request->recurring : 'none',
            'status'      => $isRecurring ? 'template' : 'pending',
        ]);

        $mainUserId = $isRecurring ? $request->user_id : $request->user_ids[0];

        // Assign users
        $task->users()->sync($isRecurring ? [$mainUserId] : $request->user_ids);

        // Schedule start email for non-recurring tasks
        if (!$isRecurring) {
            $sendTime = $startDateTime->copy()->subMinutes(15);
            SendTaskStartEmail::dispatch($task)->delay($sendTime->isPast() ? now() : $sendTime);
        }

        // Create RecurringTask if applicable
        if ($isRecurring) {
            $this->createRecurringTask($task, $request, $startDateTime);
        }

        return redirect()->route('tasks.index')->with('success', 'Task saved successfully.');
    }


    // Helper Methods for store 

    protected function getStartDateTime(Request $request)
    {
        if ($request->filled('start_date') && $request->filled('start_time')) {
            return Carbon::createFromFormat(
                'd/m/Y H:i',
                $request->start_date . ' ' . $request->start_time
            );
        }

        $time = $request->daily_time ?: $request->weekly_time ?: $request->monthly_time ?: '00:00';
        $date = $request->monthly_date ?: now()->format('d/m/Y');

        return Carbon::createFromFormat('d/m/Y H:i', $date . ' ' . $time);
    }

    protected function getDueDateTime(Request $request)
    {
        if (!$request->filled('end_date')) {
            return null;
        }

        return Carbon::createFromFormat(
            'd/m/Y H:i',
            $request->end_date . ' ' . ($request->end_time ?: '23:59')
        );
    }

    protected function calculateDueDays(?Carbon $dueDateTime): ?int
    {
        if (!$dueDateTime || now()->lessThanOrEqualTo($dueDateTime)) {
            return null;
        }

        return (int) abs(now()->diffInDays($dueDateTime));
    }

    protected function createRecurringTask(Task $task, Request $request, Carbon $startDateTime)
    {
        RecurringTask::create([
            'task_id'      => $task->id,
            'repeat_type'  => $request->recurring,
            'start_date'   => $startDateTime->toDateString(),
            'end_date'     => $request->recurring_end_date
                ? Carbon::createFromFormat('d/m/Y', $request->recurring_end_date)->toDateString()
                : null,
            'daily_time'   => $request->daily_time,
            'weekly_time'  => $request->weekly_time,
            'week_days'    => $request->week_days,
            'monthly_date' => $request->monthly_date
                ? Carbon::createFromFormat('d/m/Y', $request->monthly_date)->toDateString()
                : null,
            'monthly_time' => $request->monthly_time,
        ]);
    }

    // show assigned task user 
    public function show(Task $task)
    {
        // Only fetch users assigned to this task
        $users = $task->users()
            ->select('users.id', 'users.name')
            ->orderBy('users.name')
            ->get();

        return view('tasks.show', compact('task', 'users'));
    }



    // Edit Task
    public function edit(Task $task)
    {
        $users = $task->users()
            ->select('users.id', 'users.name') // avoid ambiguous id
            ->orderBy('users.name')
            ->get();

        return view('tasks.edit', compact('task', 'users'));
    }

    // update Task
    public function update(Request $request, Task $task)
    {
        // ------------------------
        // Validation
        // ------------------------
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date_format:d/m/Y',
            'start_time'  => 'required|date_format:H:i',
            'end_date'    => 'nullable|date_format:d/m/Y',
            'end_time'    => 'nullable|date_format:H:i',
            // 'user_id'  => 'required|exists:users,id', // uncomment if needed
        ]);


        // Parse start & end datetime

        $startDateTime = $this->parseDateTime($request->start_date, $request->start_time);
        $endDateTime   = $request->filled('end_date')
            ? $this->parseDateTime($request->end_date, $request->end_time ?? '23:59')
            : null;


        // Validate chronological order

        if ($endDateTime && $endDateTime->lessThanOrEqualTo($startDateTime)) {
            if ($startDateTime->toDateString() !== $endDateTime->toDateString()) {
                return back()->withErrors([
                    'end_date' => 'End date must be after start date.'
                ])->withInput();
            } else {
                return back()->withErrors([
                    'end_time' => 'End time must be after start time.'
                ])->withInput();
            }
        }


        // Update task
        $task->update([
            'title'       => $request->title,
            'description' => $request->description,
            'start_time'  => $startDateTime,
            'end_time'    => $endDateTime,
            'user_id'     => $request->user_id ?? $task->user_id,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    // Helper Method for Parsing Date & Time
    protected function parseDateTime(string $date, string $time): Carbon
    {
        return Carbon::createFromFormat('d/m/Y H:i', $date . ' ' . $time);
    }

    // Task Datatable
    public function datatable()
    {
        $query = Task::with('users:id,name')->where('status', '!=', 'template')->select('tasks.*');


        return DataTables::of($query)
            // ->addColumn('task_id', function ($row) {
            //     // Show parent template ID if exists, else task's own ID
            //     return $row->parent_task_id ?? $row->id;
            // })
            ->addIndexColumn()
            ->addColumn('user', function ($row) {
                $names = $row->users->pluck('name')->take(5)->toArray();
                return implode(', ', $names) . (count($row->users) > 5 ? ' ...' : '');
            })
            ->addColumn('due', fn($row) => $this->formatDueColumn($row))
            ->editColumn('description', fn($row) => Str::limit($row->description, 80))
            ->addColumn('status', fn($row) => $this->formatStatusBadge($row->status))
            ->addColumn('action', fn($row) => $this->formatActionDropdown($row))
            ->rawColumns(['due', 'status', 'action'])
            ->make(true);
    }


    // Helper Methods for Datatable
    protected function formatDueColumn($row)
    {
        if (!$row->end_time) return '<span class="text-muted">-</span>';

        $now = now()->startOfDay();
        $due = $row->end_time->startOfDay();

        // Only show overdue for incomplete tasks
        if ($row->status !== 'completed' && $now->greaterThan($due)) {
            // Calculate difference in days (always positive integer)
            $days = $due->diffInDays($now);
            return '<span class="text-danger fw-semibold" title="Deadline: ' . $due->format('d M Y, h:i A') . '">'
                . $days . ' Days</span>';
        }

        return '<span class="text-muted">-</span>';
    }

    protected function formatStatusBadge(string $status): string
    {
        $colors = [
            'completed'   => 'success',
            'in_progress' => 'dark',
            'pending'     => 'primary',
        ];

        $color = $colors[$status] ?? 'secondary';

        return '<span class="badge text-' . $color . ' text-capitalize text-start">'
            . ucfirst($status) . '</span>';
    }

    protected function formatActionDropdown($row): string
    {
        $view = $this->dropdownItem(route('tasks.show', $row->id), 'View', 'bi-eye', 'text-primary');
        $edit = $this->dropdownItem(route('tasks.edit', $row->id), 'Edit', 'bi-pencil-square', 'text-primary');
        $start = $this->dropdownButton($row->id, 'Start', 'bi-play-circle', 'text-primary', 'startTaskBtn');
        $complete = $this->dropdownForm(route('tasks.complete', $row->id), 'Complete', 'bi-check-circle', 'text-success');
        $delete = $this->dropdownForm(route('tasks.delete', $row->id), 'Delete', 'bi-trash', 'text-danger', 'DELETE', 'deleteTaskForm');

        $items = match ($row->status) {
            'completed' => $view . $delete,
            'pending' => $view . $edit . $start . $delete,
            'in_progress' => $complete . $view . $delete,
            default => $view
        };

        return '<div class="dropdown text-start">
                <button class="btn btn-sm dropdown-toggle border-0 p-0" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots text-primary fs-5"></i>
                </button>
                <ul class="dropdown-menu">' . $items . '</ul>
            </div>';
    }


    // Dropdown Helper Functions    
    protected function dropdownItem(string $url, string $label, string $icon, string $class = ''): string
    {
        return '<li><a class="dropdown-item ' . $class . '" href="' . $url . '">
                <i class="bi ' . $icon . ' me-2"></i>' . $label . '</a></li>';
    }

    protected function dropdownButton(int $id, string $label, string $icon, string $class = '', string $btnClass = ''): string
    {
        return '<li><button type="button" class="dropdown-item ' . $class . ' ' . $btnClass . '" data-id="' . $id . '">
                <i class="bi ' . $icon . ' me-2"></i>' . $label . '</button></li>';
    }

    protected function dropdownForm(string $url, string $label, string $icon, string $class = '', string $method = 'POST', string $formClass = ''): string
    {
        $methodField = $method !== 'POST' ? method_field($method) : '';
        return '<li>
        <form action="' . $url . '" method="POST" class="' . $formClass . '">
            ' . csrf_field() . $methodField . '
            <button type="submit" class="dropdown-item ' . $class . '">
                <i class="bi ' . $icon . ' me-2"></i>' . $label . '
            </button>
        </form>
    </li>';
    }


    // Task Start with email
    public function start(Task $task)
    {
        $this->updateTaskStatus($task, 'in_progress', ['started_at' => now()]);

        // Optional: Notify the user
        // if ($task->user) {
        //     Mail::to($task->user->email)->send(new TaskReminderMail($task)); // remove comment if send email when task start
        // }

        return back()->with('success', 'Task started and user notified');
    }

    // Task Complete
    public function complete(Task $task)
    {
        $this->updateTaskStatus($task, 'completed', ['completed_at' => now()]);

        return back()->with('success', 'Task completed');
    }

    // Helper to update status and additional fields
    private function updateTaskStatus(Task $task, string $status, array $extraFields = [])
    {
        $task->update(array_merge(['status' => $status], $extraFields));
    }

    // Task Delete
    public function destroy(Task $task)
    {
        $task->delete();
        return back()->with('success', 'Task deleted successfully.');
    }

    // Task Feedback & Comment
    public function commentFeedback(Request $request, Task $task)
    {
        $validated = $request->validate([
            'feedback' => 'required|string|max:255',
            'comment'  => 'required|string|max:255',
        ]);

        $task->update($validated);

        return back()->with('success', 'Feedback submitted successfully.');
    }
}
