{{-- resources/views/super_admin/institutions/trash.blade.php --}}
@extends('layouts.super_admin')

@section('title', __('Instansi Dihapus'))
@section('header', __('Trash Instansi'))

@section('content')
<div class="space-y-8 pb-8">
    <!-- Header Section -->
    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm relative overflow-hidden">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-red-100 rounded-2xl flex items-center justify-center text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ __('Trash Instansi') }}</h1>
                </div>
                <p class="text-sm text-gray-500 font-medium">
                    {{ __('Daftar instansi sekolah yang telah dihapus (Soft Delete). Pulihkan atau hapus data secara permanen.') }}
                </p>
            </div>
            <a href="{{ route('super-admin.institutions.index') }}" 
               class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-2xl hover:bg-gray-50 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Kembali ke Daftar Instansi') }}
            </a>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest w-1/3">{{ __('Instansi') }}</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest w-1/4">{{ __('Email & Alamat') }}</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('Status Terakhir') }}</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($institutions as $institution)
                    <tr class="hover:bg-gray-50/80 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gray-100 text-gray-400 rounded-xl flex items-center justify-center font-black text-lg shrink-0 border border-gray-200">
                                    {{ strtoupper(substr($institution->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ $institution->name }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-gray-100 text-gray-500 uppercase tracking-widest">
                                            ID: {{ $institution->id }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-black text-red-500 uppercase tracking-widest">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $institution->deleted_at->format('d M Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-800 font-medium truncate">{{ $institution->email ?: '-' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5 max-w-xs truncate" title="{{ $institution->address }}">{{ $institution->address ?: __('Tidak ada alamat') }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($institution->status === 'active')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @elseif($institution->status === 'suspended')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 text-red-700 border border-red-200 rounded-full text-xs font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Suspended
                                </span>
                            @elseif($institution->status === 'maintenance')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Maintenance
                                </span>
                            @elseif($institution->status === 'expired')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 text-gray-700 border border-gray-300 rounded-full text-xs font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> Expired
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 border border-gray-300 rounded-full text-xs font-bold">{{ $institution->status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2 opacity-100 md:opacity-70 md:group-hover:opacity-100 transition-opacity">
                                <form action="{{ route('super-admin.institutions.restore', $institution->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Kembalikan instansi ini ke daftar aktif?') }}')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 hover:text-emerald-800 transition-colors border border-emerald-100" title="{{ __('Restore') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        <span>Restore</span>
                                    </button>
                                </form>
                                <form action="{{ route('super-admin.institutions.forceDelete', $institution->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Yakin ingin menghapus permanen instansi ini? Seluruh data yang terkait dengan instansi ini mungkin akan terhapus secara permanen.') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-red-700 bg-red-50 rounded-lg hover:bg-red-100 hover:text-red-800 transition-colors border border-red-100" title="{{ __('Force Delete') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-12 h-12 mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <p class="text-sm font-bold text-gray-500">{{ __('Trash Kosong') }}</p>
                                <p class="text-xs mt-1">{{ __('Belum ada data instansi yang dihapus.') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
