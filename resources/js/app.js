import './bootstrap';
import { Chart, registerables } from 'chart.js';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import 'chartjs-adapter-date-fns';
import Sortable from 'sortablejs';
import Alpine from 'alpinejs';

// Register Chart.js components
Chart.register(...registerables);

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

class ProductivityApp {
    constructor() {
        this.charts = {};
        this.calendar = null;
        this.theme = localStorage.getItem('theme') || 'light';
        this.notifications = [];
        this.init();
    }

    init() {
        this.initTheme();
        this.initNotifications();
        this.initServiceWorker();
        
        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', () => {
            this.initCharts();
            this.initCalendar();
            this.initTaskManagement();
            this.initGoalTracking();
            this.initHabitTracker();
            this.initNotesInterface();
            this.initAnimations();
            this.initRealTimeFeatures();
        });
    }

    // Theme Management
    initTheme() {
        this.applyTheme();
        this.createThemeToggle();
    }

    applyTheme() {
        const html = document.documentElement;
        if (this.theme === 'dark') {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    }

    createThemeToggle() {
        // Add theme toggle to navigation
        const nav = document.querySelector('nav .flex.items-center.space-x-4');
        if (nav) {
            const themeToggle = document.createElement('button');
            themeToggle.innerHTML = `
                <div class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" ${this.theme === 'dark' ? 'checked' : ''}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">🌙</span>
                </div>
            `;
            themeToggle.addEventListener('click', () => this.toggleTheme());
            nav.insertBefore(themeToggle, nav.firstChild);
        }
    }

    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        localStorage.setItem('theme', this.theme);
        this.applyTheme();
        
        // Recreate charts with new theme
        this.destroyCharts();
        setTimeout(() => this.initCharts(), 100);
    }

    // Dashboard Charts
    initCharts() {
        this.initTaskCompletionChart();
        this.initPriorityChart();
    }

    async initTaskCompletionChart() {
        const ctx = document.getElementById('taskCompletionChart');
        if (!ctx) return;

        const isDark = this.theme === 'dark';
        const textColor = isDark ? '#E5E7EB' : '#374151';
        const gridColor = isDark ? '#374151' : '#E5E7EB';

        try {
            const response = await fetch('/api/analytics/tasks?period=7', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const analytics = await response.json();
            
            const completionTrend = analytics.completion_trend || [];
            const labels = completionTrend.map(item => new Date(item.date).toLocaleDateString('en', { weekday: 'short' }));
            const data = completionTrend.map(item => item.count);
            
            const chartData = {
                labels: labels.length ? labels : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Completed Tasks',
                    data: data.length ? data : [0, 0, 0, 0, 0, 0, 0],
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            };

            this.charts.taskCompletion = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: textColor
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: textColor },
                            grid: { color: gridColor }
                        },
                        y: {
                            ticks: { color: textColor },
                            grid: { color: gridColor }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Failed to load task completion chart:', error);
            // Fallback to sample data
            this.createFallbackTaskChart(ctx, textColor, gridColor);
        }
    }

    async initPriorityChart() {
        const ctx = document.getElementById('priorityChart');
        if (!ctx) return;

        const isDark = this.theme === 'dark';
        const textColor = isDark ? '#E5E7EB' : '#374151';

        try {
            const response = await fetch('/api/analytics/tasks?period=30', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const analytics = await response.json();
            
            const priorityDist = analytics.priority_distribution || [];
            const priorityData = { high: 0, medium: 0, low: 0 };
            
            priorityDist.forEach(item => {
                priorityData[item.priority] = item.count;
            });
            
            const chartData = {
                labels: ['High', 'Medium', 'Low'],
                datasets: [{
                    data: [priorityData.high, priorityData.medium, priorityData.low],
                    backgroundColor: ['#EF4444', '#F59E0B', '#10B981']
                }]
            };

            this.charts.priority = new Chart(ctx, {
                type: 'doughnut',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: textColor
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Failed to load priority chart:', error);
            this.createFallbackPriorityChart(ctx, textColor);
        }
    }

    createFallbackTaskChart(ctx, textColor, gridColor) {
        const data = {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Completed Tasks',
                data: [0, 0, 0, 0, 0, 0, 0],
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        };

        this.charts.taskCompletion = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: textColor },
                        grid: { color: gridColor }
                    },
                    y: {
                        ticks: { color: textColor },
                        grid: { color: gridColor }
                    }
                }
            }
        });
    }
    
    createFallbackPriorityChart(ctx, textColor) {
        const data = {
            labels: ['High', 'Medium', 'Low'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#EF4444', '#F59E0B', '#10B981']
            }]
        };

        this.charts.priority = new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor
                        }
                    }
                }
            }
        });
    }

    destroyCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });
        this.charts = {};
    }

    // Interactive Calendar
    initCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        this.calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            editable: true,
            droppable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            weekends: true,
            events: '/api/calendar/events',
            
            select: (info) => {
                this.handleCalendarSelect(info);
            },
            
            eventClick: (info) => {
                this.handleEventClick(info);
            },
            
            eventDrop: (info) => {
                this.handleEventDrop(info);
            },
            
            eventResize: (info) => {
                this.handleEventResize(info);
            }
        });
        
        this.calendar.render();
    }

    handleCalendarSelect(info) {
        const title = prompt('Event Title:');
        if (title) {
            this.createCalendarEvent({
                title: title,
                start: info.start,
                end: info.end
            });
        }
        this.calendar.unselect();
    }

    handleEventClick(info) {
        if (confirm('Delete this event?')) {
            this.deleteCalendarEvent(info.event.id);
            info.event.remove();
        }
    }

    handleEventDrop(info) {
        this.updateCalendarEvent(info.event.id, {
            start: info.event.start,
            end: info.event.end
        });
    }

    handleEventResize(info) {
        this.updateCalendarEvent(info.event.id, {
            start: info.event.start,
            end: info.event.end
        });
    }

    async createCalendarEvent(eventData) {
        try {
            const response = await fetch('/api/calendar/events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(eventData)
            });
            
            if (response.ok) {
                const event = await response.json();
                this.calendar.addEvent(event);
                this.showNotification('Event created successfully!', 'success');
            }
        } catch (error) {
            this.showNotification('Failed to create event', 'error');
        }
    }

    async updateCalendarEvent(eventId, eventData) {
        try {
            await fetch(`/api/calendar/events/${eventId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(eventData)
            });
            
            this.showNotification('Event updated successfully!', 'success');
        } catch (error) {
            this.showNotification('Failed to update event', 'error');
        }
    }

    async deleteCalendarEvent(eventId) {
        try {
            await fetch(`/api/calendar/events/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            this.showNotification('Event deleted successfully!', 'success');
        } catch (error) {
            this.showNotification('Failed to delete event', 'error');
        }
    }

    // Task Management
    initTaskManagement() {
        this.initSortableTasks();
        this.initTaskQuickActions();
        this.initBulkActions();
    }

    initSortableTasks() {
        const taskLists = document.querySelectorAll('.task-list');
        taskLists.forEach(list => {
            new Sortable(list, {
                group: 'tasks',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: (evt) => {
                    this.updateTaskOrder(evt);
                }
            });
        });
    }

    initTaskQuickActions() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.task-complete')) {
                e.preventDefault();
                this.toggleTaskComplete(e.target.dataset.taskId);
            }
            
            if (e.target.matches('.task-delete')) {
                e.preventDefault();
                if (confirm('Delete this task?')) {
                    this.deleteTask(e.target.dataset.taskId);
                }
            }
        });
    }

    initBulkActions() {
        const bulkSelect = document.getElementById('bulk-select-all');
        if (bulkSelect) {
            bulkSelect.addEventListener('change', (e) => {
                const checkboxes = document.querySelectorAll('.task-checkbox');
                checkboxes.forEach(cb => cb.checked = e.target.checked);
            });
        }
    }

    async toggleTaskComplete(taskId) {
        try {
            const response = await fetch(`/api/tasks/${taskId}/complete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                const taskElement = document.querySelector(`[data-task-id="${taskId}"]`);
                if (taskElement) {
                    taskElement.classList.toggle('completed');
                    // Move to completed column if in kanban view
                    const completedColumn = document.querySelector('[data-status="completed"] .task-list');
                    if (completedColumn && result.task.status === 'completed') {
                        completedColumn.appendChild(taskElement);
                    }
                }
                this.showNotification(result.message || 'Task updated!', 'success');
                this.updateDashboardStats();
            }
        } catch (error) {
            this.showNotification('Failed to update task', 'error');
        }
    }

    async deleteTask(taskId) {
        try {
            await fetch(`/api/tasks/${taskId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            document.querySelector(`[data-task-id="${taskId}"]`).remove();
            this.showNotification('Task deleted!', 'success');
        } catch (error) {
            this.showNotification('Failed to delete task', 'error');
        }
    }

    async updateTaskOrder(evt) {
        const taskId = evt.item.dataset.taskId;
        const newStatus = evt.to.dataset.status;
        const oldStatus = evt.from.dataset.status;
        
        try {
            // If moving between columns, update status
            if (newStatus && newStatus !== oldStatus) {
                await fetch(`/api/tasks/${taskId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                this.showNotification('Task status updated!', 'success');
                this.updateDashboardStats();
            }
        } catch (error) {
            this.showNotification('Failed to update task', 'error');
            // Revert the move
            evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
        }
    }

    // Goal Tracking
    initGoalTracking() {
        this.initGoalProgressBars();
        this.initGoalQuickUpdate();
    }

    initGoalProgressBars() {
        const progressBars = document.querySelectorAll('.goal-progress');
        progressBars.forEach(bar => {
            this.animateProgressBar(bar);
        });
    }

    animateProgressBar(progressBar) {
        const targetWidth = progressBar.dataset.progress;
        progressBar.style.width = '0%';
        
        setTimeout(() => {
            progressBar.style.transition = 'width 1.5s ease-out';
            progressBar.style.width = targetWidth + '%';
        }, 100);
    }

    initGoalQuickUpdate() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.goal-update')) {
                e.preventDefault();
                const goalId = e.target.dataset.goalId;
                const currentProgress = parseInt(e.target.dataset.progress);
                const newProgress = prompt('Update progress (0-100):', currentProgress);
                
                if (newProgress !== null && !isNaN(newProgress)) {
                    this.updateGoalProgress(goalId, parseInt(newProgress));
                }
            }
        });
    }

    async updateGoalProgress(goalId, progress) {
        try {
            const response = await fetch(`/api/goals/${goalId}/progress`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ current_progress: progress })
            });
            
            if (response.ok) {
                const result = await response.json();
                const progressBar = document.querySelector(`[data-goal-id="${goalId}"] .goal-progress`);
                if (progressBar) {
                    progressBar.dataset.progress = progress;
                    progressBar.style.width = progress + '%';
                    this.animateProgressBar(progressBar);
                }
                
                // Update progress text
                const progressText = document.querySelector(`[data-goal-id="${goalId}"] .progress-text`);
                if (progressText) {
                    progressText.textContent = `${progress}%`;
                }
                
                this.showNotification(result.message || 'Goal progress updated!', 'success');
                this.updateDashboardStats();
            }
        } catch (error) {
            this.showNotification('Failed to update goal', 'error');
        }
    }

    // Habit Tracker
    initHabitTracker() {
        this.initHabitStreaks();
        this.initHabitQuickLog();
    }

    initHabitStreaks() {
        const streakElements = document.querySelectorAll('.habit-streak');
        streakElements.forEach(element => {
            this.animateCounter(element, parseInt(element.textContent));
        });
    }

    animateCounter(element, target) {
        let current = 0;
        const increment = target / 30;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current);
        }, 50);
    }

    initHabitQuickLog() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.habit-log')) {
                e.preventDefault();
                const habitId = e.target.dataset.habitId;
                this.logHabit(habitId);
            }
        });
    }

    async logHabit(habitId) {
        try {
            const response = await fetch(`/api/habits/${habitId}/log`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                const habitElement = document.querySelector(`[data-habit-id="${habitId}"]`);
                if (habitElement) {
                    habitElement.classList.add('logged-today');
                    const logButton = habitElement.querySelector('.habit-log');
                    if (logButton) {
                        logButton.textContent = '✓';
                        logButton.classList.add('text-green-600');
                        logButton.classList.remove('text-gray-400');
                    }
                    
                    // Update streak display
                    const streakElement = habitElement.querySelector('.habit-streak');
                    if (streakElement && result.habit) {
                        streakElement.textContent = result.habit.current_streak || 0;
                    }
                }
                
                this.showNotification(result.message || 'Habit logged!', 'success');
                this.updateDashboardStats();
            }
        } catch (error) {
            this.showNotification('Failed to log habit', 'error');
        }
    }

    // Notes Interface
    initNotesInterface() {
        this.initNoteSearch();
        this.initNoteQuickActions();
        this.initAutoSave();
    }

    initNoteSearch() {
        const searchInput = document.getElementById('note-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.searchNotes(e.target.value);
            });
        }
    }

    searchNotes(query) {
        const notes = document.querySelectorAll('.note-item');
        notes.forEach(note => {
            const title = note.querySelector('.note-title').textContent.toLowerCase();
            const content = note.querySelector('.note-content').textContent.toLowerCase();
            
            if (title.includes(query.toLowerCase()) || content.includes(query.toLowerCase())) {
                note.style.display = 'block';
            } else {
                note.style.display = 'none';
            }
        });
    }

    initNoteQuickActions() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.note-pin')) {
                e.preventDefault();
                this.toggleNotePin(e.target.dataset.noteId);
            }
            
            if (e.target.matches('.note-favorite')) {
                e.preventDefault();
                this.toggleNoteFavorite(e.target.dataset.noteId);
            }
        });
    }

    async toggleNotePin(noteId) {
        try {
            await fetch(`/api/notes/${noteId}/pin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const pinButton = document.querySelector(`[data-note-id="${noteId}"] .note-pin`);
            pinButton.classList.toggle('pinned');
            
            this.showNotification('Note pin toggled!', 'success');
        } catch (error) {
            this.showNotification('Failed to pin note', 'error');
        }
    }

    async toggleNoteFavorite(noteId) {
        try {
            await fetch(`/api/notes/${noteId}/favorite`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const favoriteButton = document.querySelector(`[data-note-id="${noteId}"] .note-favorite`);
            favoriteButton.classList.toggle('favorited');
            
            this.showNotification('Note favorite toggled!', 'success');
        } catch (error) {
            this.showNotification('Failed to favorite note', 'error');
        }
    }

    initAutoSave() {
        const textareas = document.querySelectorAll('.auto-save');
        textareas.forEach(textarea => {
            let timeout;
            textarea.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.autoSaveNote(textarea.dataset.noteId, textarea.value);
                }, 2000);
            });
        });
    }

    async autoSaveNote(noteId, content) {
        try {
            await fetch(`/api/notes/${noteId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ content })
            });
            
            this.showNotification('Note auto-saved!', 'info', 1000);
        } catch (error) {
            this.showNotification('Auto-save failed', 'error');
        }
    }

    // UI Animations
    initAnimations() {
        this.initFadeInAnimations();
        this.initHoverEffects();
        this.initLoadingStates();
    }

    initFadeInAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        });

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    }

    initHoverEffects() {
        const cards = document.querySelectorAll('.hover-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-4px)';
                card.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
            });
        });
    }

    initLoadingStates() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn-loading')) {
                e.target.classList.add('loading');
                e.target.disabled = true;
                
                setTimeout(() => {
                    e.target.classList.remove('loading');
                    e.target.disabled = false;
                }, 2000);
            }
        });
    }

    // Real-time Features
    initRealTimeFeatures() {
        this.initWebSocket();
        this.initPeriodicUpdates();
    }

    initWebSocket() {
        // Note: This would require WebSocket server setup
        // For now, we'll use polling
        console.log('WebSocket connection would be initialized here');
    }

    initPeriodicUpdates() {
        // Update dashboard every 5 minutes
        setInterval(() => {
            this.updateDashboardStats();
        }, 300000);
    }

    async updateDashboardStats() {
        try {
            const response = await fetch('/api/dashboard/analytics', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (response.ok) {
                const analytics = await response.json();
                
                // Update stats cards
                if (analytics.stats) {
                    Object.keys(analytics.stats).forEach(key => {
                        const element = document.querySelector(`[data-stat="${key}"]`);
                        if (element) {
                            element.textContent = analytics.stats[key];
                        }
                    });
                }
                
                // Update achievement points if available
                if (analytics.achievements) {
                    const pointsElement = document.querySelector('.total-points');
                    if (pointsElement) {
                        pointsElement.textContent = analytics.achievements.total_points || 0;
                    }
                }
                
                // Refresh charts with new data
                this.destroyCharts();
                setTimeout(() => this.initCharts(), 100);
            }
        } catch (error) {
            console.log('Failed to update dashboard stats:', error);
        }
    }

    // Notifications
    initNotifications() {
        this.createNotificationContainer();
    }

    createNotificationContainer() {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
    }

    showNotification(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        const bgColor = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        }[type];
        
        notification.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0`;
        notification.textContent = message;
        
        const container = document.getElementById('notification-container');
        container.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
        }, 100);
        
        // Remove after duration
        setTimeout(() => {
            notification.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, duration);
    }

    // Service Worker for Offline Support
    initServiceWorker() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('Service Worker registered successfully');
                })
                .catch(error => {
                    console.log('Service Worker registration failed');
                });
        }
    }
}

// Initialize the app
const app = new ProductivityApp();
window.productivityApp = app;
