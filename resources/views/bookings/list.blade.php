<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Booking Management') }}
        </h2>
    </x-slot>

    <!-- Add DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Add Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">


    <style>
        .edit-booking  {
            margin-left: 2px;
        }


    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="flex items-center space-x-4 mb-6">
                        <button onclick="openModal()" class="bg-green-500 hover:bg-green-600 px-5 py-2 rounded-sm text-white">Add</button>
                        <div class="flex items-center space-x-2">
                            <label for="active_filter" class="text-gray-700 dark:text-gray-300">Filter Active</label>
                            <select name="active_filter" id="active_filter" class="w-32 px-3 py-2 border rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <label for="status_filter" class="text-gray-700 dark:text-gray-300">Filter Status</label>
                            <select name="status_filter" id="status_filter" class="w-32 px-3 py-2 border rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">All</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                    </div>

                    <table class="w-full bg-white shadow-md rounded-lg overflow-hidden" id="bookingTable"></table>

                </div>
            </div>
        </div>
    </div>

    <!-- Add Booking Modal -->
    <div id="addBookingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Add Booking</h2>

            <form id="addBookingForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Customer</label>
                    <select 
                        name="customer_id" 
                        id="customer_select" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                    >
                        <option value="">Select Customer</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Screening</label>
                    <select 
                        name="screening_id" 
                        id="screening_select" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                    >
                        <option value="">Select Screening</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Number of Seats</label>
                    <input 
                        type="text" 
                        name="set_number" 
                        id="seats" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter number of seats"
                        min="1"
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

    <!-- Edit Booking Modal -->
    <div id="editBookingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Edit Booking</h2>

            <form id="editBookingForm">
                @csrf
                <input type="hidden" name="booking_id" id="edit_booking_id">

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Customer</label>
                    <select 
                        name="customer_id" 
                        id="edit_customer_select" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                    >
                        <option value="">Select Customer</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Screening</label>
                    <select 
                        name="screening_id" 
                        id="edit_screening_select" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                    >
                        <option value="">Select Screening</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Number of Seats</label>
                    <input 
                        type="text" 
                        name="set_number" 
                        id="edit_seats" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter number of seats"
                        min="1"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Status</label>
                    <select 
                        name="status" 
                        id="edit_status" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                    >
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-500 text-white rounded mr-2">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <!-- Add SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Add Custom JS -->
    <script src="{{ asset('js/booking.js') }}"></script>
</x-app-layout>

