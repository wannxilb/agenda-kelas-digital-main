{{-- resources/views/guru/teacher-status/create.blade.php --}}
@extends('layouts.guru')

@section('title', 'Buat Pengajuan')
@section('header', 'Buat Pengajuan')

@section('content')
<div class="mx-auto max-w-3xl space-y-4 sm:space-y-6 pb-24 sm:pb-6">
    {{-- Hero Header --}}
    <div class="bg-linear-to-br from-rose-500 to-pink-600 rounded-2xl p-5 sm:p-7 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-1/4 w-32 h-32 bg-white/5 rounded-full blur-xl"></div>
        <div class="relative flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Buat Pengajuan Baru</h2>
                <p class="text-[11px] text-pink-100">Izin, sakit, atau tugas luar untuk hari ini atau masa depan</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-8">
        @include('guru.teacher-status._form', [
            'formAction' => route('guru.teacher-status.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Ajukan',
            'sendingLabel' => 'Mengirim...',
            'teacherStatus' => null,
        ])
    </div>
</div>
@endsection
