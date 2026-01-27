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

        $task->start_time = $task->start_time ? Carbon::parse($task->start_time) : null;
        $task->end_time   = $task->end_time ? Carbon::parse($task->end_time) : null;

        $rec->daily_time   = $rec->daily_time ? Carbon::parse($rec->daily_time)->format('H:i') : null;
        $rec->weekly_time  = $rec->weekly_time ? Carbon::parse($rec->weekly_time)->format('H:i') : null;
        $rec->monthly_time = $rec->monthly_time ? Carbon::parse($rec->monthly_time)->format('H:i') : null;
        $rec->monthly_date = $rec->monthly_date ? Carbon::parse($rec->monthly_date)->format('d/m/Y') : null;

        $rec->week_days = $rec->week_days ? json_decode($rec->week_days) : [];

        return view('recurring_tasks.edit', [
            'task'  => $task,
            'user'  => $user,
            'rec'   => $rec,
            'users' => User::all(),
            'tasks' => Task::all(),
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
            'daily_time'    => 'required_if:recurring,daily',
            'weekly_time'   => 'required_if:recurring,weekly',
            'week_days'     => 'required_if:recurring,weekly|array',
            'monthly_date'  => 'required_if:recurring,monthly',
            'monthly_time'  => 'required_if:recurring,monthly',
            'start_date'    => 'required_without:recurring',
            'start_time'    => 'required_without:recurring',
        ]);

        try {
            // Update main task
            $task->update([
                'title'       => $request->title,
                'description' => $request->description,
                'user_id'     => $request->user_id,
            ]);

            if (!$request->recurring && $request->start_date) {
                $task->start_time = Carbon::createFromFormat('d/m/Y H:i', $request->start_date . ' ' . $request->start_time);
                if ($request->end_date && $request->end_time) {
                    $task->end_time = Carbon::createFromFormat('d/m/Y H:i', $request->end_date . ' ' . $request->end_time);
                }
                $task->save();
            }

            // Update recurring task details
            $recurringTask->update([
                'repeat_type'  => $request->recurring,
                'daily_time'   => $request->daily_time ?? null,
                'weekly_time'  => $request->weekly_time ?? null,
                'monthly_time' => $request->monthly_time ?? null,
                'monthly_date' => $request->monthly_date ?? null,
                'week_days'    => $request->week_days ? json_encode($request->week_days) : null,
            ]);

            return redirect()->route('recurring-tasks.index')->with('success', 'Task updated successfully');
        } catch (\Throwable $e) {
            Log::error('Recurring task update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Update failed. Check date/time formats.'])->withInput();
        }
    }

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
            $data = $request->all();
            
            if (!empty($data['week_days'])) {
                $daysMap = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
                $data['week_days'] = array_map(fn($day) => $daysMap[(int)$day] ?? $day, $data['week_days']);
            }

            $recurring = RecurringTask::create($data);
            $this->generateRecurringTask($recurring);

            return redirect()->route('recurring-tasks.index')->with('success', 'Recurring Task Created');
        } catch (\Throwable $e) {
            Log::error('Recurring task failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Creation failed.'])->withInput();
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

    private function createDaily($recurring, $task) { /* Your existing createDaily logic */ }
    private function createWeekly($recurring, $task) { /* Your existing createWeekly logic */ }
    private function createMonthly($recurring, $task) { /* Your existing createMonthly logic */ }

    public function datatable(Request $request)
    {
        $tasks = RecurringTask::with('task.user');
        return DataTables::of($tasks)
            ->addColumn('title', fn($rec) => $rec->task->title ?? '-')
            ->addColumn('description', fn($rec) => $rec->task->description ?? '-')
            ->addColumn('user', fn($rec) => $rec->task->user->name ?? '-')
            ->addColumn('action', function ($rec) {
                return '<div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="'.route('recurring-tasks.show', $rec->id).'"><i class="bi bi-eye"></i> View</a></li>
                        <li><form action="'.route('recurring-tasks.destroy', $rec->id).'" method="POST">'.csrf_field().method_field('DELETE').'<button class="dropdown-item text-danger"><i class="bi bi-trash"></i> Delete</button></form></li>
                    </ul></div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function destroy(RecurringTask $recurringTask)
    {
        $task = $recurringTask->task;
        $recurringTask->delete();
        if($task) $task->delete();
        return redirect()->route('recurring-tasks.index')->with('success', 'Task deleted successfully');
    }
}