<x-layouts.app>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Calendar Header -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Calendar</h1>
                        <p class="text-gray-600 dark:text-gray-400">Manage your events and schedule</p>
                    </div>
                    <div class="flex space-x-4">
                        <button id="add-event-btn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors btn-loading">
                            <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                            </svg>
                            Add Event
                        </button>
                        <button id="sync-calendar-btn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                            </svg>
                            Sync
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar View Options -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-4">
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">View:</label>
                        <select id="calendar-view" class="form-input rounded-md border-gray-300 text-sm">
                            <option value="dayGridMonth">Month</option>
                            <option value="timeGridWeek">Week</option>
                            <option value="timeGridDay">Day</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter:</label>
                        <select id="event-filter" class="form-input rounded-md border-gray-300 text-sm">
                            <option value="all">All Events</option>
                            <option value="work">Work</option>
                            <option value="personal">Personal</option>
                            <option value="health">Health</option>
                            <option value="social">Social</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input type="checkbox" id="show-completed" class="rounded border-gray-300">
                        <label for="show-completed" class="text-sm text-gray-700 dark:text-gray-300">Show completed events</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Calendar -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <div id="calendar" class="animate-on-scroll"></div>
            </div>
        </div>

        <!-- Upcoming Events Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Today's Events -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Today's Events</h3>
                    <div id="today-events" class="space-y-3">
                        <div class="border-l-4 border-blue-500 pl-4 py-2">
                            <div class="font-medium text-gray-900 dark:text-white">Team Meeting</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">9:00 AM - 10:00 AM</div>
                        </div>
                        <div class="border-l-4 border-green-500 pl-4 py-2">
                            <div class="font-medium text-gray-900 dark:text-white">Lunch with Client</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">12:30 PM - 1:30 PM</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- This Week -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">This Week</h3>
                    <div id="week-events" class="space-y-3">
                        <div class="flex justify-between items-center py-2">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Project Review</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Wednesday</div>
                            </div>
                            <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Doctor Appointment</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Friday</div>
                            </div>
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Calendar Stats</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Events today</span>
                            <span class="font-bold text-blue-600" data-stat="events_today">3</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">This week</span>
                            <span class="font-bold text-green-600" data-stat="events_week">12</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">This month</span>
                            <span class="font-bold text-purple-600" data-stat="events_month">45</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Creation Modal -->
    <div id="event-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Create Event</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="event-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                    <input type="text" id="event-title" class="form-input w-full" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea id="event-description" class="form-input w-full" rows="3"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                        <input type="datetime-local" id="event-start" class="form-input w-full" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                        <input type="datetime-local" id="event-end" class="form-input w-full" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                    <select id="event-category" class="form-input w-full">
                        <option value="work">Work</option>
                        <option value="personal">Personal</option>
                        <option value="health">Health</option>
                        <option value="social">Social</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancel-event" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg btn-loading">Create Event</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize modal functionality
            const modal = document.getElementById('event-modal');
            const addEventBtn = document.getElementById('add-event-btn');
            const closeModal = document.getElementById('close-modal');
            const cancelEvent = document.getElementById('cancel-event');
            
            addEventBtn.addEventListener('click', () => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
            
            closeModal.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
            
            cancelEvent.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
            
            // Calendar view change
            document.getElementById('calendar-view').addEventListener('change', function(e) {
                if (window.productivityApp.calendar) {
                    window.productivityApp.calendar.changeView(e.target.value);
                }
            });
            
            // Event form submission
            document.getElementById('event-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const eventData = {
                    title: document.getElementById('event-title').value,
                    description: document.getElementById('event-description').value,
                    start: document.getElementById('event-start').value,
                    end: document.getElementById('event-end').value,
                    category: document.getElementById('event-category').value
                };
                
                window.productivityApp.createCalendarEvent(eventData);
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                this.reset();
            });
        });
    </script>
</x-layouts.app>