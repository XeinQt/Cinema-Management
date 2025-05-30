<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- Add Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- Total Bookings -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-75">
                                <i class="fas fa-ticket-alt text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Bookings</p>
                                <p class="text-2xl font-semibold text-gray-800 dark:text-gray-200">{{ $totalBookings }}</p>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ $todayBookings }} new today
                        </p>
                    </div>
                </div>

                <!-- Total Customers -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-75">
                                <i class="fas fa-users text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Customers</p>
                                <p class="text-2xl font-semibold text-gray-800 dark:text-gray-200">{{ $totalCustomers }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Movies -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 bg-opacity-75">
                                <i class="fas fa-film text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Movies</p>
                                <p class="text-2xl font-semibold text-gray-800 dark:text-gray-200">{{ $totalMovies }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Cinemas -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-500 bg-opacity-75">
                                <i class="fas fa-building text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Cinemas</p>
                                <p class="text-2xl font-semibold text-gray-800 dark:text-gray-200">{{ $totalCinemas }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Tables Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Booking Status Chart -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Booking Status Distribution</h3>
                        <canvas id="bookingStatusChart" class="w-full" height="300"></canvas>
                    </div>
                </div>

                <!-- Popular Movies -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Most Popular Movies</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Movie</th>
                                        <th class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">Bookings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($popularMovies as $movie)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $movie->title }}</td>
                                        <td class="px-4 py-2 text-right text-gray-600 dark:text-gray-400">{{ $movie->booking_count }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg col-span-1 lg:col-span-2">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Recent Bookings</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Customer</th>
                                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Movie</th>
                                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Status</th>
                                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentBookings as $booking)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $booking->customer_name }}</td>
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $booking->movie_title }}</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                {{ $booking->status === 'confirmed' ? 'bg-green-500' : 
                                                   ($booking->status === 'pending' ? 'bg-yellow-500' : 'bg-red-500') }} 
                                                text-white">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($booking->created_at)->format('M d, Y H:i') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Screenings -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg col-span-1 lg:col-span-2">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Upcoming Screenings</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Movie</th>
                                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Cinema</th>
                                        <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingScreenings as $screening)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $screening->movie_title }}</td>
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $screening->cinema_name }}</td>
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($screening->screening_time)->format('M d, Y H:i') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Booking Status Chart
        const bookingStatusChart = new Chart(
            document.getElementById('bookingStatusChart'),
            {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($bookingStats->pluck('status')) !!},
                    datasets: [{
                        data: {!! json_encode($bookingStats->pluck('total')) !!},
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.8)',  // green for confirmed
                            'rgba(234, 179, 8, 0.8)',  // yellow for pending
                            'rgba(239, 68, 68, 0.8)',  // red for cancelled
                        ],
                        borderColor: [
                            'rgb(34, 197, 94)',
                            'rgb(234, 179, 8)',
                            'rgb(239, 68, 68)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: document.querySelector('html').classList.contains('dark') ? 'rgb(229, 231, 235)' : 'rgb(55, 65, 81)'
                            }
                        }
                    }
                }
            }
        );
    </script>
</x-app-layout>
