@extends('layout.app')

@section('title', 'View Task')

@section('page-title')
    <div class="d-flex justify-content-between align-items-center w-100">
        <h5 class="mb-0 fw-semibold text-dark">
            <i class="bi bi-eye me-2 text-primary"></i>
            View Task
        </h5>
    </div>
@endsection

@section('content')
    <div class="container-fluid px-0">

        {{-- Header Card --}}
        <div class="card shadow-sm border-0 mb-4 p-2">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 fw-semibold">{{ $task->title }}</h6>
                    <small class="text-muted">
                        Created on {{ $task->created_at->format('d M Y, h:i A') }}
                    </small>
                </div>

                <span class="badge bg-primary-subtle text-primary px-3 py-2">
                    Task ID : {{ $task->id }}
                </span>
            </div>
        </div>

        {{-- Task Details --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-list-check me-2 text-primary"></i>
                    Task Details
                </h6>
            </div>

            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <tbody>
                        <tr>
                            <th class="w-25 text-muted fw-medium px-4">Title</th>
                            <td class="px-4">{{ $task->title }}</td>
                        </tr>
                        <tr>
                            <th class="w-25 text-muted fw-medium px-4">Description</th>
                            <td class="px-4">{{ $task->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="w-25 text-muted fw-medium px-4">Assigned To</th>
                            <td class="px-4">{{ $task->user->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="w-25 text-muted fw-medium px-4">Status</th>
                            <td class="px-4">
                                <span
                                    class="badge text-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'dark' : 'secondary') }}">
                                    {{ ucfirst($task->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="w-25 text-muted fw-medium px-4">Start Time</th>
                            <td class="px-4">
                                {{ $task->start_time ? \Carbon\Carbon::parse($task->start_time)->format('d M Y, h:i A') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-25 text-muted fw-medium px-4">End Time</th>
                            <td class="px-4">{{ $task->end_time?->format('d M Y, h:i A') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="w-25 text-muted fw-medium px-4">Recurring Type</th>
                            <td class="px-4 text-capitalize">{{ $task->repeat_type ?? 'none' }}</td>
                        </tr>
                        @if ($task->repeat_type === 'daily')
                            <tr>
                                <th class="w-25 text-muted fw-medium px-4">Daily Time</th>
                                <td class="px-4">{{ optional($task->recurring)->time ?? '-' }}</td>
                            </tr>
                        @elseif($task->repeat_type === 'weekly')
                            <tr>
                                <th class="w-25 text-muted fw-medium px-4">Weekly Days</th>
                                <td class="px-4">{{ optional($task->recurring)->weekly_day ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="w-25 text-muted fw-medium px-4">Weekly Time</th>
                                <td class="px-4">{{ optional($task->recurring)->time ?? '-' }}</td>
                            </tr>
                        @elseif($task->repeat_type === 'monthly')
                            <tr>
                                <th class="w-25 text-muted fw-medium px-4">Monthly Date</th>
                                <td class="px-4">
                                    {{ optional($task->recurring)->monthly_date ? \Carbon\Carbon::parse($task->recurring->monthly_date)->format('d/m/Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-25 text-muted fw-medium px-4">Monthly Time</th>
                                <td class="px-4">{{ optional($task->recurring)->time ?? '-' }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th class="w-25 text-muted fw-medium px-4">Started At</th>
                            <td class="px-4">
                                {{ $task->started_at ? \Carbon\Carbon::parse($task->started_at)->format('d M Y, h:i A') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-25 text-muted fw-medium px-4">Completed At</th>
                            <td class="px-4">{{ $task->complated_at ? \Carbon\Carbon::parse($task->complated_at)->format('d M Y, h:i A') : '-' }}
</td>
                        </tr>
                        <tr>
                            <th class="w-25 text-muted fw-medium px-4">Total Time Taken</th>
                            <td class="px-4">
                                @if ($task->status == 'completed' && $task->started_at && $task->completed_at)
                                    @php
                                        $start = \Carbon\Carbon::parse($task->started_at);
                                        $end = \Carbon\Carbon::parse($task->completed_at);
                                        $diff = $start->diff($end);
                                    @endphp
                                    {{ $diff->h + $diff->days * 24 }}h {{ $diff->i }}m
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Back Button --}}
        <div class="d-flex justify-content-start mb-4">
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

    </div>
@endsection
