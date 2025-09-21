<x-layouts.app>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Goals Header -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Goal Tracking</h1>
                        <p class="text-gray-600 dark:text-gray-400">Set, track, and achieve your goals</p>
                    </div>
                    <div class="flex space-x-4">
                        <button id="add-goal-btn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors btn-loading">
                            <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                            </svg>
                            New Goal
                        </button>
                        <button id="export-goals-btn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Goals Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Goals</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">8</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">3</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Progress</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">67%</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">This Month</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">2</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Goals List -->
        <div class="space-y-6">
            <!-- Personal Goals -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Personal Goals</h3>
                    <div class="space-y-4">
                        <div class="goal-item border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover-card" data-goal-id="1">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white">Learn Spanish</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Complete intermediate Spanish course</p>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Personal</span>
                                        <span class="text-sm text-gray-500">Due: Dec 31, 2024</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="goal-update text-blue-600 hover:text-blue-800" data-goal-id="1" data-progress="75">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                    </button>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">75%</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="goal-progress bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-1000" style="width: 75%" data-progress="75"></div>
                            </div>
                            <div class="mt-3 flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                <span>Progress: 15/20 lessons</span>
                                <span>Started: Jan 15, 2024</span>
                            </div>
                        </div>

                        <div class="goal-item border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover-card" data-goal-id="2">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white">Read 24 Books</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Read one book every two weeks</p>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">Personal</span>
                                        <span class="text-sm text-gray-500">Due: Dec 31, 2024</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="goal-update text-blue-600 hover:text-blue-800" data-goal-id="2" data-progress="50">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                    </button>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">50%</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="goal-progress bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full transition-all duration-1000" style="width: 50%" data-progress="50"></div>
                            </div>
                            <div class="mt-3 flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                <span>Progress: 12/24 books</span>
                                <span>Started: Jan 1, 2024</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professional Goals -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Professional Goals</h3>
                    <div class="space-y-4">
                        <div class="goal-item border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover-card" data-goal-id="3">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white">Complete Certification</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">AWS Solutions Architect certification</p>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Professional</span>
                                        <span class="text-sm text-gray-500">Due: Jun 30, 2024</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="goal-update text-blue-600 hover:text-blue-800" data-goal-id="3" data-progress="85">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                    </button>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">85%</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="goal-progress bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-1000" style="width: 85%" data-progress="85"></div>
                            </div>
                            <div class="mt-3 flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                <span>Progress: 85/100 study hours</span>
                                <span>Started: Mar 1, 2024</span>
                            </div>
                        </div>

                        <div class="goal-item border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover-card" data-goal-id="4">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white">Launch Side Project</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Complete and deploy portfolio website</p>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Professional</span>
                                        <span class="text-sm text-gray-500">Due: May 15, 2024</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="goal-update text-blue-600 hover:text-blue-800" data-goal-id="4" data-progress="40">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                    </button>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">40%</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="goal-progress bg-gradient-to-r from-yellow-500 to-yellow-600 h-3 rounded-full transition-all duration-1000" style="width: 40%" data-progress="40"></div>
                            </div>
                            <div class="mt-3 flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                <span>Progress: 4/10 milestones</span>
                                <span>Started: Feb 1, 2024</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Goal Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Goal Progress Chart</h3>
                    <div class="h-64">
                        <canvas id="goalProgressChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Achievement Timeline</h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-3 h-3 bg-green-500 rounded-full flex-shrink-0"></div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white">Fitness Goal Completed</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">March 15, 2024</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="w-3 h-3 bg-blue-500 rounded-full flex-shrink-0"></div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white">Started AWS Certification</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">March 1, 2024</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full flex-shrink-0"></div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white">Portfolio Project Kickoff</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">February 1, 2024</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize goal progress animations
            if (window.productivityApp) {
                window.productivityApp.initGoalTracking();
            }
            
            // Initialize goal progress chart
            const ctx = document.getElementById('goalProgressChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels: ['Personal Development', 'Career Growth', 'Health & Fitness', 'Financial', 'Relationships', 'Learning'],
                        datasets: [{
                            label: 'Goal Progress',
                            data: [75, 65, 90, 45, 80, 70],
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgb(59, 130, 246)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-layouts.app>