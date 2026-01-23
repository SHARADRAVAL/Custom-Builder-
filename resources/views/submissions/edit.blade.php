@extends('layout.app')

@section('title', 'Edit ' . $form->name)

@section('page-title')
    <div class="card-header">
        
        <h5 class="mb-0 text-dark fw-semibold"><i class="bi bi-pencil-square text-primary me-2"></i>{{ isset($submission) ? 'Edit ' : 'Add ' }}  {{ $form->name }}</h5>
    </div>
@endsection

@section('content')
    <div class="w-100"> <!-- full width container -->

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST"
                    action="{{ isset($submission) ? route('submissions.update', $submission->id) : route('submissions.store') }}">
                    @csrf
                    @if (isset($submission))
                        @method('PUT')
                    @endif

                    <div class="row g-3"> <!-- row with gap between columns -->
                        @foreach ($form->fields as $field)
                            @php
                                $value = old("fields.{$field->id}", $values[$field->id] ?? '');

                                if ($field->type === 'time' && $value) {
                                    try {
                                        $value = \Carbon\Carbon::createFromFormat('H:i:s', $value)->format('g:i A');
                                    } catch (\Exception $e) {
                                        $value = '';
                                    }
                                }
                            @endphp

                            <div class="col-md-6 col-12"> <!-- column -->
                                <div class="form-floating mb-3"> <!-- floating label with bottom margin -->
                                    @switch($field->type)
                                        @case('text')
                                            <input type="text" name="fields[{{ $field->id }}]"
                                                class="form-control @error('fields.' . $field->id) is-invalid @enderror"
                                                id="field{{ $field->id }}" value="{{ $value }}"
                                                {{ $field->is_required ? 'required' : '' }}>
                                        @break

                                        @case('number')
                                            <input type="number" name="fields[{{ $field->id }}]"
                                                class="form-control @error('fields.' . $field->id) is-invalid @enderror"
                                                id="field{{ $field->id }}" value="{{ $value }}"
                                                {{ $field->is_required ? 'required' : '' }}>
                                        @break

                                        @case('textarea')
                                            <textarea name="fields[{{ $field->id }}]"
                                                class="form-control @error('fields.' . $field->id) is-invalid @enderror"
                                                id="field{{ $field->id }}" style="height:100px;"
                                                {{ $field->is_required ? 'required' : '' }}>{{ $value }}</textarea>
                                        @break

                                        @case('date')
                                            <input type="text" name="fields[{{ $field->id }}]"
                                                class="form-control datepicker @error('fields.' . $field->id) is-invalid @enderror"
                                                id="field{{ $field->id }}" placeholder="DD/MM/YYYY"
                                                value="{{ $value }}" data-default-date="{{ $value }}"
                                                {{ $field->is_required ? 'required' : '' }}>
                                        @break

                                        @case('time')
                                            <input type="text" name="fields[{{ $field->id }}]"
                                                class="form-control timepicker @error('fields.' . $field->id) is-invalid @enderror"
                                                id="field{{ $field->id }}" placeholder="hh:mm AM/PM"
                                                value="{{ $value }}" autocomplete="off"
                                                {{ $field->is_required ? 'required' : '' }}>
                                        @break

                                        @default
                                            <input type="text" name="fields[{{ $field->id }}]"
                                                class="form-control @error('fields.' . $field->id) is-invalid @enderror"
                                                id="field{{ $field->id }}" value="{{ $value }}"
                                                {{ $field->is_required ? 'required' : '' }}>
                                    @endswitch

                                    <label for="field{{ $field->id }}">
                                        {{ $field->label }}
                                        @if ($field->is_required)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>

                                    @error("fields.{$field->id}")
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('forms.submissions', $form->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>

                        <button type="submit" class="btn btn-primary px-4">
                            {{ isset($submission) ? 'Update Details' : 'Save Details' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
