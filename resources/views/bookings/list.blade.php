<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Booking Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <button class="bg-green-500 px-5 py-2 rounded-sm text-white">Add</button>
                     <table class="w-full bg-white shadow-md rounded-lg overflow-hidden" id="bookingTable"></table>

                </div>
            </div>
        </div>
    </div>

    <!-- Add booking Modal -->
    <div id="addBookingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Add Cinema</h2>
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
                    <label class="block text-gray-700 dark:text-gray-300">Screening ID</label>
                    <input 
                        type="number" 
                        name="screening_id" 
                        id="screening_id" 
                        class="w-full px-3 py-2 border rounded" 
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Seat Number</label>
                    <input 
                        type="text" 
                        name="seat_number" 
                        id="seat_number" 
                        class="w-full px-3 py-2 border rounded" 
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

</x-app-layout>

{{-- JavaScript Section --}}
<script>
    // Modal Element
    const modal = document.getElementById('addBookingModal');

    // Modal Functions
    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Form Submission
    document.getElementById('addBooking').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch("{{ route('bookings.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message
                });

                this.reset();
                closeModal();
                if (typeof bookingsTable !== 'undefined') {
                    bookingsTable.ajax.reload();
                }
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to create booking'
            });
        }
    });

    // Connect add button to modal
    document.querySelector('button.bg-green-500').addEventListener('click', openModal);
</script>
