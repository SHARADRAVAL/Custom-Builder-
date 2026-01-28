<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard')</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Flatpickr --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Font Awesome icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/brands.min.css">

    {{-- DataTables --}}

    {{-- Timepicker --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">

    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
     <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

@php
    $sidebarMinimized = session('sidebarMinimized', false);
@endphp

<body id="body"
    class="{{ old('sidebarMinimized', session('sidebarMinimized', false)) == 'true' ? 'sidebar-minimized' : '' }}">

    {{-- SIDEBAR --}}
    <div class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-inner">
                <i class="bi bi-ui-checks-grid"></i>
                <span>Form Builder</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle" title="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
        </div>

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 text-primary"></i>
            <span>Dashboard</span>
        </a>

        <hr>
        <a href="{{ route('tasks.index') }}" class="{{ request()->routeIs('tasks.index') ? 'active' : '' }}">
            <i class="bi bi-check2-square text-primary"></i>
            <span>Tasks</span>
        </a>

        <a href="{{ route('recurring-tasks.index') }}" class="{{ request()->routeIS('recurring-tasks.index') ? 'active' : '' }}">
            <i class="bi bi-list-task text-primary"></i>
            <span>Recurring Tasks</span>
        </a>

        {{-- Forms --}}
        <a href="{{ route('forms.index') }}" class="{{ request()->routeIs('forms.index') ? 'active' : '' }}">
            <i class="fa-solid fa-file-circle-check text-primary"></i>
            <span>Forms</span>
        </a>

        <hr>
        <div class="sidebar-subtitle px-3 text-secondary mt-6 mb-2 fw-semibold">
            Custom Forms
        </div>

        {{-- Custom forms --}}
        <div class="modern-scroll" style="max-height: 400px; overflow-y:auto;">
            @foreach ($forms ?? [] as $f)
                @php
                    $isActive = request()->routeIs('forms.submissions') && request()->route('formId') == $f->id;
                @endphp
                <a href="{{ route('forms.submissions', $f->id) }}" class="{{ $isActive ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span>{{ $f->name }}</span>
                </a>
            @endforeach
        </div>

        <div class="sidebar-footer p-3">
            <a href="{{ route('logout') }}">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>


    {{-- <div class="sidebar" id="sidebar">
        <div class="brand">
            <i class="bi bi-ui-checks-grid"></i>
            <span class="brand-text">Form Builder</span>

            <!-- Toggle button (hidden by default) -->
            <button class="sidebar-toggle" id="sidebarToggle" title="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('forms.index') }}" class="{{ request()->routeIs('forms.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i>
            <span>Forms</span>
        </a>

        <div class="sidebar-footer">
            <a href="{{ route('logout') }}">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </div> --}}


    {{-- TOPBAR --}}
    <nav class="topbar d-flex align-items-center justify-content-between shadow-sm bg-white px-3 py-2">
        <div class="d-flex align-items-center gap-3">

            <h6 class="mb-0 fw-bold text-secondary">@yield('page-title')</h6>
        </div>
        <div class="d-flex align-items-center gap-3">@yield('nav-button')</div>
    </nav>

    {{-- MAIN CONTENT --}}
    <div class="content">
        @yield('content')
    </div>

    {{-- JS LIBRARIES --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Timepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

    {{-- Flash Messages --}}
    <script>
        window.flash = {
            success: "{{ session('success') ?? '' }}",
            error: "{{ session('error') ?? '' }}",
            validationErrors: {!! json_encode($errors->all()) !!}
        };
    </script>

     <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Custom JS --}}
    <script src="{{ asset('js/app.js') }}"></script>

    @yield('scripts')
</body>

</html>
{{-- END OF LAYOUT --}}
