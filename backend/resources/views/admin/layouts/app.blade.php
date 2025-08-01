<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data :class="{ 'dark': $store.darkMode.on }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} Admin - @yield('title')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Additional Styles -->
    @stack('styles')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
</head>
<body class="h-full font-sans antialiased">
    @auth('admin')
        <div x-data="{ mobileMenuOpen: false }">
            <!-- Mobile menu -->
            <div 
                x-show="mobileMenuOpen" 
                class="relative z-50 lg:hidden" 
                x-ref="dialog" 
                aria-modal="true"
            >
                <!-- Background backdrop -->
                <div 
                    x-show="mobileMenuOpen"
                    x-transition:enter="transition-opacity ease-linear duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-linear duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-900/80"
                    @click="mobileMenuOpen = false"
                ></div>

                <div class="fixed inset-0 flex">
                    <!-- Mobile menu panel -->
                    <div 
                        x-show="mobileMenuOpen"
                        x-transition:enter="transition ease-in-out duration-300 transform"
                        x-transition:enter-start="-translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transition ease-in-out duration-300 transform"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="-translate-x-full"
                        class="relative mr-16 flex w-full max-w-xs flex-1"
                    >
                        <!-- Close button -->
                        <div 
                            x-show="mobileMenuOpen"
                            x-transition:enter="ease-in-out duration-300"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in-out duration-300"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="absolute left-full top-0 flex w-16 justify-center pt-5"
                        >
                            <button type="button" class="-m-2.5 p-2.5" @click="mobileMenuOpen = false">
                                <span class="sr-only">Close sidebar</span>
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Sidebar component for mobile -->
                        @include('admin.partials.sidebar')
                    </div>
                </div>
            </div>

            <!-- Static sidebar for desktop -->
            <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
                @include('admin.partials.sidebar')
            </div>

            <div class="lg:pl-72">
                @include('admin.partials.navbar')
                
                <main class="py-10">
                    <div class="px-4 sm:px-6 lg:px-8">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    @else
        @yield('content')
    @endauth

    @stack('scripts')
    @include('components.notification')

    <!-- Add this script to trigger notification -->
    @if(session('notification'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification(
                '', // title (empty as per your notification component)
                '{{ session("notification")["message"] }}',
                '{{ session("notification")["type"] }}'
            );
        });
    </script>
    @endif
</body>
</html> 