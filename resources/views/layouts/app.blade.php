<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Cine Management System') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://cdn.datatables.net/2.3.0/css/dataTables.dataTables.min.css" rel="stylesheet" />
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        <!-- sweet js -->
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
        
        <!-- Custom css -->
        <link rel="stylesheet" href={{ asset('css/custom.css') }}>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script src="https://cdn.tailwindcss.com"></script>

        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ open: true }" class="min-h-screen flex bg-gray-100 dark:bg-gray-900">
            <!-- Include Navigation -->
            @include('layouts.navigation')

            <!-- Main Content Area -->
            <div :class="{'ml-64': open, 'ml-16': !open}" class="flex-1 flex flex-col transition-all duration-300">
                <!-- Mobile Navigation Toggle -->
                <div class="sm:hidden">
                    <button @click="open = !open" class="p-4 focus:outline-none hover:bg-gray-100 transition-colors duration-200" :class="{'ml-16 w-16 flex justify-center': !open}">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Main Header -->
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                @php
                                    $routeName = Route::currentRouteName();
                                    $headerText = 'Dashboard'; // Default text

                                    switch($routeName) {
                                        case 'dashboard':
                                            $headerText = 'Dashboard';
                                            break;
                                        case 'malls':
                                            $headerText = 'Mall Management';
                                            break;
                                        case 'managers':
                                            $headerText = 'Manager Management';
                                            break;
                                        case 'cinemas':
                                            $headerText = 'Cinema Management';
                                            break;
                                        case 'movies':
                                            $headerText = 'Movie Management';
                                            break;
                                        case 'screenings':
                                            $headerText = 'Screening Management';
                                            break;
                                        case 'customers':
                                            $headerText = 'Customer Management';
                                            break;
                                        case 'bookings':
                                            $headerText = 'Booking Management';
                                            break;
                                    }
                                @endphp
                                <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $headerText }}
                                </h1>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::now()->format('l, F j, Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-gray-900">
                    {{ $slot }}
                </main>
            </div>
        </div>
        
        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="{{ asset('js/utils/custom.js') }}"></script>
    </body>
</html>
