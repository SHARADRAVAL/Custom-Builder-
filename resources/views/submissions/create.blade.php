@extends('layout.app')

@section('title', 'Add ' . $form->name)

@section('page-title')
    <h5 class="fw-semibold mb-0 text-dark">
        <i class="bi bi-plus-circle text-primary me-2"></i> Add {{ $form->name }}
    </h5>
@endsection

@section('content')
    <div class="container-fluid w-100 px-0">
        <div class="row justify-content-center">
            <div class="col-12"> <!-- full width card -->
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body px-4 py-4 g-4">

                        <form action="{{ route('submissions.store', $form->id) }}" method="POST">
                            @csrf

                            <div class="row g-4">
                                @foreach ($form->fields as $field)
                                    @php
                                        $value = old("fields.{$field->id}", '');
                                        $isTextarea = $field->type === 'textarea';
                                    @endphp

                                    <div class="{{ $isTextarea ? 'col-12' : 'col-md-6 col-12' }}">
                                        <label class="form-label fw-medium">
                                            {{ $field->label }}
                                            @if ($field->is_required)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>

                                        @switch($field->type)
                                            @case('text')
                                                <input type="text" name="fields[{{ $field->id }}]"
                                                    class="form-control @error('fields.' . $field->id) is-invalid @enderror"
                                                    value="{{ $value }}" placeholder="Enter {{ strtolower($field->label) }}">
                                            @break

                                            @case('number')
                                                <input type="number" name="fields[{{ $field->id }}]"
                                                    class="form-control @error('fields.' . $field->id) is-invalid @enderror"
                                                    value="{{ $value }}">
                                            @break

                                            @case('textarea')
                                                <textarea name="fields[{{ $field->id }}]" rows="4"
                                                    class="form-control @error('fields.' . $field->id) is-invalid @enderror"
                                                    placeholder="Enter {{ strtolower($field->label) }}">{{ $value }}</textarea>
                                            @break

                                            @case('date')
                                                <input type="text" name="fields[{{ $field->id }}]"
                                                    class="form-control datepicker @error('fields.' . $field->id) is-invalid @enderror"
                                                    placeholder="DD / MM / YYYY" value="{{ $value }}">
                                            @break

                                            @case('time')
                                                <input type="text" name="fields[{{ $field->id }}]"
                                                    class="form-control timepicker @error('fields.' . $field->id) is-invalid @enderror"
                                                    placeholder="AM / PM" value="{{ $value }}">
                                            @break

                                            @default
                                                <input type="text" name="fields[{{ $field->id }}]"
                                                    class="form-control @error('fields.' . $field->id) is-invalid @enderror"
                                                    value="{{ $value }}">
                                        @endswitch

                                        @error("fields.{$field->id}")
                                            <small class="text-danger d-block mt-1">
                                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                            </small>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <a href="{{ route('forms.submissions', $form->id) }}" class="btn btn-light px-4">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i> Save
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
