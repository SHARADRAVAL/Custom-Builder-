<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Reminder</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .task-card {
            border-left: 5px solid #0d6efd;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .header {
            background: linear-gradient(90deg, #0d6efd, #6610f2);
            color: #fff;
            padding: 25px 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
            font-size: 1.6rem;
            font-weight: 600;
        }

        .footer {
            font-size: 14px;
            color: #6c757d;
            text-align: center;
            padding: 15px 0;
            margin-top: 20px;
            border-top: 1px solid #dee2e6;
        }

        .btn-start {
            background: #0d6efd;
            color: #fff;
            border-radius: 50px;
            transition: background 0.3s;
        }

        .btn-start:hover {
            background: #0b5ed7;
        }

        @media (max-width: 576px) {
            .header {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="card shadow-sm">
            <div class="header">
                🔔 Task Reminder
            </div>
            <div class="card-body">
                <p>Hi <strong>{{ $task->user->name ?? 'User' }}</strong>,</p>
                <p>This is a friendly reminder that your upcoming task is scheduled to start soon. Here are the details:</p>

                <div class="task-card bg-light p-4 mb-4 rounded shadow-sm">
                    <p><strong>Task:</strong> {{ $task->title }}</p>
                    <p><strong>Description:</strong> {{ $task->description ?? 'No description provided' }}</p>
                    <p><strong>Start Time:</strong> {{ $task->start_time ? \Carbon\Carbon::parse($task->start_time)->format('d M Y, h:i A') : '-' }}</p>

                    
                    @if($task->end_time)
                    <p><strong>End Time:</strong> {{ $task->end_time ? \Carbon\Carbon::parse($task->end_time)->format('d M Y, h:i A') : '-' }}</p>
                    {{-- <p><strong>End Time:</strong> {{ $task->end_time->format('d M Y, h:i A') }}</p> --}}
                    @endif
                    <p><strong>Status:</strong> <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'warning' : 'secondary') }}">{{ ucfirst($task->status) }}</span></p>
                    <a href="#" class="btn btn-start mt-3">Start Task</a>
                </div>

                <p>Kindly ensure that you start the task on time to stay on track.</p>
                <p>Have a productive day! 🚀</p>
            </div>
            <div class="footer">
                This is an automated reminder from your Task Management System.
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (optional, for interactive components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

