{{-- resources/views/super_admin/admins/index.blade.php --}}
@extends('layouts.super_admin')

@section('title', __('Manajemen Admin Sekolah'))
@section('header', __('Manajemen Admin Sekolah'))

@section('content')
<div class="space-y-8 pb-8">
    <!-- Header Section -->
    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ __('Daftar Admin Sekolah') }}</h1>
                <p class="mt-1 text-sm text-gray-500 font-medium">
                    {{ __('Kelola akses administrator untuk setiap instansi sekolah.') }}
                </p>
            </div>
                <a href="{{ route('super-admin.admins.trash') }}" 
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-black text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all duration-200">
                    Trash
                </a>
                <a href="{{ route('super-admin.admins.create') }}" 
                   class="col-span-2 sm:col-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-black text-white bg-green-600 rounded-xl hover:bg-green-700 transition-all duration-200">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Tambah Admin') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Grouped Table Section -->
    <div class="space-y-6">
        @forelse($institutions as $institution)
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ expanded: false }">
            <!-- Institution Header -->
            <div class="bg-gray-50/80 px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer hover:bg-gray-100/80 transition-colors" @click="expanded = !expanded">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-linear-to-br from-green-100 to-emerald-100 text-green-600 rounded-xl flex items-center justify-center font-black text-lg shadow-sm">
                        {{ strtoupper(substr($institution->name, 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900">{{ $institution->name }}</h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-white border border-gray-200 text-gray-600 uppercase tracking-widest">
                                ID: {{ $institution->id }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $institution->users->count() >= 2 ? 'bg-red-50 text-red-600 border-red-100' : 'bg-green-50 text-green-600 border-green-100' }} border uppercase tracking-widest">
                                {{ __('Kapasitas') }}: {{ $institution->users->count() }}/2 {{ __('Admin') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center">
                    <button type="button" class="text-gray-400 hover:text-gray-600 transition-transform duration-300" :class="{'rotate-180': expanded}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Admins Table -->
            <div class="overflow-x-auto" x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" style="display: none;">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-white">
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest w-1/3">{{ __('Nama Admin') }}</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest w-1/3">{{ __('Email') }}</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest w-1/3">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($institution->users as $admin)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                                    </div>
                                    <p class="font-bold text-gray-900 text-sm">{{ $admin->name }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-600 font-medium">{{ $admin->email }}</p>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-3">
                                    <a href="{{ route('super-admin.admins.edit', $admin->id) }}" class="text-gray-400 hover:text-green-600 transition-colors" title="{{ __('Edit Admin') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <form action="{{ route('super-admin.admins.destroy', $admin->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Yakin ingin menghapus admin ini?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="{{ __('Hapus Admin') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center">
                                <p class="text-sm text-gray-500 font-medium">{{ __('Belum ada admin terdaftar untuk instansi ini.') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-3xl p-12 text-center border border-gray-100 shadow-sm">
            <p class="text-gray-500 font-medium">{{ __('Belum ada data instansi. Silakan tambahkan instansi terlebih dahulu.') }}</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
