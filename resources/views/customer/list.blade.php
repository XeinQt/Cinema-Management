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
                    
                    <button class="bg-green-500 px-5 py-2 rounded-sm text-white">Add</button>
                    <table class="w-full bg-white shadow-md rounded-lg overflow-hidden" id="mallsTable">
                        <thead class="bg-gray-100 text-gray-600 uppercase text-sm">
                            <tr>
                                <th class="py-3 px-6 text-left">CUSTOMER ID</th>
                                <th class="py-3 px-6 text-left">FIRSTNAME</th>
                                <th class="py-3 px-6 text-left">LASTNAME ID</th>
                                <th class="py-3 px-6 text-left">EMAIL</th>
                                <th class="py-3 px-6 text-left">PHONE NO.</th>
                                <th class="py-3 px-6 text-left">ACTIONS</th>
                                
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($customers as $customer)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-6">{{ $customer->customer_id }}</td>
                                <td class="py-3 px-6">{{ $customer->first_name }}</td>
                                <td class="py-3 px-6">{{ $customer->last_name }}</td>
                                <td class="py-3 px-6">{{ $customer->email }}</td>
                                <td class="py-3 px-6">{{ $customer->phonenumber }}</td>

                                <td class="py-3 px-6 flex gap-2">
                                        <button class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">Edit</button>
                                        <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">Delete</button>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
