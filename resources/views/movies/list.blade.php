<x-app-layout>
    {{-- Header Section --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Movies Management') }}
        </h2>
    </x-slot>
     <!-- Add DataTables CSS -->
     <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <!-- Add Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Add Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    
  
    

    {{-- Main Content Section --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="flex items-center space-x-4 mb-6">
                        <button onclick="openModal()" class="bg-green-500 hover:bg-green-600 px-5 py-2 rounded-sm text-white">Add</button>
                        
                        <div class="flex items-center space-x-2">
                            <label for="filter" class="text-gray-700 dark:text-gray-300">Filter</label>
                            <select name="filter" id="filter" class="w-32 px-3 py-2 border rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <table class="w-full bg-white shadow-md rounded-lg overflow-hidden" id="moviesDatatables"></table>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Movies Modal --}}
    <div id="addMovieModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Add Movie</h2>
            
            <form id="addMovieForm">
                @csrf
                {{-- Movie Title --}}
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 mb-2">Title</label>
                    <input 
                        type="text" 
                        name="title" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter movie title"
                        required
                    >
                </div>

                {{-- Movie Genre --}}
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Genre</label>
                    <input 
                        type="text" 
                        name="genre" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter movie genre"
                        required
                    >
                </div>

                {{-- Movie Duration --}}
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Duration (minutes)</label>
                    <input 
                        type="text" 
                        name="duration" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter duration in minutes"
                        required
                        min="1"
                    >
                </div>

                {{-- Movie Description --}}
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Description</label>
                    <textarea 
                        name="description" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter movie description"
                        rows="3"
                        required
                    ></textarea>
                </div>

                {{-- Movie Rating --}}
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Rating</label>
                    <input 
                        type="text" 
                        name="rating" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter movie rating"
                        required
                    >
                </div>

                {{-- Form Actions --}}
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 bg-gray-500 text-white rounded mr-2">Cancel</button>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- edit modal --}}
    <div id="editMovieModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Edit Movie</h2>
            <form id="editMovieForm">
                @csrf
                <input type="hidden" id="edit_movie_id" name="movie_id">
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 mb-2">Title</label>
                    <input 
                        type="text" 
                        name="title" 
                        id="edit_title"
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter movie title"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Genre</label>
                    <input 
                        type="text" 
                        name="genre" 
                        id="edit_genre"
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter movie genre"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Duration (minutes)</label>
                    <input 
                        type="text" 
                        name="duration" 
                        id="edit_duration"
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter duration in minutes"
                        required
                        min="1"
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Description</label>
                    <textarea 
                        name="description" 
                        id="edit_description"
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter movie description"
                        rows="3"
                        required
                    ></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Rating</label>
                    <input 
                        type="text" 
                        name="rating" 
                        id="edit_rating"
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter movie rating"
                        required
                    >
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-500 text-white rounded mr-2">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- JavaScript Section --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/utils/custom.js') }}"></script>
    <script src="{{ asset('js/movie.js') }}"></script>
    <script>
        $(document).ready(function() {
            initializeMovieTable();
        });
    </script>
</x-app-layout>
