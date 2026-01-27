@extends('layout.app')

@section('title','Tasks List')

@section('page-title')
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold text-dark">
            <i class="bi bi-list-task text-primary me-2"></i>Tasks List
        </h5>
    </div>
@endsection

@section('nav-button')
    <div id="searchDiv" style="display:none; max-width: 300px;">
        <input type="text" id="taskSearch" class="form-control form-control-sm" placeholder="Search Tasks...">
    </div>

    <div class="d-flex align-items-center gap-2">
        <button id="toggleSearch" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-search"></i>
        </button>

        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Task
        </a>
    </div>
@endsection

@section('content')
<div class="card shadow-sm rounded-3">
    <div class="card-body table-responsive">
        <table id="TaskTable"
               class="table table-striped custom-light table-hover align-middle w-100"
               data-url="{{ route('tasks.datatable') }}">
            <thead class="table-light text-center">
                <tr>
                    <th style="width:8%;">ID</th>
                    <th style="width:20%;">Title</th>
                    <th style="width:27%;">Description</th>
                    <th style="width:15%;">Assigned To</th>
                    <th style="width:10%;">Due</th> {{-- 🔥 Shows "3 Days" logic --}}
                    <th style="width:10%;">Status</th>
                    <th style="width:10%;">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>  window.deleteTaskUrl = "{{ route('tasks.delete', ['task' => 0]) }}";</script>
    <script src="{{ asset('js/taskDatatable.js') }}"></script>
@endsection