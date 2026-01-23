<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Note;
use Yajra\DataTables\DataTables;

class NoteController extends Controller
{

    // Return the view for notes (for AJAX)
    public function view(Task $task)
    {
        return view('notes.list', compact('task'));
    }

    // Return JSON for DataTables
    public function datatable(Task $task)
    {
        $notesQuery = $task->notes(); 
        return DataTables::of($notesQuery)
            ->addColumn('action', function ($note) {
                return '<div class="text-end">
            <button class="btn btn-sm btn-danger deleteNote" data-id="' . $note->id . '" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>';
            })
            ->editColumn('created_at', function ($note) {
                return $note->created_at->format('d/m/Y H:i');
            })
            ->orderColumn('id', function ($query, $order) {
                $query->orderBy('id', $order);
            })
            ->orderColumn('note', function ($query, $order) {
                $query->orderBy('note', $order);
            })
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    // Store note via AJAX
    public function store(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'note' => 'required|string',
        ]);

        $note = new \App\Models\Note();
        $note->task_id = $request->task_id;
        $note->note = $request->note;
        $note->save();

        return response()->json(['success' => true]);
    }

    // Delete note via AJAX
    public function destroy($id)
    {
        $note = Note::findOrFail($id);
        $note->delete();
        return response()->json(['success' => true]);
    }
}
