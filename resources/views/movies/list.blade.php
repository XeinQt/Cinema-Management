<x-app-layout>
    {{-- Header Section --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Movies Management') }}
        </h2>
    </x-slot>

    {{-- Main Content Section --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <button onclick="openModal()" class="bg-green-500 px-5 py-2 rounded-sm text-white">Add</button>
                    <table class="w-full bg-white shadow-md rounded-lg overflow-hidden" id="moviesTable"></table>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Movies Modal --}}
    <div id="addMoviesModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Add Movies</h2>
            
            <form id="addMovieForm">
                @csrf
                {{-- Movie Title --}}
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Title</label>
                    <input 
                        type="text" 
                        name="title" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter movie title (e.g., The Dark Knight)"
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
                        placeholder="Enter genre (e.g., Action, Drama, Comedy)"
                        required
                    >
                </div>

                {{-- Movie Duration --}}
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Duration</label>
                    <input 
                        type="text" 
                        name="duration" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter duration (e.g., 2h 30m)"
                        required
                    >
                </div>

                {{-- Movie Description --}}
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Description</label>
                    <input 
                        type="text" 
                        name="description" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter brief movie plot or synopsis"
                        required
                    >
                </div>

                {{-- Movie Rating --}}
               <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Rating</label>
                    <select name="rating" class="w-full px-3 py-2 border rounded" required>
                        <option value="">Select Rating</option>
                        <option value="G">G – General Audiences</option>
                        <option value="PG">PG – Parental Guidance Suggested</option>
                        <option value="PG-13">PG-13 – Parents Strongly Cautioned</option>
                        <option value="R">R – Restricted</option>
                        <option value="NC-17">NC-17 – Adults Only</option>
                    </select>
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

    {{-- JavaScript Section --}}
    <script>
        // Modal Element
        const modal = document.getElementById('addMoviesModal');

        // Modal Functions
        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Form Submission Handler
        document.getElementById('addMovieForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
            const response = await fetch("{{ route('movies.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                 Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Mall added successfully.'
                });

                this.reset();
                closeModal();
                mallsDatatables.ajax.reload();
            } else {
                const error = await response.json();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error.message || "Failed to add Movie."
                });
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>
</x-app-layout>
