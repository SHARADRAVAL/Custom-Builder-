@extends('layout.app')

@section('title', 'Edit Task')

@section('page-title')
    <div class="card-header">
        <h5 class="mb-0 text-dark fw-semibold">
            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Task
        </h5>
    </div>
@endsection

@section('content')
    <div class="w-100">
        {{-- Nav Tabs --}}
        {{-- Nav Tabs + Add Note Button --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <ul class="nav nav-tabs" id="taskTab">
                <li class="nav-item">
                    <a class="nav-link active" id="details-tab" data-bs-toggle="tab" href="#details">Details</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="notes-tab" data-bs-toggle="tab" href="#notes">Notes</a>
                </li>
            </ul>

            <button id="addNoteBtn" class="btn btn-sm btn-primary d-none" data-bs-toggle="modal"
                data-bs-target="#addNoteModal">
                <i class="bi bi-plus-circle me-1"></i> Add Note
            </button>
        </div>

        <div class="tab-content">

            {{-- DETAILS TAB --}}
            <div class="tab-pane fade show active" id="details">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('tasks.update', $task) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="title"
                                            class="form-control @error('title') is-invalid @enderror"
                                            value="{{ old('title', $task->title) }}" required>
                                        <label>Task Title *</label>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <textarea name="description" class="form-control">{{ old('description', $task->description) }}</textarea>
                                        <label>Description</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="start_date" class="form-control datepicker"
                                            value="{{ old('start_date', $task->start_time ? \Carbon\Carbon::parse($task->start_time)->format('d/m/Y') : '') }}">
                                        <label>Start Date *</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="time" name="start_time" class="form-control"
                                            value="{{ old('start_time', $task->start_time ? \Carbon\Carbon::parse($task->start_time)->format('H:i') : '') }}">
                                        <label>Start Time *</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="end_date" class="form-control datepicker"
                                            value="{{ old('end_date', $task->end_time ? \Carbon\Carbon::parse($task->end_time)->format('d/m/Y') : '') }}">
                                        <label>End Date</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="time" name="end_time" class="form-control"
                                            value="{{ old('end_time', $task->end_time ? \Carbon\Carbon::parse($task->end_time)->format('H:i') : '') }}">
                                        <label>End Time</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating">
                                        <select name="user_id" class="form-select" required>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ $task->user_id == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label>Assign To *</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-end gap-2">
                                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Back</a>
                                <button class="btn btn-primary">Update Task</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- NOTES TAB --}}
            <div class="tab-pane fade" id="notes">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        
                        {{-- Placeholder for Notes Table --}}
                        <div id="notesList" class="table-responsive"></div>

                        {{-- Add Note Modal --}}
                        <div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form id="noteForm" action="{{ route('notes.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="task_id" value="{{ $task->id }}">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addNoteModalLabel">Add Note</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="noteDescription" class="form-label">Note</label>
                                                <textarea name="note" id="noteDescription" class="form-control" rows="4" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Add Note</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        flatpickr('.datepicker', {
            dateFormat: "d/m/Y",
            allowInput: true
        });

        let notesTable;

        // Load Notes tab content & initialize DataTable
        function loadNotesTab() {
            $.get("{{ route('notes.view', $task->id) }}", function(html) {
                $('#notesList').html(html);

                const notesTableEl = $('#notesTable');

                if ($.fn.DataTable.isDataTable(notesTableEl)) {
                    notesTableEl.DataTable().destroy();
                }

                // Inject search bar
                if (!$('#notesSearch').length) {
                    const searchHtml = `
                <div class="ms-auto mb-2">
                    <input type="text" id="notesSearch" class="form-control form-control-sm" placeholder="Search notes...">
                </div>
            `;
                    $('#notesHeader').append(searchHtml);
                }

                notesTable = notesTableEl.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('notes.datatable', $task->id) }}",
                    autoWidth: false,
                    responsive: true,
                    columns: [{
                            data: "id",
                            name: "id"
                        },
                        {
                            data: "note",
                            name: "note"
                        },
                        {
                            data: "created_at",
                            name: "created_at"
                        },
                        {
                            data: "action",
                            name: "action",
                            orderable: false,
                            searchable: false
                        },
                    ],
                    order: [
                        [0, "desc"]
                    ],
                    pageLength: 10,
                    lengthChange: true,
                    dom: '<"table-responsive"rt><"d-flex justify-content-between mt-2"<"length-div"l><"pagination-div"p>>i',
                    drawCallback: function() {
                        $(".dropdown-toggle").each(function() {
                            new bootstrap.Dropdown(this);
                        });
                        $(".pagination-div .paginate_button")
                            .addClass("btn btn-sm btn-primary mx-1")
                            .removeClass("paginate_button");
                    },
                });

                // Custom search input
                $('#notesSearch').off('keyup').on('keyup', function() {
                    notesTable.search(this.value).draw();
                });

            }).fail(function() {
                console.error("Failed to load notes view");
            });
        }

        // Load Notes tab when activated
        document.addEventListener('DOMContentLoaded', function() {
            var notesTab = document.querySelector('#notes-tab');
            notesTab.addEventListener('shown.bs.tab', function() {
                loadNotesTab();
            });
        });

        // Add Note via AJAX
        $(document).on('submit', '#noteForm', function(e) {
            e.preventDefault();
            $.post("{{ route('notes.store') }}", $(this).serialize(), function() {
                $('#addNoteModal').modal('hide');
                $('#noteForm')[0].reset();
                loadNotesTab();
            });
        });

        // Delete Note via AJAX
        $(document).on('click', '.deleteNote', function() {
            let button = $(this);
            let id = button.data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/notes/' + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            // Remove the row smoothly
                            let row = button.closest('tr');
                            row.fadeOut(300, function() {
                                $(this).remove();
                            });

                            // SweetAlert success message
                            Swal.fire(
                                'Deleted!',
                                'Your note has been deleted.',
                                'success'
                            );
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'Failed to delete note. Try again.',
                                'error'
                            );
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const addBtn = document.getElementById('addNoteBtn');

            document.getElementById('notes-tab').addEventListener('shown.bs.tab', function() {
                addBtn.classList.remove('d-none'); // show on Notes
            });

            document.getElementById('details-tab').addEventListener('shown.bs.tab', function() {
                addBtn.classList.add('d-none'); // hide on Details
            });
        });
    </script>
@endsection
