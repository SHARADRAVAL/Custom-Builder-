@extends('layout.app')

@section('title', 'View ' . $form->name)

@section('page-title')
    <div class="d-flex justify-content-between align-items-center w-100">
        <h5 class="mb-0 fw-semibold text-dark">
            <i class="bi bi-eye me-2 text-primary"></i>
            View {{ $form->name }}
        </h5>
    </div>
@endsection

@section('content')
    <div class="container-fluid px-0">

        {{-- Header Card --}}
        <div class="card shadow-sm border-0 mb-4 p-2">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 fw-semibold">{{ $form->name }}</h6>
                    <small class="text-muted">
                        Submitted on {{ $submission->created_at->format('d M Y, h:i A') }}
                    </small>
                </div>

                <span class="badge bg-primary-subtle text-primary px-3 py-2">
                    Submission Id :{{ $submission->id }}
                </span>
            </div>
        </div>

        {{-- Submission Data --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-check me-2 text-primary"></i>
                    Submission Details
                </h6>
            </div>

            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <tbody>
                        @foreach ($form->fields as $field)
                            <tr>
                                <th class="w-25 text-muted fw-medium px-4">
                                    {{ $field->label }}
                                </th>
                                <td class="px-4">
                                    {{ $values[$field->id] ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Bottom Actions --}}
        <div class="d-flex justify-content-start mb-4">
            <a href="{{ route('forms.submissions', $form->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

    </div>
@endsection
