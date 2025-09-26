@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Section with Gamification -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">Welcome back, {{ $user->name }}!</h1>
                        <p class="text-blue-100">Level {{ $achievements['level'] }} • {{ $achievements['total_points'] }} points</p>
                    </div>
                    <div class="text-right">
                        <div class="bg-white bg-opacity-20 rounded-lg p-4">
                            <div class="text-2xl font-bold">{{ number_format(($stats['completed_tasks'] / max($stats['total_tasks'], 1)) * 100, 1) }}%</div>
                            <div class="text-sm text-blue-100">Completion Rate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Tasks Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-blue-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Tasks</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $stats['completed_tasks'] }}/{{ $stats['total_tasks'] }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $stats['pending_tasks'] }} pending • {{ $stats['overdue_tasks'] }} overdue
                            </div>
                            <!-- Progress bar -->
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $stats['total_tasks'] > 0 ? ($stats['completed_tasks'] / $stats['total_tasks']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Goals Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-green-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Goals</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $stats['completed_goals'] }}/{{ $stats['total_goals'] }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Active goals</div>
                            <!-- Progress bar -->
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $stats['total_goals'] > 0 ? ($stats['completed_goals'] / $stats['total_goals']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Events Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-purple-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 7V3a1 1 0 012 0v4h4a1 1 0 010 2h-4v4a1 1 0 01-2 0v-4H4a1 1 0 010-2h4z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Calendar</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $stats['total_events'] }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $stats['upcoming_events'] }} upcoming
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-yellow-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 102 0V3h2v1a1 1 0 102 0V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $stats['total_notes'] }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total notes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Task Completion Chart -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Task Completion Trend</h3>
                    <div class="h-64">
                        <canvas id="taskCompletionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Priority Distribution Chart -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tasks by Priority</h3>
                    <div class="h-64">
                        <canvas id="priorityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Achievements Section -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-8">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Achievements</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($achievements['achievements'] as $key => $achievement)
                    <div class="border rounded-lg p-4 {{ $achievement['completed'] ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
                        <div class="flex items-center mb-2">
                            <div class="w-8 h-8 {{ $achievement['completed'] ? 'bg-green-500' : 'bg-gray-400' }} rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium {{ $achievement['completed'] ? 'text-green-800' : 'text-gray-700' }}">{{ $achievement['name'] }}</h4>
                                <p class="text-sm {{ $achievement['completed'] ? 'text-green-600' : 'text-gray-500' }}">{{ $achievement['description'] }}</p>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="{{ $achievement['completed'] ? 'bg-green-500' : 'bg-blue-500' }} h-2 rounded-full" style="width: {{ ($achievement['progress'] / $achievement['target']) * 100 }}%"></div>
                        </div>
                        <div class="text-xs text-gray-600 mt-1">{{ $achievement['progress'] }}/{{ $achievement['target'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Recent Activities -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activities</h2>
                    <div class="space-y-3">
                        @forelse($recentActivities as $activity)
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-{{ $activity['color'] }}-500 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $activity['action'] }} {{ $activity['type'] }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $activity['title'] }}</p>
                                <p class="text-xs text-gray-500">{{ $activity['date']->diffForHumans() }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500 text-center py-4">No recent activities</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('tasks.create') }}" class="block p-4 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900 dark:hover:bg-blue-800 rounded-lg text-center transition-colors">
                            <div class="text-blue-600 dark:text-blue-400 font-medium">Add Task</div>
                        </a>
                        <a href="{{ route('goals.create') }}" class="block p-4 bg-green-50 hover:bg-green-100 dark:bg-green-900 dark:hover:bg-green-800 rounded-lg text-center transition-colors">
                            <div class="text-green-600 dark:text-green-400 font-medium">New Goal</div>
                        </a>
                        <a href="{{ route('calendar.create') }}" class="block p-4 bg-purple-50 hover:bg-purple-100 dark:bg-purple-900 dark:hover:bg-purple-800 rounded-lg text-center transition-colors">
                            <div class="text-purple-600 dark:text-purple-400 font-medium">Schedule Event</div>
                        </a>
                        <a href="{{ route('notes.create') }}" class="block p-4 bg-yellow-50 hover:bg-yellow-100 dark:bg-yellow-900 dark:hover:bg-yellow-800 rounded-lg text-center transition-colors">
                            <div class="text-yellow-600 dark:text-yellow-400 font-medium">Write Note</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Task Completion Chart
        const taskCtx = document.getElementById('taskCompletionChart').getContext('2d');
        const taskChart = new Chart(taskCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_keys($analytics['task_completion_trend']->toArray())) !!},
                datasets: [{
                    label: 'Tasks Completed',
                    data: {!! json_encode(array_values($analytics['task_completion_trend']->toArray())) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Priority Chart
        const priorityCtx = document.getElementById('priorityChart').getContext('2d');
        const priorityChart = new Chart(priorityCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($analytics['tasks_by_priority']->toArray())) !!},
                datasets: [{
                    data: {!! json_encode(array_values($analytics['tasks_by_priority']->toArray())) !!},
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',   // low - green
                        'rgba(234, 179, 8, 0.8)',   // medium - yellow
                        'rgba(249, 115, 22, 0.8)',  // high - orange
                        'rgba(239, 68, 68, 0.8)'    // urgent - red
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
@endsection