<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Customer Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <button onclick="openModal()" class="bg-green-500 px-5 py-2 rounded-sm text-white">Add</button>
                    <table class="w-full bg-white shadow-md rounded-lg overflow-hidden" id="customerTable"></table>
                </div>
            </div>
        </div>
    </div>

    {{-- modal --}}
     <div id="addCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-md w-full max-w-md">
            <h2 class="text-xl mb-4 text-gray-800 dark:text-gray-100">Add Customer</h2>
            <form id="addCustomerForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">First Name</label>
                    <input 
                        type="text" 
                        name="first_name" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter first name (e.g., Juan)"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Last Name</label>
                    <input 
                        type="text" 
                        name="last_name" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter last name (e.g., Dela Cruz)"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        class="w-full px-3 py-2 border rounded" 
                        placeholder="Enter email (e.g., juan.delacruz@email.com)"
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
</x-app-layout>
<script>
    const modal = document.getElementById("addCustomerModal");

    function openModal() {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    }

    function closeModal() {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    }

    // AJAX submit
    document.getElementById("addCustomerForm").addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch("{{ route('customers.store') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Customer added successfully.'
                });

                this.reset();
                closeModal();
            } else {
                // Handle validation or duplicate error
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: data.message || "Failed to add Customer.",
                });
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            Swal.fire({
                icon: "error",
                title: "Unexpected Error",
                text: "Something went wrong. Please try again.",
            });
        }
    });
</script>
