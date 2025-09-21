<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Life Atlas Organizer - {{ $filename }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #007bff;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .json-data {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .status-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-in-progress { background-color: #d1ecf1; color: #0c5460; }
        .priority-high { color: #dc3545; font-weight: bold; }
        .priority-medium { color: #ffc107; font-weight: bold; }
        .priority-low { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Life Atlas Organizer</h1>
        <p>Data Export - {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p>Export: {{ $filename }}</p>
    </div>

    @if(is_array($data) && isset($data['user']))
        <!-- Complete Backup Format -->
        <div class="section">
            <h2>User Information</h2>
            <p><strong>Name:</strong> {{ $data['user']['name'] ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ $data['user']['email'] ?? 'N/A' }}</p>
            <p><strong>Member Since:</strong> {{ isset($data['user']['created_at']) ? \Carbon\Carbon::parse($data['user']['created_at'])->format('F j, Y') : 'N/A' }}</p>
        </div>

        @if(isset($data['tasks']) && $data['tasks']->count() > 0)
        <div class="section">
            <h2>Tasks ({{ $data['tasks']->count() }})</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                        <th>Category</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['tasks'] as $task)
                    <tr>
                        <td>{{ $task->title }}</td>
                        <td><span class="status-badge status-{{ $task->status }}">{{ ucfirst($task->status) }}</span></td>
                        <td><span class="priority-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span></td>
                        <td>{{ $task->due_date ? $task->due_date->format('M j, Y') : 'No due date' }}</td>
                        <td>{{ $task->category->name ?? 'Uncategorized' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(isset($data['goals']) && $data['goals']->count() > 0)
        <div class="section">
            <h2>Goals ({{ $data['goals']->count() }})</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Deadline</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['goals'] as $goal)
                    <tr>
                        <td>{{ $goal->title }}</td>
                        <td><span class="status-badge status-{{ $goal->status }}">{{ ucfirst($goal->status) }}</span></td>
                        <td>{{ $goal->progress_percentage ?? 0 }}%</td>
                        <td>{{ $goal->deadline ? $goal->deadline->format('M j, Y') : 'No deadline' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(isset($data['calendar_events']) && $data['calendar_events']->count() > 0)
        <div class="section">
            <h2>Calendar Events ({{ $data['calendar_events']->count() }})</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['calendar_events'] as $event)
                    <tr>
                        <td>{{ $event->title }}</td>
                        <td>{{ $event->start_date->format('M j, Y') }}</td>
                        <td>{{ $event->start_time ? $event->start_time->format('g:i A') : 'All day' }}</td>
                        <td>{{ ucfirst($event->event_type) }}</td>
                        <td>{{ $event->location ?? 'No location' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    @elseif(is_object($data) && method_exists($data, 'toArray'))
        <!-- Collection Format -->
        <div class="section">
            <h2>Data Export</h2>
            <table class="data-table">
                @foreach($data as $item)
                    @if($loop->first)
                        <thead>
                            <tr>
                                @foreach(array_keys($item->toArray()) as $key)
                                    <th>{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                    @endif
                    <tr>
                        @foreach($item->toArray() as $value)
                            <td>
                                @if(is_array($value) || is_object($value))
                                    {{ json_encode($value) }}
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @if($loop->last)
                        </tbody>
                    @endif
                @endforeach
            </table>
        </div>
    @else
        <!-- Raw Data Format -->
        <div class="section">
            <h2>Raw Data Export</h2>
            <div class="json-data">{{ json_encode($data, JSON_PRETTY_PRINT) }}</div>
        </div>
    @endif

    <div class="footer">
        <p>Generated by Life Atlas Organizer on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p>This document contains your personal productivity data.</p>
    </div>
</body>
</html>