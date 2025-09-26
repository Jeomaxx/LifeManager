@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Notes Header -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Notes</h1>
                        <p class="text-gray-600 dark:text-gray-400">Capture and organize your thoughts</p>
                    </div>
                    <div class="flex space-x-4">
                        <button id="add-note-btn" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors btn-loading">
                            <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                            </svg>
                            New Note
                        </button>
                        <button id="export-notes-btn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes Filters and Search -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-4">
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="flex-1 min-w-64">
                        <input type="text" id="note-search" placeholder="Search notes..." class="form-input w-full">
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter:</label>
                        <select id="note-filter" class="form-input rounded-md border-gray-300 text-sm">
                            <option value="all">All Notes</option>
                            <option value="pinned">Pinned</option>
                            <option value="favorited">Favorites</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</label>
                        <select id="note-sort" class="form-input rounded-md border-gray-300 text-sm">
                            <option value="updated">Last Updated</option>
                            <option value="created">Date Created</option>
                            <option value="title">Title A-Z</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button id="grid-view" class="p-2 text-gray-600 hover:text-gray-800 bg-gray-100 rounded">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </button>
                        <button id="list-view" class="p-2 text-gray-600 hover:text-gray-800">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes Grid -->
        <div id="notes-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Pinned Note -->
            <div class="note-item pinned bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card" data-note-id="1">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h3 class="note-title text-lg font-semibold text-gray-900 dark:text-white mb-2">Project Ideas</h3>
                            <div class="note-content text-gray-600 dark:text-gray-400 text-sm mb-3">
                                <p>1. Build a habit tracking app with gamification<br>
                                2. Create a personal finance dashboard<br>
                                3. Develop a workout planning tool<br>
                                4. Design a reading list manager</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-1 ml-4">
                            <button class="note-pin text-yellow-600" data-note-id="1" title="Pinned">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5z"/>
                                </svg>
                            </button>
                            <button class="note-favorite text-gray-400 hover:text-red-600" data-note-id="1" title="Add to favorites">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Updated 2 hours ago</span>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">Ideas</span>
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded">Work</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Favorite Note -->
            <div class="note-item favorited bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card" data-note-id="2">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h3 class="note-title text-lg font-semibold text-gray-900 dark:text-white mb-2">Meeting Notes - Q1 Planning</h3>
                            <div class="note-content text-gray-600 dark:text-gray-400 text-sm mb-3">
                                <p>Key takeaways from Q1 planning meeting:</p>
                                <ul class="list-disc list-inside mt-2">
                                    <li>Focus on user acquisition</li>
                                    <li>Improve app performance</li>
                                    <li>Launch new feature set</li>
                                </ul>
                            </div>
                        </div>
                        <div class="flex items-center space-x-1 ml-4">
                            <button class="note-pin text-gray-400 hover:text-yellow-600" data-note-id="2" title="Pin note">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5z"/>
                                </svg>
                            </button>
                            <button class="note-favorite text-red-600" data-note-id="2" title="Favorited">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Updated yesterday</span>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Meeting</span>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">Planning</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regular Note -->
            <div class="note-item bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card" data-note-id="3">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h3 class="note-title text-lg font-semibold text-gray-900 dark:text-white mb-2">Book Recommendations</h3>
                            <div class="note-content text-gray-600 dark:text-gray-400 text-sm mb-3">
                                <p>Books to read this quarter:</p>
                                <p>• Atomic Habits - James Clear<br>
                                • The Pragmatic Programmer<br>
                                • Deep Work - Cal Newport</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-1 ml-4">
                            <button class="note-pin text-gray-400 hover:text-yellow-600" data-note-id="3" title="Pin note">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5z"/>
                                </svg>
                            </button>
                            <button class="note-favorite text-gray-400 hover:text-red-600" data-note-id="3" title="Add to favorites">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Updated 3 days ago</span>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Books</span>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">Learning</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Note with Rich Content -->
            <div class="note-item bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card" data-note-id="4">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h3 class="note-title text-lg font-semibold text-gray-900 dark:text-white mb-2">Recipe: Homemade Pizza</h3>
                            <div class="note-content text-gray-600 dark:text-gray-400 text-sm mb-3">
                                <p><strong>Ingredients:</strong></p>
                                <p>- 500g bread flour<br>
                                - 325ml warm water<br>
                                - 7g active dry yeast<br>
                                - 2 tsp salt<br>
                                - 2 tbsp olive oil</p>
                                <p class="mt-2"><strong>Instructions:</strong><br>
                                Mix, knead, rise, shape, top, bake!</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-1 ml-4">
                            <button class="note-pin text-gray-400 hover:text-yellow-600" data-note-id="4" title="Pin note">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5z"/>
                                </svg>
                            </button>
                            <button class="note-favorite text-gray-400 hover:text-red-600" data-note-id="4" title="Add to favorites">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Updated 1 week ago</span>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded">Recipe</span>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Cooking</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Note -->
            <div class="note-item bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card" data-note-id="5">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h3 class="note-title text-lg font-semibold text-gray-900 dark:text-white mb-2">Daily Reflection</h3>
                            <div class="note-content text-gray-600 dark:text-gray-400 text-sm mb-3">
                                <p>Today was productive! Completed 3 major tasks and had a great team meeting. Need to follow up on the client feedback tomorrow.</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-1 ml-4">
                            <button class="note-pin text-gray-400 hover:text-yellow-600" data-note-id="5" title="Pin note">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5z"/>
                                </svg>
                            </button>
                            <button class="note-favorite text-gray-400 hover:text-red-600" data-note-id="5" title="Add to favorites">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Updated today</span>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded">Journal</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Note Editor -->
            <div class="note-item bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card" data-note-id="6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h3 class="note-title text-lg font-semibold text-gray-900 dark:text-white mb-2">Quick Note</h3>
                            <div class="note-content mb-3">
                                <textarea class="auto-save w-full p-3 border border-gray-300 rounded-lg resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                          data-note-id="6" 
                                          rows="4" 
                                          placeholder="Start typing your note here..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Auto-saved</span>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded">Draft</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes Statistics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Notes Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Total Notes</span>
                            <span class="font-bold text-blue-600">127</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Pinned</span>
                            <span class="font-bold text-yellow-600">8</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">Favorites</span>
                            <span class="font-bold text-red-600">15</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">This Week</span>
                            <span class="font-bold text-green-600">12</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Popular Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">Work (23)</span>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">Ideas (18)</span>
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm">Learning (15)</span>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">Meeting (12)</span>
                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">Personal (9)</span>
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm">Recipe (7)</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <span class="text-gray-600 dark:text-gray-400">Created "Daily Reflection"</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                            <span class="text-gray-600 dark:text-gray-400">Pinned "Project Ideas"</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                            <span class="text-gray-600 dark:text-gray-400">Favorited "Meeting Notes"</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <span class="text-gray-600 dark:text-gray-400">Updated "Recipe"</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize notes interface functionality
            if (window.productivityApp) {
                window.productivityApp.initNotesInterface();
            }
            
            // View toggle functionality
            document.getElementById('grid-view').addEventListener('click', function() {
                document.getElementById('notes-container').className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
                this.classList.add('bg-gray-100');
                document.getElementById('list-view').classList.remove('bg-gray-100');
            });
            
            document.getElementById('list-view').addEventListener('click', function() {
                document.getElementById('notes-container').className = 'space-y-4';
                this.classList.add('bg-gray-100');
                document.getElementById('grid-view').classList.remove('bg-gray-100');
            });
            
            // Filter functionality
            document.getElementById('note-filter').addEventListener('change', function(e) {
                const filter = e.target.value;
                const notes = document.querySelectorAll('.note-item');
                
                notes.forEach(note => {
                    if (filter === 'all' || note.classList.contains(filter)) {
                        note.style.display = 'block';
                    } else {
                        note.style.display = 'none';
                    }
                });
            });
        });
    </script>
@endsection