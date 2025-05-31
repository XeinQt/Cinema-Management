<!-- Fixed Sidebar -->
<div class="bg-[#1a1f2b] text-white transition-all duration-300 fixed left-0 top-0 h-screen z-30 w-64">
    <div class="flex flex-col h-full">
        <!-- Logo Section -->
        <div class="flex items-center p-4">
            <a href="{{ route('dashboard') }}" class="flex items-center">
                <x-application-logo class="block h-8 w-auto fill-current text-white" />
                <span class="ml-3 text-lg font-medium text-white">Cinema</span>
            </a>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 py-4">
            <nav class="space-y-1 px-2">
                <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="ml-4 text-sm">{{ __('Dashboard') }}</span>
                </x-nav-link>

                <x-nav-link href="{{ route('malls') }}" :active="request()->routeIs('malls')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="ml-4 text-sm">{{ __('Mall') }}</span>
                </x-nav-link>

                <x-nav-link href="{{ route('managers') }}" :active="request()->routeIs('managers')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="ml-4 text-sm">{{ __('Manager') }}</span>
                </x-nav-link>

                <x-nav-link href="{{ route('cinemas') }}" :active="request()->routeIs('cinemas')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                    </svg>
                    <span class="ml-4 text-sm">{{ __('Cinemas') }}</span>
                </x-nav-link>

                <x-nav-link href="{{ route('movies') }}" :active="request()->routeIs('movies')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span class="ml-4 text-sm">{{ __('Movies') }}</span>
                </x-nav-link>

                <x-nav-link href="{{ route('screenings') }}" :active="request()->routeIs('screenings')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="ml-4 text-sm">{{ __('Screening') }}</span>
                </x-nav-link>

                <x-nav-link href="{{ route('customers') }}" :active="request()->routeIs('customers')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="ml-4 text-sm">{{ __('Customers') }}</span>
                </x-nav-link>

                <x-nav-link href="{{ route('bookings') }}" :active="request()->routeIs('bookings')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="ml-4 text-sm">{{ __('Booking') }}</span>
                </x-nav-link>
            </nav>
        </div>

        <!-- User Profile Section -->
        <div class="border-t border-gray-700/50 p-4">
            <div x-data="{ dropdownOpen: false }" class="relative">
                <button @click="dropdownOpen = !dropdownOpen" class="flex items-center w-full text-left hover:bg-gray-800/50 rounded-lg p-2 transition-colors duration-200">
                    <div class="flex-shrink-0">
                        <img class="h-8 w-8 rounded-full ring-2 ring-gray-600" src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=random" alt="{{ Auth::user()->name }}">
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
                    </div>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="dropdownOpen" @click.away="dropdownOpen = false" 
                    class="absolute bottom-full left-0 w-full mb-2 transform"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden ring-1 ring-black ring-opacity-5">
                        <x-dropdown-link href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
