{{-- <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mall Management') }}
        </h2>
    </x-slot>
    <!-- Add DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <!-- Add Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    
</x-app-layout> --}}

<x-app-layout>


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

                    <table class="w-full bg-white shadow-md rounded-lg overflow-hidden mt-4" id="mallsDatatables"></table>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="addMallModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Add Mall</h2>
            <form id="addMallForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Mall Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter mall name (e.g., SM Mall of Asia, Ayala Mall)"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Location</label>
                    <input 
                        type="text" 
                        name="location" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="123 Main Street, City, Province, ZIP Code"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Description</label>
                    <input 
                        type="text" 
                        name="description" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="A premier shopping destination with entertainment facilities"
                        required
                    >
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded mr-2">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editMallModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Edit Mall</h2>
            <form id="editMallForm">
                @csrf
                <input type="hidden" name="mall_id" id="edit_mall_id">
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Mall Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        id="edit_name"
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter mall name (e.g., SM Mall of Asia, Ayala Mall)"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Location</label>
                    <input 
                        type="text" 
                        name="location" 
                        id="edit_location"
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="123 Main Street, City, Province, ZIP Code"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Description</label>
                    <textarea 
                        type="text" 
                        name="description" 
                        id="edit_description"
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="A premier shopping destination with entertainment facilities"
                        required
                    ></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-500 text-white rounded mr-2">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('js/utils/custom.js') }}"></script>
    <script src="{{ asset('js/mall.js') }}"></script>
    <script>
        // Initialize DataTable     
        $(document).ready(function() {
            initializeMallTable();
        });

    </script>
</x-app-layout>
