{{-- resources/views/super_admin/admins/force-delete-confirm.blade.php --}}
@extends('layouts.super_admin')

@section('title', __('Konfirmasi Hapus Permanen'))
@section('header', __('Konfirmasi Hapus Permanen'))

@section('content')
<div class="space-y-6 pb-8 max-w-2xl mx-auto">
    <!-- Header Section -->
    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-11 h-11 bg-red-100 rounded-2xl flex items-center justify-center text-red-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ __('Hapus Permanen Admin Sekolah') }}</h1>
        </div>
        <p class="text-sm text-gray-500 font-medium mt-1">
            {{ __('Akun ini masih terhubung dengan data aktif di sistem. Tinjau data yang akan ikut terhapus sebelum melanjutkan.') }}
        </p>
    </div>

    <!-- Account Info -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center font-black text-sm shrink-0 border border-gray-200">
                {{ strtoupper(substr($admin->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-black text-gray-900">{{ $admin->name }}</p>
                <p class="text-sm text-gray-500 font-medium">{{ $admin->email }}</p>
            </div>
        </div>
    </div>

    <!-- Data Impact -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
        <p class="text-sm font-bold text-gray-700 mb-4">{{ __('Data yang AKAN TERHAPUS PERMANEN:') }}</p>
        <div class="space-y-3">
            <div class="flex items-center justify-between bg-red-50/60 border border-red-100 rounded-2xl px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center text-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-800">{{ __('Agenda / Jurnal Mengajar') }}</p>
                </div>
                <span class="text-sm font-black text-red-600">{{ $agendaCount }} data</span>
            </div>

            <div class="flex items-center justify-between bg-red-50/60 border border-red-100 rounded-2xl px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center text-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-800">{{ __('Jadwal Mengajar') }}</p>
                </div>
                <span class="text-sm font-black text-red-600">{{ $scheduleCount }} data</span>
            </div>

            <div class="flex items-center justify-between bg-red-50/60 border border-red-100 rounded-2xl px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center text-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-800">{{ __('Izin / Status Guru') }}</p>
                </div>
                <span class="text-sm font-black text-red-600">{{ $teacherStatusCount }} data</span>
            </div>
        </div>

        @if($homeroomCount > 0)
        <div class="mt-4">
            <p class="text-sm font-bold text-gray-700 mb-3">{{ __('Data yang TIDAK terhapus, hanya dilepas kaitannya:') }}</p>
            <div class="flex items-center justify-between bg-amber-50/70 border border-amber-100 rounded-2xl px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-800">{{ __('Kelas sebagai Wali Kelas') }}</p>
                    <span class="text-xs text-gray-500 font-medium">{{ __('(wali kelas dilepas, siswa tetap aman)') }}</span>
                </div>
                <span class="text-sm font-black text-amber-600">{{ $homeroomCount }} kelas</span>
            </div>
        </div>
        @endif
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('super-admin.admins.trash') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-2xl hover:bg-gray-50 transition-all duration-200 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('Batal') }}
        </a>
        <form action="{{ route('super-admin.admins.forceDelete', $admin->id) }}" method="POST"
              onsubmit="return confirm('{{ __('Data permanen tidak dapat dikembalikan. Lanjutkan?') }}')">
            @csrf
            @method('DELETE')
            <input type="hidden" name="continue" value="1">
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold text-white bg-red-600 rounded-2xl hover:bg-red-700 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('Ya, Hapus Permanen') }}
            </button>
        </form>
    </div>
</div>
@endsection