@extends('layout.app')
@section('title', 'Add Task')

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

                            <!-- Title -->
                            <div class="form-floating mb-3">
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}"
                                    required>
                                <label>Task Title *</label>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="form-floating mb-3">
                                <textarea name="description" class="form-control" style="height:100px">{{ old('description') }}</textarea>
                                <label>Description</label>
                            </div>

                            <!-- Assign User -->
                            <div class="form-floating mb-3">
                                <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">Select User</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Assign To *</label>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Recurring -->
                            <div class="form-floating mb-3">
                                <select name="recurring" id="recurring" class="form-select">
                                    <option value="">None</option>
                                    <option value="daily" {{ old('recurring') == 'daily' ? 'selected' : '' }}>Daily
                                    </option>
                                    <option value="weekly" {{ old('recurring') == 'weekly' ? 'selected' : '' }}>Weekly
                                    </option>
                                    <option value="monthly" {{ old('recurring') == 'monthly' ? 'selected' : '' }}>Monthly
                                    </option>
                                </select>
                                <label>Recurring</label>
                            </div>
                            <!-- Recurring End Date -->



                            <!-- One-time fields -->
                            <div id="oneTimeFields">
                                <div class="form-floating mb-3">
                                    <input type="text" name="start_date" id="start_date" class="form-control"
                                        value="{{ old('start_date') }}" placeholder="dd/mm/yyyy">
                                    <label>Start Date</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="time" name="start_time" class="form-control"
                                        value="{{ old('start_time') }}">
                                    <label>Start Time</label>
                                </div>
                            </div>

                            <!-- Daily -->
                            <div id="daily_input" class="mb-3" style="display:none;">
                                <label>Time</label>
                                <input type="time" name="daily_time" class="form-control"
                                    value="{{ old('daily_time') }}">
                            </div>

                            <!-- Weekly -->
                            <div id="weekly_input" class="mb-3" style="display:none;">
                                <label>Weekday(s)</label>
                                <div class="d-flex gap-2 flex-wrap mb-2">
                                    @php $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']; @endphp
                                    @foreach ($days as $day)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="week_days[]"
                                                value="{{ $day }}" id="day{{ $day }}"
                                                {{ is_array(old('week_days')) && in_array($day, old('week_days')) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="day{{ $day }}">{{ $day }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <label>Time</label>
                                <input type="time" name="weekly_time" class="form-control"
                                    value="{{ old('weekly_time') }}">
                            </div>

                            <!-- Monthly -->
                            <div id="monthly_input" class="mb-3" style="display:none;">
                                <label>Date</label>
                                <input type="text" name="monthly_date" id="monthly_date" class="form-control"
                                    placeholder="dd/mm/yyyy" value="{{ old('monthly_date') }}">
                                <label class="mt-2">Time</label>
                                <input type="time" name="monthly_time" class="form-control"
                                    value="{{ old('monthly_time') }}">
                            </div>

                            <div id="recurring_end" class="form-floating mb-3">
                                <input type="text" name="recurring_end_date" id="end_date" class="form-control"
                                    placeholder="dd/mm/yyyy" value="{{ old('recurring_end_date') }}">
                                <label>End Date</label>
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
    <script src="{{ asset('js/app.js') }}"></script>
@endsection
