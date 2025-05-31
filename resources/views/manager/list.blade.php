<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manager Management') }}
        </h2>
    </x-slot>
     <!-- Add DataTables CSS -->
     <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <!-- Add Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    

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

                    <table class="w-full bg-white shadow-md rounded-lg overflow-hidden" id="managerTable"></table>

                </div>
            </div>
        </div>
    </div>

    <!-- add Modal -->
    <div id="addManagerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Add Manager</h2>
            <form id="addManagerForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">First Name</label>
                    <input 
                        type="text" 
                        name="first_name" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter first name (e.g., John)"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Last Name</label>
                    <input 
                        type="text" 
                        name="last_name" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter last name (e.g., Smith)"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="example@email.com"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Phone No.</label>
                    <input 
                        type="text" 
                        name="phonenumber" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="+63 912 345 6789"
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

    {{-- editmodal --}}
    <div id="editManagerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Edit Manager</h2>
            <form id="editManagerForm">
                @csrf
                <input type="hidden" name="manager_id" id="edit_manager_id">
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">First Name</label>
                    <input 
                        type="text" 
                        name="first_name" 
                        id="edit_first_name"
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter first name (e.g., John)"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Last Name</label>
                    <input 
                        type="text" 
                        name="last_name" 
                        id="edit_last_name"
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter last name (e.g., Smith)"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Email</label>
                    <input 
                        type="text" 
                        name="email" 
                        id="edit_email"
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="example@email.com"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Phone No.</label>
                    <input 
                        type="text" 
                        name="phonenumber" 
                        id="edit_phonenumber"
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="+63 912 345 6789"
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/utils/custom.js') }}"></script>
    <script src="{{ asset('js/manager.js') }}"></script>
    <script>
        $(document).ready(function() {
            initializeManagerTable();
        });
    </script>

</x-app-layout>
