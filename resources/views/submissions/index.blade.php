@extends('layout.app')

@section('title', 'View ' . $form->name)

@php
    use Carbon\Carbon;
@endphp

@section('page-title')
    <div class="d-flex justify-content-between align-items-center w-100 px-3 py-2">
        <h5 class="mb-0 fw-semibold text-dark">
            <i class="bi bi-inbox text-primary me-2"></i>{{ $form->name }}
        </h5>
    </div>
@endsection

@section('nav-button')
    <div id="searchDiv" class="d-inline-block me-2" style="display:none; max-width: 300px;">
        <input type="text" id="formSearch" class="form-control form-control-sm" placeholder="Search submissions...">
    </div>

    <div class="d-inline-flex align-items-center gap-2">
        <button id="toggleSearch" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-search"></i>
        </button>

        <a href="{{ route('submissions.create', $form->id) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add {{ $form->name }}
        </a>
    </div>
@endsection

@section('content')
    <div class="w-100 px-0">
        <div class="card shadow-sm border-0 rounded-0">
            {{-- Card Header --}}
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center px-3 py-2">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-ul me-2 text-primary"></i> {{ $form->name }} List
                </h6>
                <span class="badge bg-primary-subtle text-primary px-3 py-2">Form Id : {{ $form->id }}</span>
            </div>

            {{-- Card Body / Table --}}
            <div class="card-body p-0">
                <div class="table-responsive w-100">
                    <table id="SubmissionsTable" class="table table-hover align-middle mb-0 w-100">
                        <thead class="table-light text-uppercase small">
                            <tr>
                                <th class="px-4">ID</th>
                                <th>Submitted On</th>
                                @foreach ($form->fields as $field)
                                    <th class="px-3">{{ $field->label }}</th>
                                @endforeach
                                <th class="text-center px-4">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            const table = $('#SubmissionsTable').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: "{{ route('forms.submissions.datatable', $form->id) }}",
                pageLength: 10,
                order: [
                    [0, 'desc']
                ],
                responsive: true,
                lengthChange: true,
                dom: '<"table-responsive"t><"d-flex justify-content-between align-items-center px-3 py-3"<"d-flex align-items-center gap-3"l><"d-flex ms-auto"p>>',
                columns: [{
                        data: 'id',
                        name: 'id',
                        className: 'px-4 fw-semibold'
                    },
                    {
                        data: 'submitted_on',
                        name: 'created_at'
                    },
                    @foreach ($form->fields as $i => $field)
                        {
                            data: 'fields.{{ $i }}',
                            orderable: false,
                            searchable: true
                        },
                    @endforeach {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center px-4'
                    }
                ],
                drawCallback: function() {
                    $('.dropdown-toggle').each(function() {
                        new bootstrap.Dropdown(this);
                    });
                    $('.dataTables_paginate .paginate_button').addClass('btn btn-sm btn-primary mx-1')
                        .removeClass('paginate_button');
                }
            });

            // Toggle search
            $('#toggleSearch').click(function() {
                $('#searchDiv').slideToggle(200, function() {
                    if ($(this).is(':visible')) $('#formSearch').focus();
                });
            });
            $('#formSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            // AJAX Delete
            $('#SubmissionsTable').on('submit', 'form.delete-submission', function(e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to delete this submission?')) return;
                const url = $(this).attr('action');
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(resp) {
                        table.ajax.reload(null, false);
                    },
                    error: function() {
                        alert('Delete failed!');
                    }
                });
            });

        });
    </script>
@endsection
