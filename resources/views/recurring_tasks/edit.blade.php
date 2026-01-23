@extends('layout.app')

@section('title', 'Edit Recurring Task')

@section('page-title')
    <div class="card-header">
        <h5 class="mb-0 text-dark fw-semibold">
            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Recurring Task
        </h5>
    </div>
@endsection

@section('content')
<div class="w-100">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('recurring-tasks.update', $rec->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    {{-- Task Title --}}
                    <div class="col-12">
                        <div class="form-floating mb-3">
                            <input type="text" name="title"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $task->title) }}" required>
                            <label>Task Title <span class="text-danger">*</span></label>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="col-12">
                        <div class="form-floating mb-3">
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" style="height:100px">{{ old('description', $task->description) }}</textarea>
                            <label>Description</label>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Assign User --}}
                    <div class="col-md-6 col-12">
                        <div class="form-floating mb-3">
                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                @foreach ($users as $userItem)
                                    <option value="{{ $userItem->id }}"
                                        {{ old('user_id', $task->user_id) == $userItem->id ? 'selected' : '' }}>
                                        {{ $userItem->name }}
                                    </option>
                                @endforeach
                            </select>
                            <label>Assign To <span class="text-danger">*</span></label>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Recurring Type --}}
                    <div class="col-md-6 col-12">
                        <div class="form-floating mb-3">
                            <select name="recurring" id="recurring" class="form-select">
                                <option value="">None</option>
                                <option value="daily" {{ old('recurring', $rec->repeat_type) == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ old('recurring', $rec->repeat_type) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ old('recurring', $rec->repeat_type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                            <label>Recurring</label>
                        </div>
                    </div>

                    {{-- Daily Time --}}
                    <div class="col-md-6 col-12" id="daily_input" style="display:none;">
                        <div class="form-floating mb-3">
                            <input type="time" name="daily_time" class="form-control"
                                value="{{ old('daily_time', $rec->daily_time) }}">
                            <label>Daily Time</label>
                        </div>
                    </div>

                    {{-- Weekly --}}
                    <div class="col-md-6 col-12" id="weekly_input" style="display:none;">
                        <div class="mb-2">
                            <label>Weekdays</label>
                            <div class="d-flex gap-2 flex-wrap mt-1">
                                @php
                                    $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                                    $selectedDays = old('week_days', $rec->week_days ?? []);
                                @endphp
                                @foreach ($days as $day)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="week_days[]" value="{{ $day }}"
                                            id="day{{ $day }}" {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="day{{ $day }}">{{ $day }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="time" name="weekly_time" class="form-control"
                                value="{{ old('weekly_time', $rec->weekly_time) }}">
                            <label>Weekly Time</label>
                        </div>
                    </div>

                    {{-- Monthly --}}
                    <div class="col-md-6 col-12" id="monthly_input" style="display:none;">
                        <div class="form-floating mb-3">
                            <input type="text" name="monthly_date" class="form-control datepicker"
                                value="{{ old('monthly_date', $rec->monthly_date) }}" placeholder="dd/mm/yyyy">
                            <label>Monthly Date</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="time" name="monthly_time" class="form-control"
                                value="{{ old('monthly_time', $rec->monthly_time) }}">
                            <label>Monthly Time</label>
                        </div>
                    </div>

                </div>

                {{-- Buttons --}}
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('recurring-tasks.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary px-4">Update Task</button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function toggleFields() {
            let type = $('#recurring').val();
            $('#daily_input, #weekly_input, #monthly_input').hide();

            if(type === 'daily') {
                $('#daily_input').show();
            } else if(type === 'weekly') {
                $('#weekly_input').show();
            } else if(type === 'monthly') {
                $('#monthly_input').show();
            }
        }
        toggleFields();
        $('#recurring').change(toggleFields);

        flatpickr('.datepicker', {
            dateFormat: "d/m/Y",
            allowInput: true
        });
    });
</script>
@endpush
