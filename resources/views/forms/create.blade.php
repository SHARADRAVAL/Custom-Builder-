@extends('layout.app')

@section('page-title')
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold text-dark">
            <i class="bi bi-ui-checks-grid me-2 text-primary"></i>Create New Form
        </h5>
    </div>
@endsection

@section('content')
<div class="container-fluid w-100 px-0">
    <div class="row justify-content-center">
        <div class="col-12"> <!-- full width card -->

            <div class="card shadow-sm border-0 rounded-4">
                {{-- <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-ui-checks-grid me-2 text-primary"></i> Form Details
                    </h5>
                </div> --}}

                <div class="card-body px-4 py-4">
                    <form action="{{ route('forms.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-medium">Form Name</label>
                            <input type="text" name="name" class="form-control form-control-md"
                                   placeholder="Enter form name" required>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                            <a href="{{ route('forms.index') }}" class="btn btn-light">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </a>

                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-circle me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
