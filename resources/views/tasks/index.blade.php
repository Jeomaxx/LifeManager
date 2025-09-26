@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Tasks Header -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Task Management</h1>
                        <p class="text-gray-600 dark:text-gray-400">Organize and track your tasks efficiently</p>
                    </div>
                    <div class="flex space-x-4">
                        <button id="add-task-btn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors btn-loading">
                            <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                            </svg>
                            Add Task
                        </button>
                        <button id="bulk-actions-btn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Bulk Actions
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Filters and Search -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-4">
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="flex-1 min-w-64">
                        <input type="text" id="task-search" placeholder="Search tasks..." class="form-input w-full">
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Priority:</label>
                        <select id="priority-filter" class="form-input rounded-md border-gray-300 text-sm">
                            <option value="all">All</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</label>
                        <select id="status-filter" class="form-input rounded-md border-gray-300 text-sm">
                            <option value="all">All</option>
                            <option value="pending">Pending</option>
                            <option value="in-progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input type="checkbox" id="bulk-select-all" class="rounded border-gray-300">
                        <label for="bulk-select-all" class="text-sm text-gray-700 dark:text-gray-300">Select All</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Board -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Pending Tasks -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 bg-yellow-500 text-white">
                    <h3 class="font-semibold flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Pending Tasks
                        <span class="ml-auto bg-yellow-600 text-xs px-2 py-1 rounded-full">5</span>
                    </h3>
                </div>
                <div class="task-list p-4 space-y-3" data-status="pending">
                    <div class="task-item bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border priority-high" data-task-id="1">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-3">
                                <input type="checkbox" class="task-checkbox mt-1">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white">Complete project proposal</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Due: Tomorrow</p>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">High Priority</span>
                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Work</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button class="task-complete text-green-600 hover:text-green-800" data-task-id="1">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                                <button class="task-delete text-red-600 hover:text-red-800" data-task-id="1">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="task-item bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border priority-medium" data-task-id="2">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-3">
                                <input type="checkbox" class="task-checkbox mt-1">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white">Review marketing materials</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Due: Friday</p>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Medium Priority</span>
                                        <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">Marketing</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button class="task-complete text-green-600 hover:text-green-800" data-task-id="2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                                <button class="task-delete text-red-600 hover:text-red-800" data-task-id="2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- In Progress Tasks -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 bg-blue-500 text-white">
                    <h3 class="font-semibold flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                        </svg>
                        In Progress
                        <span class="ml-auto bg-blue-600 text-xs px-2 py-1 rounded-full">3</span>
                    </h3>
                </div>
                <div class="task-list p-4 space-y-3" data-status="in-progress">
                    <div class="task-item bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border priority-high" data-task-id="3">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-3">
                                <input type="checkbox" class="task-checkbox mt-1">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white">Update website design</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">In progress for 2 days</p>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">High Priority</span>
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Design</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: 60%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button class="task-complete text-green-600 hover:text-green-800" data-task-id="3">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                                <button class="task-delete text-red-600 hover:text-red-800" data-task-id="3">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Tasks -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 bg-green-500 text-white">
                    <h3 class="font-semibold flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Completed
                        <span class="ml-auto bg-green-600 text-xs px-2 py-1 rounded-full">8</span>
                    </h3>
                </div>
                <div class="task-list p-4 space-y-3" data-status="completed">
                    <div class="task-item bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border completed" data-task-id="4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-3">
                                <input type="checkbox" class="task-checkbox mt-1" checked>
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white">Set up project repository</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Completed yesterday</p>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Low Priority</span>
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">Development</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button class="task-delete text-red-600 hover:text-red-800" data-task-id="4">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Task Progress</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>Completed Tasks</span>
                                <span>8/16 (50%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 50%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>In Progress</span>
                                <span>3/16 (19%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: 19%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>Pending</span>
                                <span>5/16 (31%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: 31%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Productivity Metrics</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Tasks completed today</span>
                            <span class="font-bold text-green-600">3</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Average completion time</span>
                            <span class="font-bold text-blue-600">2.5 days</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Current streak</span>
                            <span class="font-bold text-purple-600">7 days</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Overdue tasks</span>
                            <span class="font-bold text-red-600">2</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Task search functionality
            document.getElementById('task-search').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const tasks = document.querySelectorAll('.task-item');
                
                tasks.forEach(task => {
                    const title = task.querySelector('h4').textContent.toLowerCase();
                    if (title.includes(searchTerm)) {
                        task.style.display = 'block';
                    } else {
                        task.style.display = 'none';
                    }
                });
            });
            
            // Filter functionality
            document.getElementById('priority-filter').addEventListener('change', function(e) {
                const priority = e.target.value;
                const tasks = document.querySelectorAll('.task-item');
                
                tasks.forEach(task => {
                    if (priority === 'all' || task.classList.contains(`priority-${priority}`)) {
                        task.style.display = 'block';
                    } else {
                        task.style.display = 'none';
                    }
                });
            });
        });
    </script>
@endsection