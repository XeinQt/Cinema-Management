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

    // Function to populate cinema and movie dropdowns
    async function populateDropdowns() {
        try {
            // Fetch cinemas
            const cinemasResponse = await fetch('/CinemasManagement/DataTables');
            const cinemasData = await cinemasResponse.json();
            const cinemaSelect = document.getElementById('cinema_select');
            
            cinemasData.data.forEach(cinema => {
                const option = document.createElement('option');
                option.value = cinema.cinema_id;
                option.textContent = cinema.name;
                cinemaSelect.appendChild(option);
            });

            // Fetch movies
            const moviesResponse = await fetch('/MoviesManagement/DataTables');
            const moviesData = await moviesResponse.json();
            const movieSelect = document.getElementById('movie_select');
            
            moviesData.data.forEach(movie => {
                const option = document.createElement('option');
                option.value = movie.movie_id;
                option.textContent = movie.title;
                movieSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading dropdowns:', error);
        }
    }

    // Call populateDropdowns when opening the modal
    document.querySelector('button.bg-green-500').addEventListener('click', () => {
        openModal();
        populateDropdowns();
    });

    // Add event listeners for movie and cinema selection
    document.getElementById('cinema_select').addEventListener('change', fetchScreeningTimes);
    document.getElementById('movie_select').addEventListener('change', fetchScreeningTimes);

    // Function to fetch and populate screening times
    async function fetchScreeningTimes() {
        const cinemaId = document.getElementById('cinema_select').value;
        const movieId = document.getElementById('movie_select').value;
        const timeSelect = document.getElementById('screening_time');
        
        // Reset and disable time dropdown if either cinema or movie is not selected
        if (!cinemaId || !movieId) {
            timeSelect.innerHTML = '<option value="">Select movie and cinema first</option>';
            timeSelect.disabled = true;
            return;
        }

        try {
            const response = await fetch('/ScreeningsManagement/DataTables');
            const data = await response.json();
            
            // Filter screenings for selected cinema and movie
            const availableScreenings = data.data.filter(screening => 
                screening.cinema_id == cinemaId && 
                screening.movie_id == movieId
            );

            // Clear and populate time dropdown
            timeSelect.innerHTML = '<option value="">Select screening time</option>';
            
            if (availableScreenings.length === 0) {
                timeSelect.innerHTML += '<option value="" disabled>No screenings available</option>';
            } else {
                availableScreenings.forEach(screening => {
                    const screeningTime = new Date(screening.screening_time);
                    const formattedTime = screeningTime.toLocaleString('en-US', {
                        weekday: 'short',
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    timeSelect.innerHTML += `
                        <option value="${screening.screening_time}">
                            ${formattedTime}
                        </option>
                    `;
                });
            }
            
            timeSelect.disabled = false;
        } catch (error) {
            console.error('Error fetching screening times:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load screening times'
            });
        }
    }
</script>
