<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Booking Management') }}
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

                     <table class="w-full bg-white shadow-md rounded-lg overflow-hidden" id="bookingTable"></table>

                </div>
            </div>
        </div>
    </div>

    <!-- Add booking Modal -->
    <div id="addBookingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Add Booking</h2>

            <form id="addBooking">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 mb-2">Customer Full Name</label>
                    <input 
                        type="text" 
                        name="customer_full_name" 
                        id="customer_full_name" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter first and last name"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Cinema</label>
                    <select 
                        name="cinema_name" 
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
                        name="movie_title" 
                        id="movie_select" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                    >
                        <option value="">Select Movie</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 mb-2">Date & Time</label>
                    <select 
                        name="time" 
                        id="screening_time" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                        disabled
                    >
                        <option value="">Select movie and cinema first</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Seat Number</label>
                    <input 
                        type="text" 
                        name="seat_number" 
                        id="seat_number" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter seat number (e.g., A1, B12, C5)"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border rounded" required>
                        <option value="">Select Status</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="peding">Pending</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
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
                    <label class="block text-gray-700 dark:text-gray-300 mb-2">Customer Full Name</label>
                    <input 
                        type="text" 
                        name="customer_full_name" 
                        id="edit_customer_full_name" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter first and last name"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Cinema</label>
                    <select 
                        name="cinema_name" 
                        id="edit_cinema_select" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                    >
                        <option value="">Select Cinema</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Movie</label>
                    <select 
                        name="movie_title" 
                        id="edit_movie_select" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                    >
                        <option value="">Select Movie</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 mb-2">Date & Time</label>
                    <select 
                        name="time" 
                        id="edit_screening_time" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                        disabled
                    >
                        <option value="">Select movie and cinema first</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Seat Number</label>
                    <input 
                        type="text" 
                        name="seat_number" 
                        id="edit_seat_number" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter seat number (e.g., A1, B12, C5)"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" id="edit_status" class="w-full px-3 py-2 border rounded" required>
                        <option value="">Select Status</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="pending">Pending</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-500 text-white rounded mr-2">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Update</button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/utils/custom.js') }}"></script>
<script src="{{ asset('js/booking.js') }}"></script>
<script>
    $(document).ready(function() {
        initializeBookingTable();
    });
</script>

