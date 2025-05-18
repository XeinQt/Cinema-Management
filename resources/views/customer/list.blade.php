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
                    <table class="w-full bg-white shadow-md rounded-lg overflow-hidden" id="customerTable">
                        
                    </table>


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
