@extends('layout.app')

@section('title', 'edit'.' '.$form->name)

@section('page-title')
    <h5 class="fw-semibold mb-0 text-dark">
        <i class="bi bi-pencil-square text-primary me-2"></i> Edit  {{ $form->name }}
    </h5>
@endsection



@section('content')
<div class="container-fluid w-100 ">
    
    <!-- Edit Form Card -->
    <div class="card shadow-sm mb-4 border-0 rounded-4">
        <div class="card-body px-4 py-4">
            <form action="{{ route('forms.update', $form->id) }}" method="POST" class="mb-0">
                @csrf
                @method('PUT')

                <div class="form-floating mb-3">
                    <input type="text" name="name" class="form-control form-control-mg" id="formName" value="{{ $form->name }}"
                           required>
                    <label for="formName">Form Name</label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update Form</button>
                    <a href="{{ route('forms.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Fields Table -->
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">Fields</h6>
        </div>
        <div class="card-body px-4 py-4">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Label</th>
                        <th>Type</th>
                        <th>Required</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($form->fields as $field)
                        <tr>
                            <td>{{ $field->label }}</td>
                            <td>{{ ucfirst($field->type) }}</td>
                            <td>{{ $field->is_required ? 'Yes' : 'No' }}</td>
                            <td class="text-center">
                                <!-- Delete Button -->
                                <form action="{{ route('fields.destroy', $field->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this field?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No fields added yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add New Field Card -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body px-4 py-4">
            <h6 class="mb-3">Add New Field</h6>
            <form action="{{ route('fields.store', $form->id) }}" method="POST">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" name="label" class="form-control" id="fieldLabel"
                                   placeholder="Field Label" required>
                            <label for="fieldLabel">Field Label</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <select name="type" class="form-select" id="fieldType" required>
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="textarea">Textarea</option>
                                <option value="date">Date</option>
                                <option value="time">Time</option>
                            </select>
                            <label for="fieldType">Field Type</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="is_required" value="1"
                                   id="isRequired">
                            <label class="form-check-label" for="isRequired">Required</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="submit" class="btn btn-primary">Add Field</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
