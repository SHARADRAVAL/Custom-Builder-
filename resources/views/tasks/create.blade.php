@extends('layout.app')

@section('title','Add Task')

@section('page-title')
<div class="d-flex justify-content-end align-items-center">
    <h5 class="mb-0 fw-semibold text-dark">
        <i class="bi bi-plus text-primary me-2"></i>Add New Task
    </h5>
</div>
@endsection

@section('content')
<div class="w-100">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body p-4">
          <form id="taskForm" action="{{ route('tasks.store') }}" method="POST">
            @csrf

            <div class="form-floating mb-3">
              <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                     value="{{ old('title') }}" required>
              <label>Task Title <span class="text-danger">*</span></label>
              @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-floating mb-3">
              <textarea name="description" class="form-control" style="height:100px">{{ old('description') }}</textarea>
              <label>Description</label>
            </div>

            <div class="row g-4">
              <div class="col-md-6">
                <div class="form-floating">
                  <input type="text" name="start_date" id="start_date"
                         class="form-control @error('start_date') is-invalid @enderror"
                         value="{{ old('start_date') }}" placeholder="dd/mm/yyyy" required>
                  <label>Start Date <span class="text-danger">*</span></label>
                  @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating">
                  <input type="time" name="start_time"
                         class="form-control @error('start_time') is-invalid @enderror"
                         value="{{ old('start_time') }}" required>
                  <label>Start Time <span class="text-danger">*</span></label>
                  @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating">
                  <input type="text" name="end_date" id="end_date"
                         class="form-control @error('end_date') is-invalid @enderror"
                         value="{{ old('end_date') }}" placeholder="dd/mm/yyyy" required>
                  <label>End Date <span class="text-danger">*</span></label>
                  @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating">
                  <input type="time" name="end_time"
                         class="form-control @error('end_time') is-invalid @enderror"
                         value="{{ old('end_time') }}">
                  <label>End Time</label>
                  @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>

            <div class="form-floating mt-3">
              <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                <option value="">Select User</option>
                @foreach($users as $user)
                  <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                  </option>
                @endforeach
              </select>
              <label>Assign To <span class="text-danger">*</span></label>
              @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="text-end mt-4">
              <a href="{{ route('tasks.index') }}" class="btn btn-light">Cancel</a>
              <button class="btn btn-primary">Add Task</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')

    <script src="{{ asset("js/app.js") }}"></script>

@endsection