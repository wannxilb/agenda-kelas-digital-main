{{-- resources/views/layouts/super_admin.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name', 'Agenda Digital') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 font-['Inter']" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" x-cloak>
    @include('partials.global-skeleton')
    <div class="flex flex-col h-screen overflow-hidden">
        <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar -->
        <aside 
            x-show="sidebarOpen" 
            @resize.window="sidebarOpen = window.innerWidth >= 1024"
            x-transition:enter="transition ease-in-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            @click.away="sidebarOpen = window.innerWidth < 1024 ? false : true"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-2xl lg:relative lg:translate-x-0 lg:block"
            :class="{'hidden': !sidebarOpen && window.innerWidth < 1024, 'lg:block': true}">
            <div class="flex flex-col h-full">
                <!-- Logo Section -->
                <div class="flex items-center justify-between px-6 py-6 border-b border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-linear-to-br from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">{{ config('app.name', 'Platform Manager') }}</h1>
                            <p class="text-[10px] font-bold text-purple-600 uppercase tracking-widest">Super Admin</p>
                        </div>
                    </div>
                    <button @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-600 lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto scrollbar-hide">
                    <div class="mb-4 px-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('messages.main_menu') }}</p>
                    </div>

                    <a href="{{ route('super-admin.dashboard') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-purple-50 hover:text-purple-600 transition-all group {{ request()->routeIs('super-admin.dashboard') ? 'bg-purple-50 text-purple-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('super-admin.dashboard') ? 'text-purple-600' : 'text-gray-400 group-hover:text-purple-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span class="ml-3 font-medium">{{ __('messages.dashboard') }}</span>
                        @if(request()->routeIs('super-admin.dashboard'))
                            <span class="ml-auto w-1.5 h-8 bg-purple-600 rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('super-admin.institutions.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-all group {{ request()->routeIs('super-admin.institutions.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('super-admin.institutions.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="ml-3 font-medium">{{ __('messages.instansi_management') }}</span>
                        @if(request()->routeIs('super-admin.institutions.*'))
                            <span class="ml-auto w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('super-admin.admins.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-green-50 hover:text-green-600 transition-all group {{ request()->routeIs('super-admin.admins.*') ? 'bg-green-50 text-green-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('super-admin.admins.*') ? 'text-green-600' : 'text-gray-400 group-hover:text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="ml-3 font-medium">{{ __('messages.admin_management') }}</span>
                        @if(request()->routeIs('super-admin.admins.*'))
                            <span class="ml-auto w-1.5 h-8 bg-green-600 rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('super-admin.settings') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-gray-100 hover:text-gray-900 transition-all group {{ request()->routeIs('super-admin.settings*') ? 'bg-gray-100 text-gray-900' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('super-admin.settings*') ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-900' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="ml-3 font-medium">{{ __('messages.system_settings') }}</span>
                        @if(request()->routeIs('super-admin.settings*'))
                            <span class="ml-auto w-1.5 h-8 bg-gray-900 rounded-full"></span>
                        @endif
                    </a>
                    <a href="{{ route('super-admin.audit-logs.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-yellow-50 hover:text-yellow-600 transition-all group {{ request()->routeIs('super-admin.audit-logs.*') ? 'bg-yellow-50 text-yellow-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('super-admin.audit-logs.*') ? 'text-yellow-600' : 'text-gray-400 group-hover:text-yellow-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <span class="ml-3 font-medium">{{ __('messages.audit_logs') }}</span>
                        @if(request()->routeIs('super-admin.audit-logs.*'))
                            <span class="ml-auto w-1.5 h-8 bg-yellow-600 rounded-full"></span>
                        @endif
                    </a>
                </nav>
                
                <!-- User Menu -->
                <div class="p-4 border-t border-gray-100">
                    <div class="flex items-center space-x-3">
                        @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 rounded-full object-cover shadow-md">
                        @else
                            <div class="w-10 h-10 bg-linear-to-br from-purple-500 to-indigo-500 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', Auth::user()->roles->first()?->name ?? 'Admin') }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Overlay -->
        <div x-show="sidebarOpen && window.innerWidth < 1024" 
             x-transition:enter="transition-opacity ease-in-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in-out duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden">
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="bg-white shadow-sm sticky top-0 z-30">
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 lg:hidden">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                            <h2 class="text-xl font-semibold text-gray-800">@yield('header', config('app.name', 'Platform Manager'))</h2>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <!-- Search -->
                            <div class="hidden md:block">
                                <form action="{{ route('super-admin.search') }}" method="GET" class="relative">
                                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('messages.search') }}" 
                                           class="w-80 pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <button type="submit" class="absolute left-3 top-2.5 text-gray-400 hover:text-purple-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>

                            <!-- Profile Dropdown -->
                            <div class="relative" x-data="{ profileOpen: false }">
                                <button @click="profileOpen = !profileOpen" class="flex items-center space-x-2 focus:outline-none">
                                    @if(Auth::user()->avatar)
                                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 bg-linear-to-br from-purple-500 to-indigo-500 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="hidden md:block text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                                    <svg class="hidden md:block w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="profileOpen" x-cloak
                                     @click.away="profileOpen = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg py-1 border border-gray-100 z-50 overflow-hidden">
                                    <a href="{{ route('super-admin.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('messages.settings') }}</a>
                                    @include('partials.period-switcher')
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                            {{ __('messages.logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 scrollbar-hide">
                <div class="px-4 sm:px-6 lg:px-8 py-8">
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                            <div class="flex">
                                <div class="shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                            <div class="flex">
                                <div class="shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                            <div class="flex">
                                <div class="shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <ul class="list-disc list-inside text-sm text-red-700">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    @include('partials.period-banner')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    </div>
    @stack('scripts')
</body>
</html>
