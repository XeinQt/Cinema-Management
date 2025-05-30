<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Screening Management') }}
        </h2>
    </x-slot>

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
                     <table class="w-full bg-white shadow-md rounded-lg overflow-hidden" id="screeningTable"></table>
                </div>
            </div>
        </div>
    </div>
    <!-- Add screening Modal -->
    <div id="addScreeningModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Add Cinema</h2>
            <form id="addScreeningForm">
                @csrf

                {{-- mall name --}}
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 mb-2">Cinema</label>
                    <select 
                        name="cinemaName" 
                        id="cinema_select" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                    >
                        <option value="">Select Cinema</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Movie</label>
                    <select 
                        name="movieName" 
                        id="movie_select" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                    >
                        <option value="">Select Movie</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="created_at" class="block text-gray-700 dark:text-gray-300 mb-2">Date & Time</label>
                    <input 
                        type="datetime-local" 
                        id="created_at" 
                        name="time" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                    />
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded mr-2">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
<script src="{{ asset('js/utils/custom.js') }}"></script>
<script src="{{ asset('js/screening.js') }}"></script>
<script>
    $(document).ready(function() {
        initializeScreeningTable();
    });
</script>
