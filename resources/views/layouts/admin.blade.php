{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $inst = \App\Models\Institution::find(auth()->user()->institution_id ?? null); @endphp
    @if($inst && $inst->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $inst->favicon) }}">
    @endif
    <title>@yield('title') - Agenda Kelas Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
    @stack('styles')
    <style>
        html::-webkit-scrollbar, body::-webkit-scrollbar { display: none !important; }
        html, body { -ms-overflow-style: none !important; scrollbar-width: none !important; }
        
        [x-cloak] { display: none !important; }
        .scrollbar-hide::-webkit-scrollbar {
            display: none !important;
        }
        .scrollbar-hide {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
        .scrollbar-hide::-moz-scrollbar {
            display: none !important;
        }
        .ts-wrapper .ts-control {
            border-radius: 0.75rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            min-height: 42px;
            border: 1px solid #e5e7eb;
        }
        .ts-wrapper .ts-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99,102,241,0.15);
        }
        .ts-wrapper .ts-dropdown {
            border-radius: 0.75rem;
            z-index: 9999;
        }
        .ts-dropdown-content {
            max-height: 220px;
            overflow-y: auto;
        }
        .ts-dropdown-content::-webkit-scrollbar {
            display: none !important;
        }
        .ts-dropdown-content {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
        .ts-dropdown {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
        .ts-dropdown::-webkit-scrollbar {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-50 font-['Inter']" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" x-cloak>
    @include('partials.global-skeleton')
    <div class="flex flex-col h-screen overflow-hidden">
        <div class="flex flex-1 overflow-hidden min-h-0">
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
            class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-2xl lg:translate-x-0 overflow-hidden"
            :class="{'hidden': !sidebarOpen && window.innerWidth < 1024}">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-between px-6 py-6 border-b border-gray-100">
                    <div class="flex items-center space-x-3">
                        @if($inst && $inst->logo)
                            <img src="{{ asset('storage/' . $inst->logo) }}" alt="Logo" class="w-10 h-10 rounded-xl object-contain shadow-lg">
                        @else
                        <div class="w-10 h-10 bg-linear-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        @endif
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">
                                {{ \App\Models\Setting::get('school_name', 'Agenda') }}<span class="text-blue-600">{{ \App\Models\Setting::get('school_name') ? '' : 'Digital' }}</span>
                            </h1>
                            <p class="text-xs text-gray-500">{{ __('messages.school_information_system') }}</p>
                        </div>
                    </div>
                    <button @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-600 lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Navigation -->
                @php
                    $routePrefix = Auth::user()->hasRole('super_admin') ? 'super-admin.' : 'admin.';
                @endphp
                <nav class="flex-1 px-4 py-6 space-y-6 overflow-y-auto scrollbar-hide" style="overscroll-behavior: contain;">
                    
                    <!-- Section: Utama -->
                    <div>
                        <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">{{ __('messages.main_menu') }}</p>
                        <a href="{{ route($routePrefix . 'dashboard') }}" 
                           class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs($routePrefix . 'dashboard') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            <span class="ml-3 font-medium">{{ __('messages.dashboard') }}</span>
                        </a>
                    </div>

                    <!-- Section: {{ __('messages.master_data') }} (urut dari data dasar yang paling penting) -->
                    <div>
                        <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">{{ __('messages.master_data') }}</p>
                        @if(Auth::user()->hasRole('admin'))
                        <a href="{{ route('admin.academic-years.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs('admin.academic-years.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="ml-3 font-medium">{{ __('messages.academic_year') }}</span>
                        </a>
                        @endif
                        <a href="{{ route($routePrefix . 'majors.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs($routePrefix . 'majors.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            <span class="ml-3 font-medium">Jurusan</span>
                        </a>
                        <a href="{{ route($routePrefix . 'classes.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs($routePrefix . 'classes.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span class="ml-3 font-medium">{{ __('messages.class') }}</span>
                        </a>
                        <a href="{{ route($routePrefix . 'subjects.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs($routePrefix . 'subjects.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <span class="ml-3 font-medium">{{ __('messages.subject') }}</span>
                        </a>
                        <a href="{{ route($routePrefix . 'teachers.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs($routePrefix . 'teachers.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="ml-3 font-medium">{{ __('messages.teacher') }}</span>
                        </a>
                        @feature('rooms')
                        <a href="{{ route($routePrefix . 'rooms.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs($routePrefix . 'rooms.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span class="ml-3 font-medium">{{ __('messages.room') }}</span>
                        </a>
                        @endfeature
                        <a href="{{ route($routePrefix . 'students.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs($routePrefix . 'students.index') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span class="ml-3 font-medium">{{ __('messages.student_data') }}</span>
                        </a>
                    </div>

                    <!-- Section: Akademik & Operasional -->
                    <div>
                        <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">{{ __('messages.academic') }}</p>
                        @feature('schedule')
                        <a href="{{ route($routePrefix . 'schedules.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs($routePrefix . 'schedules.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="ml-3 font-medium">{{ __('messages.schedule') }}</span>
                        </a>
                        @endfeature
                        @if(Auth::user()->hasRole('admin'))
                        <a href="{{ route($routePrefix . 'class-promotions.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs('admin.class-promotions.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                            <span class="ml-3 font-medium">{{ __('messages.class_promotion') }}</span>
                        </a>
                        <a href="{{ route('admin.academic-records.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs('admin.academic-records.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="ml-3 font-medium">{{ __('messages.academic_records') }}</span>
                        </a>
                        @endif

                        <a href="{{ route($routePrefix . 'students.bulk-graduation') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs($routePrefix . 'students.bulk-graduation') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <span class="ml-3 font-medium">{{ __('messages.graduation') }}</span>
                        </a>
                    </div>

                    <!-- Section: Laporan -->
                    <div>
                        <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">{{ __('messages.report') }}</p>
                        @feature('rooms')
                        <a href="{{ route('admin.monitoring.rooms') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs('admin.monitoring.rooms') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="ml-3 font-medium">{{ __('messages.monitoring_room') }}</span>
                        </a>
                        @endfeature
                        <a href="{{ route('admin.teacher-status.report') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs('admin.teacher-status.report*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="ml-3 font-medium">Rekap Izin Guru</span>
                        </a>
                        @if(Auth::user()->hasRole('admin'))
                        <a href="{{ route('admin.daily-attendance.settings.edit') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all {{ request()->routeIs('admin.daily-attendance.settings.edit') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="ml-3 font-medium">Pengaturan Absensi</span>
                        </a>
                        @endif
                    </div>
                </nav>
                
                <!-- User Menu -->
                <div class="p-4 border-t border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-linear-to-br from-blue-500 to-indigo-500 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
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
        
        <!-- Overlay untuk mobile -->
        <div x-show="sidebarOpen && window.innerWidth < 1024" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-in-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in-out duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden">
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden min-h-0 lg:ml-72">
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
                            <h2 class="text-xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h2>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <!-- Search -->
                            <div class="hidden md:block">
                                @php
                                    $searchAction = '#';
                                    if(Auth::user()->hasRole('super_admin')) $searchAction = route('super-admin.search');
                                    else if(Auth::user()->hasRole('admin')) $searchAction = route('admin.search');
                                @endphp
                                <form action="{{ $searchAction }}" method="GET" class="relative">
                                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('messages.search') }}" 
                                           class="w-80 pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <button type="submit" class="absolute left-3 top-2.5 text-gray-400 hover:text-blue-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>

                            <!-- Profile Dropdown -->
                            <div class="relative" x-data="{ profileOpen: false }">
                                <button @click="profileOpen = !profileOpen" class="flex items-center space-x-2 focus:outline-none">
                                    <div class="w-8 h-8 bg-linear-to-br from-blue-500 to-indigo-500 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <span class="hidden md:block text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                                    <svg class="hidden md:block w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="profileOpen" @click.away="profileOpen = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg py-1 border border-gray-100 z-50 overflow-hidden">
                                    <a href="{{ route(Auth::user()->hasRole('super_admin') ? 'super-admin.profile' : 'admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('messages.my_profile') }}</a>
                                     <a href="{{ route(Auth::user()->hasRole('super_admin') ? 'super-admin.settings' : 'admin.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('messages.settings') }}</a>
                                     @if(Auth::user()->hasRole('admin'))
                                         <a href="{{ route('admin.daily-attendance.corrections.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Koreksi Absensi</a>
                                     @endif
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
            <main class="flex-1 min-h-0 overflow-y-auto overscroll-y-contain bg-gray-50 scrollbar-hide" style="scrollbar-width: none; -ms-overflow-style: none;">
                <div class="px-4 sm:px-6 lg:px-8 py-8">
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
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
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // Handle resize event untuk mengupdate sidebar state
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                if (typeof Alpine !== 'undefined') {
                    Alpine.store('sidebarOpen', true);
                }
            }
        });
    </script>
    
    @stack('scripts')
    </div>
</body>
</html>
