<?php

namespace App\Http\Controllers;

use App\Models\{Task, User, RecurringTask};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Jobs\ProcessRecurringTask;
use Illuminate\Support\Facades\DB;



class RecurringTaskController extends Controller
{
    // Index page for Recurring 
    public function index()
    {
        // Paginate recurring tasks with eager loading for task
        $recurrings = RecurringTask::with(['task:id,title'])
            ->select('id', 'task_id', 'created_at')
            ->latest()
            ->paginate(10);

        // Only load tasks if needed for dropdowns, limit for performance
        $tasks = Task::select('id', 'title')
            ->orderBy('title')
            ->limit(1000) // Optional: prevent loading all if huge
            ->get();

        return view('recurring_tasks.index', compact('recurrings', 'tasks'));
    }


    public function create()
    {
        return view('recurring_tasks.create', [
            'users' => collect(),
        ]);
    }




    public function show(RecurringTask $recurringTask)
    {
        // Eager load task and its user with only required columns
        $recurringTask->load([
            'task:id,user_id,title,description,start_time,end_time,repeat_type,started_at,completed_at,status',
            'task.user:id,name,email'
        ]);

        // Get the task and user safely
        $task = $recurringTask->task;
        $user = $task?->user;

        // Pass all necessary data to the view
        return view('recurring_tasks.show', compact('recurringTask', 'task', 'user'));
    }



    // public function edit(RecurringTask $recurringTask)
    // {
    //     // Eager load task and user to avoid extra queries
    //     $recurringTask->load('task.user');

    //     $task = $recurringTask->task;
    //     $user = $task?->user;

    //     // Convert task start/end times to Carbon instances if they exist
    //     $task->start_time = $task->start_time ? Carbon::parse($task->start_time) : null;
    //     $task->end_time   = $task->end_time ? Carbon::parse($task->end_time) : null;

    //     // Format recurring task times and dates safely
    //     $recurringTask->daily_time   = $recurringTask->daily_time ? Carbon::parse($recurringTask->daily_time)->format('H:i') : null;
    //     $recurringTask->weekly_time  = $recurringTask->weekly_time ? Carbon::parse($recurringTask->weekly_time)->format('H:i') : null;
    //     $recurringTask->monthly_time = $recurringTask->monthly_time ? Carbon::parse($recurringTask->monthly_time)->format('H:i') : null;
    //     $recurringTask->monthly_date = $recurringTask->monthly_date ? Carbon::parse($recurringTask->monthly_date)->format('d/m/Y') : null;

    //     // Decode weekly days safely
    //     $recurringTask->week_days = $recurringTask->week_days ? json_decode($recurringTask->week_days, true) : [];

    //     // Fetch users and tasks for select dropdowns
    //     $users = User::select('id', 'name')->get();
    //     $tasks = Task::select('id', 'title')->get();

    //     return view('recurring_tasks.edit', compact('task', 'user', 'recurringTask', 'users', 'tasks'));
    // }


    // public function update(Request $request, RecurringTask $recurringTask)
    // {
    //     $task = $recurringTask->task;

    //     $request->validate([
    //         'title'         => 'required|string|max:255',
    //         'description'   => 'nullable|string',
    //         'user_id'       => 'required|exists:users,id',
    //         'recurring'     => 'nullable|in:daily,weekly,monthly',
    //         'daily_time'    => 'required_if:recurring,daily',
    //         'weekly_time'   => 'required_if:recurring,weekly',
    //         'week_days'     => 'required_if:recurring,weekly|array',
    //         'monthly_date'  => 'required_if:recurring,monthly',
    //         'monthly_time'  => 'required_if:recurring,monthly',
    //         'start_date'    => 'required_without:recurring',
    //         'start_time'    => 'required_without:recurring',
    //     ]);

    //     try {
    //         // Update main task
    //         $task->update([
    //             'title'       => $request->title,
    //             'description' => $request->description,
    //             'user_id'     => $request->user_id,
    //         ]);

    //         if (!$request->recurring && $request->start_date) {
    //             $task->start_time = Carbon::createFromFormat('d/m/Y H:i', $request->start_date . ' ' . $request->start_time);
    //             if ($request->end_date && $request->end_time) {
    //                 $task->end_time = Carbon::createFromFormat('d/m/Y H:i', $request->end_date . ' ' . $request->end_time);
    //             }
    //             $task->save();
    //         }

    //         // Update recurring task details
    //         $recurringTask->update([
    //             'repeat_type'  => $request->recurring,
    //             'daily_time'   => $request->daily_time ?? null,
    //             'weekly_time'  => $request->weekly_time ?? null,
    //             'monthly_time' => $request->monthly_time ?? null,
    //             'monthly_date' => $request->monthly_date ?? null,
    //             'week_days'    => $request->week_days ? json_encode($request->week_days) : null,
    //         ]);

    //         return redirect()->route('recurring-tasks.index')->with('success', 'Task updated successfully');
    //     } catch (\Throwable $e) {
    //         Log::error('Recurring task update failed: ' . $e->getMessage());
    //         return back()->withErrors(['error' => 'Update failed. Check date/time formats.'])->withInput();
    //     }
    // }
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'task_id'      => 'required|exists:tasks,id',
    //         'repeat_type'  => 'required|in:daily,weekly,monthly',
    //         'daily_time'   => 'required_if:repeat_type,daily',
    //         'weekly_time'  => 'required_if:repeat_type,weekly',
    //         'week_days'    => 'required_if:repeat_type,weekly|array',
    //         'monthly_date' => 'required_if:repeat_type,monthly',
    //         'monthly_time' => 'required_if:repeat_type,monthly',
    //     ]);

    //     try {
    //         // Only take necessary fields
    //         $data = $request->only([
    //             'task_id',
    //             'repeat_type',
    //             'daily_time',
    //             'weekly_time',
    //             'week_days',
    //             'monthly_date',
    //             'monthly_time',
    //             'monthly_day',
    //             'start_date',
    //             'end_date'
    //         ]);

    //         // Map week_days integers to string names
    //         if (!empty($data['week_days'])) {
    //             $daysMap = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
    //             $data['week_days'] = array_map(fn($day) => $daysMap[(int)$day] ?? $day, $data['week_days']);
    //         }

    //         // Create recurring task
    //         $recurring = RecurringTask::create($data);

    //         // Dispatch job to generate the task asynchronously
    //         ProcessRecurringTask::dispatch($recurring, now()->toDateTimeString());

    //         return redirect()->route('recurring-tasks.index')
    //             ->with('success', 'Recurring Task created and queued for processing.');
    //     } catch (\Throwable $e) {
    //         Log::error('Recurring Task creation failed: ' . $e->getMessage());
    //         return back()->withErrors(['error' => 'Creation failed.'])->withInput();
    //     }
    // }

    public function store(Request $request)
    {
        $request->validate([
            'task_id'      => 'required|exists:tasks,id',
            'repeat_type'  => 'required|in:daily,weekly,monthly',
            'daily_time'   => 'required_if:repeat_type,daily',
            'weekly_time'  => 'required_if:repeat_type,weekly',
            'week_days'    => 'required_if:repeat_type,weekly|array',
            'monthly_date' => 'required_if:repeat_type,monthly',
            'monthly_time' => 'required_if:repeat_type,monthly',
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
                'monthly_day',
                'start_date',
                'end_date'
            ]);

            if (!empty($data['week_days'])) {
                $daysMap = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
                $data['week_days'] = array_map(fn($day) => $daysMap[(int)$day] ?? $day, $data['week_days']);
            }

            // Create recurring task — next_run_at is automatically set in the model
            $recurring = RecurringTask::create($data);

            //  Dispatch the first job
            ProcessRecurringTask::dispatch($recurring, $recurring->next_run_at);

            return redirect()->route('recurring-tasks.index')
                ->with('success', 'Recurring Task created and queued for processing.');
        } catch (\Throwable $e) {
            Log::error('Recurring Task creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Creation failed.'])->withInput();
        }
    }


    public function datatable(Request $request)
    {
        // Use query builder to select only required columns
        $query = RecurringTask::query()
            ->select('recurring_tasks.id', 'recurring_tasks.task_id', 'recurring_tasks.repeat_type', 'recurring_tasks.next_run_at')
            ->with([
                'task' => fn($q) => $q->select('id', 'title', 'description', 'user_id')
                    ->with(['user' => fn($q2) => $q2->select('id', 'name')])
            ]);

        // Pass query builder directly to DataTables (server-side)
        return DataTables::eloquent($query)
            ->addColumn('title', fn($rec) => $rec->task->title ?? '-')
            ->addColumn('description', fn($rec) => $rec->task->description ?? '-')
            ->addColumn('user', fn($rec) => $rec->task->user->name ?? '-')
            ->addColumn('action', function ($rec) {
                $view = route('recurring-tasks.show', $rec->id);
                $delete = route('recurring-tasks.destroy', $rec->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return <<<HTML
            <div class="dropdown">
                <button class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{$view}"><i class="bi bi-eye"></i> View</a></li>
                    <li>
                        <form action="{$delete}" method="POST">
                            {$csrf}{$method}
                            <button class="dropdown-item text-danger"><i class="bi bi-trash"></i> Delete</button>
                        </form>
                    </li>
                </ul>
            </div>
            HTML;
            })
            ->rawColumns(['action'])
            ->filterColumn('title', function ($query, $keyword) {
                $query->whereHas('task', fn($q) => $q->where('title', 'like', "%{$keyword}%"));
            })
            ->filterColumn('user', function ($query, $keyword) {
                $query->whereHas('task.user', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
            })
            ->orderColumn('user', function ($query, $order) {
                $query->join('tasks', 'tasks.id', '=', 'recurring_tasks.task_id')
                    ->join('users', 'users.id', '=', 'tasks.user_id')
                    ->orderBy("users.name", $order)
                    ->select('recurring_tasks.*');
            })
            ->make(true);
    }
    public function destroy(RecurringTask $recurringTask)
    {
        DB::transaction(function () use ($recurringTask) {
            // Delete the related task if exists
            if ($recurringTask->task) {
                $recurringTask->task()->delete();
            }

            // Delete the recurring rule
            $recurringTask->delete();
        });

        return redirect()->route('recurring-tasks.index')
            ->with('success', 'Recurring task and its associated task deleted successfully.');
    }
}
