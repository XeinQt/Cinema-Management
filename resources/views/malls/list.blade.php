{{-- <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mall Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                <button class="bg-green-500 px-5 py-2 rounded-sm text-white">Add</button>
               <table class="w-full bg-white shadow-md rounded-lg overflow-hidden" id="mallsDatatables">

                </table>

                </div>
            </div>
        </div>
    </div>
</x-app-layout> --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mall Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <button onclick="openModal()" class="bg-green-500 px-5 py-2 rounded-sm text-white">Add</button>

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
                    <input type="text" name="name" class="w-full px-3 py-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Location</label>
                    <input type="text" name="location" class="w-full px-3 py-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Description</label>
                    <input type="text" name="description" class="w-full px-3 py-2 border rounded" required>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded mr-2">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JS Script -->
    <script>
        const modal = document.getElementById('addMallModal');

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // AJAX submit
        document.getElementById('addMallForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            const response = await fetch("{{ route('malls.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            });

            if (response.ok) {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Mall Added Successfully!",
                    showConfirmButton: false,
                    timer: 1500
                });

                this.reset();
                closeModal();
                mallsDatatables.ajax.reload();
            } else {
                const error = await response.json();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error.message || "Failed to add mall."
                });
                
            }
        });
    </script>
</x-app-layout>
