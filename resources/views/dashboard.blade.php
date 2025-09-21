<x-layouts.app>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h1 class="text-2xl font-bold mb-2">Welcome back, {{ $user->name }}!</h1>
                <p class="text-gray-600 dark:text-gray-400">Here's your life organization overview.</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Tasks Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Tasks</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $stats['completed_tasks'] }}/{{ $stats['total_tasks'] }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $stats['pending_tasks'] }} pending
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Goals Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Goals</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $stats['total_goals'] }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Active goals</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Habits Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Habits</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $stats['active_habits'] }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Active habits</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 102 0V3h2v1a1 1 0 102 0V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
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

        <!-- Quick Actions -->
        <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <button class="p-4 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900 dark:hover:bg-blue-800 rounded-lg text-center transition-colors">
                        <div class="text-blue-600 dark:text-blue-400 font-medium">Add Task</div>
                    </button>
                    <button class="p-4 bg-green-50 hover:bg-green-100 dark:bg-green-900 dark:hover:bg-green-800 rounded-lg text-center transition-colors">
                        <div class="text-green-600 dark:text-green-400 font-medium">New Goal</div>
                    </button>
                    <button class="p-4 bg-purple-50 hover:bg-purple-100 dark:bg-purple-900 dark:hover:bg-purple-800 rounded-lg text-center transition-colors">
                        <div class="text-purple-600 dark:text-purple-400 font-medium">Track Habit</div>
                    </button>
                    <button class="p-4 bg-yellow-50 hover:bg-yellow-100 dark:bg-yellow-900 dark:hover:bg-yellow-800 rounded-lg text-center transition-colors">
                        <div class="text-yellow-600 dark:text-yellow-400 font-medium">Write Note</div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>