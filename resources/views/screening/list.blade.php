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
                   
                    <button onclick="openModal()" class="bg-green-500 px-5 py-2 rounded-sm text-white">Add</button>
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
<script>
function closeModal() {
    document.getElementById('addScreeningModal').classList.remove('flex');
    document.getElementById('addScreeningModal').classList.add('hidden');
}
function openModal() {
    document.getElementById('addScreeningModal').classList.remove('hidden');
    document.getElementById('addScreeningModal').classList.add('flex');
    populateDropdowns();
}

// Function to populate cinema and movie dropdowns
async function populateDropdowns() {
    try {
        // Fetch cinemas
        const cinemasResponse = await fetch('/CinemasManagement/DataTables');
        const cinemasData = await cinemasResponse.json();
        const cinemaSelect = document.getElementById('cinema_select');
        
        // Clear existing options except the first one
        while (cinemaSelect.options.length > 1) {
            cinemaSelect.remove(1);
        }
        
        cinemasData.data.forEach(cinema => {
            const option = document.createElement('option');
            option.value = cinema.name;  // Using name as value since ScreeningController expects name
            option.textContent = cinema.name;
            cinemaSelect.appendChild(option);
        });

        // Fetch movies
        const moviesResponse = await fetch('/MoviesManagement/DataTables');
        const moviesData = await moviesResponse.json();
        const movieSelect = document.getElementById('movie_select');
        
        // Clear existing options except the first one
        while (movieSelect.options.length > 1) {
            movieSelect.remove(1);
        }
        
        moviesData.data.forEach(movie => {
            const option = document.createElement('option');
            option.value = movie.title;  // Using title as value since ScreeningController expects title
            option.textContent = movie.title;
            movieSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading dropdowns:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load cinema and movie data'
        });
    }
}

document.getElementById('addScreeningForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;

    const formData = new FormData(form);

    try {
        const res = await fetch("{{ route('screenings.store') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json",
            },
            body: formData,
        });

        if (!res.ok) {
            const errData = await res.json();
            throw errData;
        }

        const data = await res.json();

        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'Success' : 'Error',
            text: data.message
        });

        if (data.success) {
            closeModal();
            form.reset();
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: err.message || 'Something went wrong!'
        });
    } finally {
        submitButton.disabled = false;
    }
});

</script>