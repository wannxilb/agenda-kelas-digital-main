@extends('layouts.admin')

@section('title', 'Monitoring Ruangan')
@section('header', 'Monitoring Ruangan')

@section('content')
<div class="space-y-8 pb-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Monitoring Ruangan</h1>
            <p class="mt-2 text-base text-gray-500 max-w-2xl">Pantau ketersediaan seluruh ruangan secara real-time.</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="{{ route('admin.monitoring.rooms') }}" class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ruangan..." 
                       class="block w-full pl-12 pr-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm">
            </div>

            <select name="type" class="block w-full px-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm">
                <option value="">Semua Tipe</option>
                <option value="Laboratorium" {{ request('type') == 'Laboratorium' ? 'selected' : '' }}>Laboratorium</option>
                <option value="Kelas" {{ request('type') == 'Kelas' ? 'selected' : '' }}>Kelas</option>
            </select>
            
            <select name="status" class="block w-full px-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm">
                <option value="">Semua Status</option>
                <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>Sedang Digunakan</option>
                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Tersedia</option>
            </select>
            
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition">Filter Data</button>
        </form>
    </div>

    <!-- Rooms Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Ruangan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Detail Penggunaan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($rooms as $room)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $room->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 text-xs font-bold bg-gray-100 text-gray-700 rounded-lg">
                                    {{ $room->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($room->is_occupied)
                                    <span class="px-3 py-1 text-xs font-bold bg-rose-50 text-rose-700 border border-rose-100 rounded-lg">
                                        Sedang Digunakan
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-lg">
                                        Tersedia
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($room->is_occupied && $room->active_schedule)
                                    <div class="space-y-0.5">
                                        <p><span class="font-bold text-gray-900">Kelas:</span> {{ $room->active_schedule->class->name }}</p>
                                        <p><span class="font-bold text-gray-900">Guru:</span> {{ $room->active_schedule->teacher->name }}</p>
                                        <p><span class="font-bold text-gray-900">Selesai:</span> {{ \Carbon\Carbon::parse($room->active_schedule->end_time)->format('H:i') }}</p>
                                    </div>
                                @else
                                    <span class="italic text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center">
                                <p class="text-gray-500 italic">Tidak ada ruangan ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
