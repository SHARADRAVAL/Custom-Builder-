@extends('layout.app')

@section('title', 'Recurring Tasks List')

@section('page-title')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-semibold text-dark">
            <i class="bi bi-list-task text-primary me-2"></i>Recurring Tasks
        </h5>
    </div>
@endsection

@section('nav-button')
    <div id="searchDiv" style="display:none; max-width: 300px;">
        <input type="text" id="taskSearch" class="form-control form-control-sm" placeholder="Search Tasks...">
    </div>

    <div class="d-flex align-items-center gap-2">
        <!-- Search toggle button -->
        <button id="toggleSearch" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-search"></i>
        </button>

        <!-- New Recurring Task Button -->
        <a href="{{ route('recurring-tasks.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Task
        </a>
    </div>
@endsection

@section('content')
    <div class="card shadow-sm rounded-3">
        <div class="card-body table-responsive">
            <table id="TaskTable" class="table table-striped table-hover align-middle w-100 text-wrap"
                data-url="{{ route('recurring-tasks.recurring-tasks.datatable') }}">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width:5%;">ID</th>
                        <th style="width:25%;">Title</th>
                        <th style="width:25%;">Description</th>
                        <th style="width:15%;">Assigned To</th>
                        <th style="width:20%;">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
   <script src="{{ asset('js/recurringDatatable.js') }}"></script>
@endsection
