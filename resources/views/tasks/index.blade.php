{{-- @extends('layout.app')

@section('title', 'Task Manager')
@section('page-title')
    <div class="d-flex justify-content-end align-items-center">
        <h5 class="mb-0 fw-semibold text-dark">
            <i class="bi bi-list-ul text-primary me-2"></i>Task Manager
        </h5>
        <!-- Right side: New Task button -->
        
    </div>
    <!-- Hidden search input -->

@endsection

@section('nav-button')
    <div class="d-flex align-items-center gap-2">
        <!-- New Task Button -->
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Task
        </a>
    </div>
@endsection


@section('content')
<div class="container py-4">
  <table class="table table-hover shadow-sm bg-white">
    <thead class="table-light">
      <tr>
        <th>Task Name</th>
        <th>Description</th>
        <th>User</th>
        <th>Status</th>
        <th>Start</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($tasks as $task)
      <tr>
        <td>{{ $task->title }}</td>
        <td>{{ Str::limit($task->description,50) }}</td>
        <td>{{ $task->user->name }}</td>
        <td>
          <span class="badge bg-{{ $task->status=='completed'?'success':($task->status=='in_progress'?'warning':'secondary') }}">
            {{ ucfirst($task->status) }}
          </span>
        </td>
        <td>{{ $task->start_time->format('d M Y H:i') }}</td>
        <td>
          <a href="{{ route('tasks.show',$task) }}" class="btn btn-sm btn-info">View</a>
          <a href="{{ route('tasks.edit',$task) }}" class="btn btn-sm btn-warning">Edit</a>

          @if($task->status=='pending')
          <form action="{{ route('tasks.start',$task) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-primary">Start</button>
          </form>
          @endif

          @if($task->status=='in_progress')
          <form action="{{ route('tasks.complete',$task) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-success">Complete</button>
          </form>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{ $tasks->links() }}
</div>
@endsection --}}

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
        <!-- Search toggle button -->
        <button id="toggleSearch" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-search"></i>
        </button>

        <!-- New Task Button -->
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Task
        </a>
    </div>
@endsection


@section('content')
    <div class="card shadow-sm rounded-3">
    <div class="card-body table-responsive"> {{-- 🔥 Important --}}
        <table id="TaskTable"
               class="table table-striped custom-light table-hover align-middle w-100 text-wrap"
               data-url="{{ route('tasks.datatable') }}">
            <thead class="table-light text-center">
                <tr >
                    <th style="width:8%; ">ID</th>
                    <th style="width:20%;">Title</th>
                    <th style="width:30%;">Description</th>
                    <th style="width:15%;">Assigned To</th>
                    <th style="width:12%;">Status</th>
                    <th style="width:15%;">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

    </div>
@endsection


@section('scripts')
    <script src="{{ asset('js/taskDatatable.js') }}"></script>
@endsection
