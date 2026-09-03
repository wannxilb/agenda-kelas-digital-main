@extends('layouts.admin')

@section('title', 'Manajemen Ruangan')
@section('header', 'Manajemen Ruangan')

@section('content')
<div class="space-y-8 pb-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Data Ruangan</h1>
            <p class="mt-2 text-base text-gray-500 max-w-2xl">Kelola daftar ruangan, tipe, dan kapasitas yang tersedia di sekolah.</p>
        </div>
        <a href="{{ route('admin.rooms.create') }}" 
           class="inline-flex items-center px-6 py-3 text-sm font-semibold text-white bg-linear-to-r from-blue-600 to-indigo-600 rounded-2xl shadow-lg shadow-blue-500/20 hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl transition-all duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Ruangan
        </a>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 group hover:border-blue-500 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Ruangan</p>
                    <p class="text-3xl font-black text-gray-900 mt-1">{{ $totalRooms }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-50 flex items-center text-xs text-gray-500">
                <span class="font-bold text-blue-600">Terdaftar</span>
                <span class="mx-2">•</span>
                Total keseluruhan ruangan
            </div>
        </div>
        
        @php
            $colors = [
                ['border' => 'hover:border-emerald-500', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
                ['border' => 'hover:border-violet-500', 'bg' => 'bg-violet-50', 'text' => 'text-violet-600'],
                ['border' => 'hover:border-amber-500', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
                ['border' => 'hover:border-rose-500', 'bg' => 'bg-rose-50', 'text' => 'text-rose-600'],
            ];
            $colorIndex = 0;
        @endphp

        @foreach($roomsByType as $type => $count)
            @php
                $color = $colors[$colorIndex % count($colors)];
                $colorIndex++;
            @endphp
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 group {{ $color['border'] }} transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $type }}</p>
                        <p class="text-3xl font-black text-gray-900 mt-1">{{ $count }}</p>
                    </div>
                    <div class="w-12 h-12 {{ $color['bg'] }} rounded-2xl flex items-center justify-center {{ $color['text'] }} group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-50 flex items-center text-xs text-gray-500">
                    <span class="font-bold {{ $color['text'] }}">Aktif</span>
                    <span class="mx-2">•</span>
                    Ruangan tipe {{ strtolower($type) }}
                </div>
            </div>
        @endforeach
    </div>

    <!-- Tabel Ruangan -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Ruangan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kapasitas</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($rooms as $room)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $room->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100 rounded-lg">
                                    {{ $room->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $room->capacity ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-3">
                                    <a href="{{ route('admin.rooms.edit', $room->id) }}" class="text-gray-400 hover:text-green-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.rooms.destroy', $room->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus ruangan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center text-gray-500 italic">
                                Belum ada data ruangan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
