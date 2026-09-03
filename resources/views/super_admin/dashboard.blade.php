{{-- resources/views/super_admin/dashboard.blade.php --}}
@extends('layouts.super_admin')

@section('title', __('Dashboard Super Admin'))
@section('header', __('Dashboard Super Admin'))

@section('content')
<div class="space-y-8 pb-8">
    <!-- Welcome Banner -->
    <div class="relative overflow-hidden bg-linear-to-br from-purple-700 via-indigo-600 to-violet-700 rounded-3xl p-8 text-white shadow-xl shadow-purple-500/20">
        <!-- Abstract Shapes for Premium Feel -->
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
        
        <div class="relative flex items-center justify-between">
            <div class="flex-1">
                <h1 class="text-3xl font-black tracking-tight">{{ __('Halo, :name!', ['name' => Auth::user()->name]) }} 👋</h1>
                <p class="mt-2 text-lg text-purple-100 font-medium max-w-xl">
                    {{ __('Selamat datang kembali di dashboard sistem. Pantau dan kelola seluruh instansi dari sini.') }}
                </p>
                <div class="mt-6 flex gap-3">
                    <div class="px-4 py-2 bg-white/20 backdrop-blur-md rounded-xl text-xs font-bold uppercase tracking-wider">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </div>
                    <div class="px-4 py-2 bg-emerald-500/20 backdrop-blur-md rounded-xl text-xs font-bold uppercase tracking-wider text-emerald-300 flex items-center">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full mr-2 animate-pulse"></span>
                        {{ __('Sistem Aktif') }}
                    </div>
                </div>
            </div>
            <div class="hidden lg:block">
                <div class="w-32 h-32 bg-white/10 backdrop-blur-md rounded-3xl flex items-center justify-center border border-white/20 shadow-inner">
                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row 1 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100 group hover:border-purple-500 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Total Instansi') }}</p>
                    <p class="text-3xl font-black text-gray-900 mt-1">{{ $stats['total_institutions'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100 group hover:border-emerald-500 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Instansi Aktif') }}</p>
                    <p class="text-3xl font-black text-emerald-600 mt-1">{{ $stats['active_institutions'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100 group hover:border-indigo-500 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Total Admin') }}</p>
                    <p class="text-3xl font-black text-indigo-600 mt-1">{{ $stats['total_admins'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100 group hover:border-blue-500 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Total Pengguna') }}</p>
                    <p class="text-3xl font-black text-blue-600 mt-1">{{ ($stats['total_teachers'] ?? 0) + ($stats['total_students'] ?? 0) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
        <h2 class="text-xl font-black text-gray-900 mb-6 tracking-tight">{{ __('Aksi Cepat') }}</h2>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('super-admin.institutions.index') }}" 
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-black text-white bg-linear-to-r from-purple-600 to-indigo-600 rounded-xl shadow-md shadow-purple-500/20 hover:from-purple-700 hover:to-indigo-700 transition-all duration-200">
                {{ __('Kelola Instansi') }}
            </a>
            <a href="{{ route('super-admin.admins.index') }}" 
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-black text-gray-600 bg-gray-50 border border-gray-200 rounded-xl hover:bg-white hover:text-gray-900 hover:border-gray-300 transition-all duration-200 shadow-sm">
                {{ __('Kelola Admin') }}
            </a>
        </div>
    </div>
</div>
@endsection
