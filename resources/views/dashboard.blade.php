<x-app-layout>

    <!-- Add Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Bookings -->
                    <div class="bg-blue-500 rounded-lg shadow-sm overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white bg-opacity-20">
                                    <i class="fas fa-ticket-alt text-white text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-blue-100">Total Bookings</p>
                                    <p class="text-3xl font-bold text-white">{{ $totalBookings ?? 0 }}</p>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-blue-100">
                                {{ $todayBookings ?? 0 }} new today
                            </p>
                        </div>
                    </div>

                    <!-- Total Customers -->
                    <div class="bg-emerald-500 rounded-lg shadow-sm overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white bg-opacity-20">
                                    <i class="fas fa-users text-white text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-emerald-100">Total Customers</p>
                                    <p class="text-3xl font-bold text-white">{{ $totalCustomers ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Movies -->
                    <div class="bg-purple-500 rounded-lg shadow-sm overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white bg-opacity-20">
                                    <i class="fas fa-film text-white text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-purple-100">Total Movies</p>
                                    <p class="text-3xl font-bold text-white">{{ $totalMovies ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Cinemas -->
                    <div class="bg-rose-500 rounded-lg shadow-sm overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white bg-opacity-20">
                                    <i class="fas fa-building text-white text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-rose-100">Total Cinemas</p>
                                    <p class="text-3xl font-bold text-white">{{ $totalCinemas ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Booking Status Chart -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Booking Status Distribution</h3>
                            <canvas id="bookingStatusChart" class="w-full" height="300"></canvas>
                        </div>
                    </div>

                    <!-- Popular Movies -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Most Popular Movies</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-100">
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Movie</th>
                                            <th class="px-4 py-2 text-right text-sm font-medium text-gray-500">Bookings</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($popularMovies ?? [] as $movie)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $movie->title }}</td>
                                            <td class="px-4 py-2 text-sm text-right text-gray-900">{{ $movie->booking_count }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-2 text-sm text-center text-gray-500">No movie data available</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Bookings -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden col-span-1 lg:col-span-2">
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Bookings</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-100">
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Customer</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Movie</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Status</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentBookings ?? [] as $booking)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $booking->customer_name }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $booking->movie_title }}</td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                    {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-700' : 
                                                       ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                                    {{ ucfirst($booking->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($booking->created_at)->format('M d, Y H:i') }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-2 text-sm text-center text-gray-500">No recent bookings</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Screenings -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden col-span-1 lg:col-span-2">
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Screenings</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-100">
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Movie</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Cinema</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Date & Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($upcomingScreenings as $screening)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $screening->movie_title }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $screening->cinema_name }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
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
    </div>

    <script>
        // Initialize Booking Status Chart only if data is available
        @if(isset($bookingStats) && $bookingStats->count() > 0)
        const bookingStatusChart = new Chart(
            document.getElementById('bookingStatusChart'),
            {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($bookingStats->pluck('status')) !!},
                    datasets: [{
                        data: {!! json_encode($bookingStats->pluck('total')) !!},
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.9)',  // green for confirmed
                            'rgba(234, 179, 8, 0.9)',  // yellow for pending
                            'rgba(239, 68, 68, 0.9)',  // red for cancelled
                        ],
                        borderColor: [
                            'rgb(34, 197, 94)',
                            'rgb(234, 179, 8)',
                            'rgb(239, 68, 68)',
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                },
                                color: 'rgb(55, 65, 81)'
                            }
                        }
                    },
                    cutout: '70%'
                }
            }
        );
        @endif
    </script>
</x-app-layout>
