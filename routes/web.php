<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\FormController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\RecurringTaskController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\noteController;

use Illuminate\Support\Facades\File;

Route::get('/admin/schedule-log', function () {
    $path = storage_path('logs/schedule.log');

    if (!File::exists($path)) {
        return "Log file not found yet. The scheduler hasn't run successfully.";
    }

    $content = File::get($path);

    // We use <pre> to keep the terminal formatting
    return "<body style='background:#1a202c; color:#cbd5e0; padding:20px; font-family:monospace;'>
                <h2>Automation Status Log</h2>
                <hr>
                <pre>{$content}</pre>
            </body>";
})->name('schedule.log');

/* ---------------- AUTH ---------------- */

Route::get('/', [RegisterController::class, 'ShowLoginForm'])->name('login.form');
Route::post('/login/perform', [RegisterController::class, 'login'])->name('login.perform');
Route::get('/register', [RegisterController::class, 'index'])->name('register');
Route::post('/register/store', [RegisterController::class, 'create'])->name('register.create');
Route::get('/logout', [RegisterController::class, 'logout'])->name('logout');

/* ---------------- DASHBOARD ---------------- */

Route::get('/dashboard', [FormController::class, 'dashboard'])->name('dashboard');

/* ---------------- FORMS ---------------- */

Route::controller(FormController::class)->group(function () {

    Route::get('/forms', 'index')->name('forms.index');
    Route::get('/forms/datatable', 'formsDatatable')->name('forms.datatable');

    Route::get('/forms/create', 'create')->name('forms.create');
    Route::post('/forms', 'store')->name('forms.store');
    Route::get('/forms/{id}/edit', 'edit')->name('forms.edit');
    Route::put('/forms/{id}', 'update')->name('forms.update');
    Route::delete('/forms/{id}', 'destroy')->name('forms.destroy');

    Route::post('/forms/{formId}/fields', 'addField')->name('fields.store');
    Route::put('/fields/{id}', 'updateField')->name('fields.update');
    Route::delete('/fields/{id}', 'deleteField')->name('fields.destroy');

    Route::get('/forms/{formId}/submissions', 'submissions')->name('forms.submissions');
    Route::get('/forms/{formId}/submissions/create', 'createSubmission')->name('submissions.create');
    Route::post('/forms/{formId}/submissions', 'storeSubmission')->name('submissions.store');

    Route::get('/submissions/{id}/edit', 'editSubmission')->name('submissions.edit');
    Route::put('/submissions/{id}', 'updateSubmission')->name('submissions.update');
    Route::delete('/submissions/{id}', 'deleteSubmission')->name('submissions.destroy');
    Route::get('/submissions/{id}', 'showSubmission')->name('submissions.show');

    Route::get(
        '/forms/{form}/submissions/datatable',
        [FormController::class, 'submissionsDatatable']
    )->name('forms.submissions.datatable');
});

// routes/web.php
Route::get('/users/search', function (\Illuminate\Http\Request $request) {
    return \App\Models\User::where('name', 'like', '%' . $request->q . '%')
        ->select('id', 'name')
        ->limit(20)
        ->get();
});

/* ---------------- TASKS ---------------- */

Route::get('tasks/datatable', [TaskController::class, 'datatable'])->name('tasks.datatable');
Route::resource('tasks', TaskController::class);
Route::post('tasks/{task}/start', [TaskController::class, 'start'])->name('tasks.start');
Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
Route::delete('tasks/{task}/delete', [TaskController::class, 'destroy'])->name('tasks.delete');
// web.php
Route::post('/tasks/{task}/comment-feedback', [TaskController::class, 'commentFeedback'])->name('tasks.comment_feedback');



Route::get('/test-mail', function () {
    $task = \App\Models\Task::first();
    Mail::to('sharadraval101@gmail.com')->send(new \App\Mail\TaskReminderMail($task));
    return "Mail Sent!";
});


Route::post('/tasks/{task}/start', [TaskController::class, 'start'])->name('tasks.start');


// recuring 

Route::prefix('recurring-tasks')->name('recurring-tasks.')->group(function () {

    Route::get('/', [RecurringTaskController::class, 'index'])->name('index');
    Route::get('/create', [RecurringTaskController::class, 'create'])->name('create');
    Route::post('/store', [RecurringTaskController::class, 'store'])->name('store');
    Route::get('/{recurringTask}', [RecurringTaskController::class, 'show'])->name('show');
    Route::get('/{recurringTask}/edit', [RecurringTaskController::class, 'update'])->name('edit');
    Route::put('/{recurringTask}', [RecurringTaskController::class, 'update'])->name('update');
    Route::delete('/{recurringTask}', [RecurringTaskController::class, 'destroy'])->name('destroy');

    Route::get('recurring-tasks/datatable', [RecurringTaskController::class, 'datatable'])
        ->name('recurring-tasks.datatable');
    Route::post('recurring-tasks/{recurring}/start', [RecurringTaskController::class, 'start'])->name('recurring-tasks.start');
    Route::post('recurring-tasks/{recurring}/complete', [RecurringTaskController::class, 'complete'])->name('recurring-tasks.complete');
});

Route::get('/sentry-test', function () {
    throw new Exception('Sentry DSN test working!');
});

Route::get('/tasks/{task}/notes/view', [NoteController::class, 'view'])->name('notes.view');
Route::get('/tasks/{task}/notes/datatable', [NoteController::class, 'datatable'])->name('notes.datatable');

Route::get('/notes/{taskId}', [NoteController::class, 'index'])->name('notes.index');
Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
Route::delete('/notes/{noteId}', [NoteController::class, 'destroy'])->name('notes.destroy');


// Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
// Route::delete('/notes/{id}', [NoteController::class, 'destroy'])->name('notes.destroy');
