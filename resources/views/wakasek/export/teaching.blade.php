{{-- resources/views/wakasek/export/teaching.blade.php --}}
@extends('layouts.wakasek')

@section('title', 'Laporan Akademik')
@section('header', 'Laporan Akademik')

@section('content')
<div class="space-y-8 pb-8">
    <!-- Header Section -->
    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Laporan Akademik & Pengajaran</h1>
                <p class="mt-1 text-sm text-gray-500 font-medium">
                    Unduh rekapitulasi jurnal mengajar seluruh guru dan progres kurikulum sekolah.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Teaching Journal Report -->
        <div class="bg-white rounded-3xl
        border border-gray-100 shadow-sm overflow-hidden group hover:border-indigo-200 transition-all        
        duration-300">
            <div class="p-10">
                <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 10-8 0v2m8 0V7a4 4 0 118 0v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-gray-900 mb-2">Rekap Jurnal Guru</h3>
                <p class="text-sm text-gray-500 font-medium leading-relaxed mb-8">Data lengkap pengajaran seluruh guru mata pelajaran per bulan.</p>
                
                <form action="{{ route('wakasek.export.teaching.excel') }}" method="GET" class="space-y-4">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Pilih Bulan</label>
                        <select name="month" class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-bold text-gray-900">
                            @foreach($months as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Unduh Excel
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Curriculum Progress Report -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden group hover:border-blue-200 transition-all duration-300">
            <div class="p-10">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-gray-900 mb-2">Progres Kurikulum</h3>
                <p class="text-sm text-gray-500 font-medium leading-relaxed mb-8">Laporan ketuntasan materi pembelajaran per tingkat kelas.</p>
                
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Tahun Akademik</label>
                        <select class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm font-bold text-gray-900">
                            <option>2023/2024</option>
                        </select>
                    </div>
                    <button type="button" class="w-full py-4 bg-blue-600 text-white rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all opacity-50 cursor-not-allowed">
                        Coming Soon
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
