@extends('layout.app')

@section('content')
    <div class="container">
        <h2>{{ $submission ? 'Edit' : 'Add' }} {{ $form->name }}</h2>

        <form
            action="{{ $submission ? route('submissions.update', $submission->id) : route('submissions.store', $form->id) }}"
            method="POST">

            @csrf
            @if ($submission)
                @method('PUT')
            @endif

            @foreach ($form->fields as $field)
                @php
                    $value = old("fields.{$field->id}", $values[$field->id] ?? '');
                @endphp

                <div class="mb-3">
                    <label class="form-label">{{ $field->label }} @if ($field->is_required)
                            
                        @endif
                    </label>

                    @switch($field->type)
                        @case('text')
                            <input type="text" name="fields[{{ $field->id }}]" class="form-control" value="{{ $value }}">
                        @break

                        @case('number')
                            <input type="number" name="fields[{{ $field->id }}]" class="form-control"
                                value="{{ $value }}">
                        @break

                        @case('textarea')
                            <textarea name="fields[{{ $field->id }}]" class="form-control">{{ $value }}</textarea>
                        @break

                        @case('date')
                            <input type="date" name="fields[{{ $field->id }}]" class="form-control"
                                value="{{ $value }}">
                        @break

                        @case('time')
                            <input type="time" name="fields[{{ $field->id }}]" class="form-control"
                                value="{{ $value }}">
                        @break

                        @default
                            <input type="text" name="fields[{{ $field->id }}]" class="form-control"
                                value="{{ $value }}">
                    @endswitch

                    @error("fields.{$field->id}")
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @endforeach

            <button type="submit" class="btn btn-success">{{ $submission ? 'Update' : 'Save' }}</button>
            <a href="{{ route('forms.submissions', $form->id) }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
