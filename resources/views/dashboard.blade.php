@extends('layout.app')

@section('page-title')
<div class="d-flex justify-content-between align-items-center w-100">
    <h5 class="mb-0 fw-semibold text-dark">
        <i class="bi bi-house-door me-2 text-primary"></i>
        Dashboard
    </h5>
</div>
@endsection
@section('nav-button')
    @auth
        <div class="d-flex align-items-center gap-2">
            <img
                src="{{ auth()->user()->avatar 
                        ? asset('./public/images/user.png' . auth()->user()->avatar) 
                        : asset('images/user.png') }}"
                class="rounded-circle border"
                width="36"
                height="36"
                alt="Avatar">

            <span class="fw-semibold">
                {{ auth()->user()->name }}
            </span>
        </div>
    @endauth
@endsection


@section('content')
<div class="container-fluid w-100 px-0"> <!-- full width container -->

    {{-- Dashboard Card --}}
    <div class="card shadow-sm rounded-4 border-0">
        <div class="card-body px-4 py-4">

            {{-- Welcome Message --}}
            <h3 class="fw-semibold">
                Welcome to your Dashboard, 
                <span class="text-primary">
                    {{ Auth::check() ? Auth::user()->name : 'Guest' }}
                </span>
            </h3>

            {{-- Brief Description --}}
            <p class="text-muted mt-2">
                Select a form from the sidebar to manage your submissions.
            </p>

            {{-- Optional Action Button --}}
            <a href="{{ route('forms.index') }}" class="btn btn-primary mt-3 px-4">
                <i class="bi bi-file-earmark-plus me-1"></i> View Forms
            </a>
        </div>
    </div>

</div>
@endsection
