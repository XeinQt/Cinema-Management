<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cinemas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                   
                    <button onclick="openModal()" class="bg-green-500 px-5 py-2 rounded-sm text-white">Add</button>
                    <table class="w-full bg-white shadow-md rounded-lg overflow-hidden" id="cinemaTable"></table>

                </div>
            </div>
        </div>
    </div>


    <!-- Add Cinema Modal -->
    <div id="addCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Add Cinema</h2>
            <form id="addCinemaForm">
                @csrf

                {{-- mall name --}}
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 mb-2">Mall name</label>
                    <input type="text" name="name" id="name" class="w-full px-3 py-2 border rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Manager Name</label>
                    <input type="text" name="manager_full_name" class="w-full px-3 py-2 border rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Cinema Name</label>
                    <input type="text" name="cinema_name" class="w-full px-3 py-2 border rounded" required>
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
    document.getElementById('addCustomerModal').classList.remove('flex');
    document.getElementById('addCustomerModal').classList.add('hidden');
}
function openModal() {
    document.getElementById('addCustomerModal').classList.remove('hidden');
    document.getElementById('addCustomerModal').classList.add('flex');
}

document.getElementById('addCinemaForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    try {
        const res = await fetch("{{ route('cinemas.store') }}", {
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
    }
});


</script>



