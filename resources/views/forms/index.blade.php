@extends('layout.app')

@section('title','Forms List')

@section('page-title')
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold text-dark">
            <i class="bi bi-list-ul text-primary me-2"></i>Forms List
        </h5>

        <!-- Right side: Search icon and New Form button -->
    </div>
    <!-- Hidden search input -->
@endsection

@section('nav-button')
    <div id="searchDiv" style="display:none; max-width: 300px; ">
        <input type="text" id="formSearch" class="form-control form-control-sm" placeholder="Search...">
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- Search toggle button -->
        <button id="toggleSearch" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-search "></i>
        </button>

        <!-- New Form Button -->
        <a href="{{ route('forms.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Form
        </a>
    </div>
@endsection


@section('content')
    <div class="card shadow-sm rounded-3">
        <div class="card-body">
            <table id="FormTable" class="table table-striped custom-light table-hover align-middle w-100"
                data-url="{{ route('forms.datatable') }}">
                <thead class="table-light">
                    <tr>
                        <th style="width:10%;">ID</th>
                        <th style="width:70%;">Name</th>
                        <th style="width:20%; ">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/formsDatatable.js') }}"></script>
@endsection
