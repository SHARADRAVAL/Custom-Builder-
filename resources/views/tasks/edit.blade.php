@extends('layout.app')

@section('title', 'Edit Task')

@section('page-title')
    <div class="card-header">
        <h5 class="mb-0 text-dark fw-semibold">
            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Task
        </h5>
    </div>
@endsection

@section('nav-button')
    <div class="d-flex align-items-center gap-1">
        <button type="button" class="btn btn-primary " data-bs-toggle="modal" data-bs-target="#commentFeedbackModal">
            <i class="bi bi-chat-left-text me-1"></i> Complate
        </button>

        {{-- Start Task Button --}}
        @if ($task->status === 'pending')
            <button type="button" id="startTaskBtn" class="btn btn-primary">
                <i class="bi bi-play-circle me-1"></i> Start Task
            </button>
        @else
            <span class="badge bg-secondary text-capitalize p-2">{{ $task->status }}</span>
        @endif

    </div>
@endsection

@section('content')
    <div class="w-100">
        {{-- Header Navigation & Actions --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <ul class="nav nav-tabs" id="taskTab">
                <li class="nav-item">
                    <a class="nav-link active" id="details-tab" data-bs-toggle="tab" href="#details">Details</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="notes-tab" data-bs-toggle="tab" href="#notes">Notes</a>
                </li>
            </ul>

            <div class="d-flex align-items-center">
                {{-- Add Note Button (Visible only on Notes Tab via JS or CSS) --}}
                <button id="addNoteBtn" class="btn btn-sm btn-primary me-2 d-none" data-bs-toggle="modal"
                    data-bs-target="#addNoteModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Note
                </button>

                {{-- Quick AJAX Comment Button --}}

            </div>
        </div>

        <div class="tab-content">

            {{-- DETAILS TAB --}}
            <div class="tab-pane fade show active" id="details">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form action="{{ route('tasks.update', $task) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                {{-- Task Title --}}
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="title" id="main_title"
                                            class="form-control @error('title') is-invalid @enderror"
                                            value="{{ old('title', $task->title) }}" required>
                                        <label>Task Title <span class="text-danger">*</span></label>
                                    </div>
                                </div>

                                {{-- Description --}}
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <textarea name="description" class="form-control" style="height: 100px">{{ old('description', $task->description) }}</textarea>
                                        <label>Description</label>
                                    </div>
                                </div>

                                {{-- Start Date & Time --}}
                                <div class="col-md-3">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="start_date" class="form-control datepicker"
                                            value="{{ old('start_date', $task->start_time ? \Carbon\Carbon::parse($task->start_time)->format('d/m/Y') : '') }}"
                                            required>
                                        <label>Start Date *</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-floating mb-3">
                                        <input type="time" name="start_time" class="form-control"
                                            value="{{ old('start_time', $task->start_time ? \Carbon\Carbon::parse($task->start_time)->format('H:i') : '') }}"
                                            required>
                                        <label>Start Time *</label>
                                    </div>
                                </div>

                                {{-- End Date & Time --}}
                                <div class="col-md-3">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="end_date" class="form-control datepicker"
                                            value="{{ old('end_date', $task->end_time ? \Carbon\Carbon::parse($task->end_time)->format('d/m/Y') : '') }}">
                                        <label>End Date</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-floating mb-3">
                                        <input type="time" name="end_time" class="form-control"
                                            value="{{ old('end_time', $task->end_time ? \Carbon\Carbon::parse($task->end_time)->format('H:i') : '') }}">
                                        <label>End Time</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <hr class="text-muted">
                                </div>

                                {{-- FEEDBACK SECTION (Read-Only on Main Form) --}}
                                {{-- Feedback Column --}}
                                <div class="col-md-4">
                                    <div class="form-floating mb-3">
                                        <select id="main_feedback_select" class="form-select bg-light" disabled>
                                            {{-- If feedback is null, this acts as the "Not Available" label --}}
                                            <option value="">
                                                {{ $task->feedback ? 'Select Feedback' : 'Not Available' }}</option>
                                            @foreach (['Excellent', 'Good', 'Average', 'Poor'] as $option)
                                                <option value="{{ $option }}"
                                                    {{ $task->feedback == $option ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label>Feedback</label>
                                    </div>
                                </div>

                                {{-- Comment Column --}}
                                <div class="col-md-8">
                                    <div class="form-floating mb-3">
                                        <textarea id="main_comment_textarea" class="form-control bg-light" style="height: 58px" readonly>{{ $task->comment ?? 'Not Available' }}</textarea>
                                        <label>Comment</label>
                                    </div>
                                </div>

                                {{-- Assign Users --}}
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold mb-1">Assign To <span
                                                class="text-danger">*</span></label>
                                        <select name="user_ids[]" id="user-select-edit"
                                            class="form-control @error('user_ids') is-invalid @enderror" multiple required>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ $task->users->contains($user->id) ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-end gap-2">
                                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary px-4">Back</a>
                                <button type="submit" class="btn btn-primary px-4">Update Task</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- NOTES TAB --}}
            <div class="tab-pane fade" id="notes">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div id="notesList" class="table-responsive">
                            {{-- Notes will be loaded here via JS --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: Quick Comment & Feedback (AJAX) --}}
    <div class="modal fade" id="commentFeedbackModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="commentFeedbackForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Quick Update</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Feedback <span class="text-danger">*</span></label>
                            <select id="modal_feedback" name="feedback" class="form-control" required>
                                <option value="">Select Feedback</option>
                                <option value="Excellent" {{ $task->feedback == 'Excellent' ? 'selected' : '' }}>Excellent
                                </option>
                                <option value="Good" {{ $task->feedback == 'Good' ? 'selected' : '' }}>Good</option>
                                <option value="Average" {{ $task->feedback == 'Average' ? 'selected' : '' }}>Average
                                </option>
                                <option value="Poor" {{ $task->feedback == 'Poor' ? 'selected' : '' }}>Poor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comment <span class="text-danger">*</span></label>
                            <textarea id="modal_comment" name="comment" class="form-control" rows="4" required>{{ $task->comment }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Add Note --}}
    <div class="modal fade" id="addNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="noteForm" action="{{ route('notes.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Note</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Note Content <span class="text-danger">*</span></label>
                            <textarea name="note" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Note</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Global Data for external JS
        window.taskAppData = {
            taskId: "{{ $task->id }}",
            commentFeedbackUrl: "{{ route('tasks.comment_feedback', $task->id) }}",
            csrfToken: "{{ csrf_token() }}"
        };
    </script>

    {{-- Load external JS --}}
    <script src="{{ asset('js/comment_feed.js') }}"></script>
@endsection
