@extends('layouts.siswa')

@section('title', 'Riwayat Pengajuan')
@section('header', 'Riwayat Pengajuan')
@section('content-padding', 'pt-4 sm:pt-6 pb-24 lg:pb-8')

@php
    $statusPresentation = [
        'pending' => ['Menunggu', 'bg-amber-50 text-amber-700 ring-amber-100'],
        'approved' => ['Disetujui', 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
        'rejected' => ['Ditolak', 'bg-rose-50 text-rose-700 ring-rose-100'],
        'cancelled' => ['Dibatalkan', 'bg-gray-50 text-gray-500 ring-gray-200'],
    ];
    $groupLabels = \App\Models\StudentEarlyLeaveRequest::categoryGroups();
@endphp

@section('content')
<div class="space-y-4 sm:space-y-5">
    <section class="rounded-3xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[11px] font-black uppercase tracking-wider text-blue-600">Pengajuan izin / dispen</p>
                <h1 class="mt-1 text-xl font-black tracking-tight text-gray-900">Riwayat pengajuan</h1>
                <p class="mt-1 text-xs leading-5 text-gray-500">Semua pengajuan Anda beserta hasil review wali kelas.</p>
            </div>
            <a href="{{ route('siswa.daily-attendance.index') }}"
               class="shrink-0 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-[11px] font-black text-gray-700 transition active:scale-[0.98]">
                Kembali
            </a>
        </div>
    </section>

    @forelse($requests as $request)
        @php
            $badge = $statusPresentation[$request->status] ?? ['Diajukan', 'bg-gray-50 text-gray-500 ring-gray-200'];
            $groupLabel = $groupLabels[$request->categoryGroup()] ?? 'Pengajuan';
        @endphp
        <article class="rounded-3xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-black text-gray-900">{{ $request->categoryLabel() }}</p>
                        <span class="rounded-lg px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-gray-400 ring-1 ring-gray-100">{{ $groupLabel }}</span>
                    </div>
                    <p class="mt-1 text-[11px] font-bold text-gray-500">
                        @if($request->isMultiDay())
                            {{ $request->date->translatedFormat('d M Y') }} - {{ $request->date_end->translatedFormat('d M Y') }}
                        @else
                            {{ $request->date->translatedFormat('d M Y') }}
                        @endif
                        <span class="mx-1 text-gray-300">•</span>
                        {{ $request->created_at->translatedFormat('d M Y H:i') }}
                    </p>
                </div>
                <span class="shrink-0 rounded-lg px-2 py-1 text-[10px] font-black ring-1 {{ $badge[1] }}">{{ $badge[0] }}</span>
            </div>

            <p class="mt-3 break-words text-xs leading-5 text-gray-700">{{ $request->reason }}</p>

            @if($request->activity_name)
                <p class="mt-1 text-[11px] font-bold text-gray-600">Kegiatan: {{ $request->activity_name }}</p>
            @endif
            @if($request->activity_start_time || $request->activity_end_time)
                <p class="mt-0.5 text-[11px] font-semibold text-gray-500">
                    Jam: {{ $request->activity_start_time ? \Illuminate\Support\Carbon::parse($request->activity_start_time)->format('H:i') : '?' }} - {{ $request->activity_end_time ? \Illuminate\Support\Carbon::parse($request->activity_end_time)->format('H:i') : '?' }}
                </p>
            @endif

            <div class="mt-3 flex flex-wrap items-center gap-2">
                @if($request->evidence_path)
                    <a href="{{ route('attendance.early-leave.evidence', $request) }}" target="_blank" rel="noopener"
                       class="rounded-lg bg-gray-50 px-2.5 py-1.5 text-[10px] font-black text-gray-600 ring-1 ring-gray-100">Buka bukti</a>
                @endif
                @if($request->status === 'pending')
                    <form method="POST" action="{{ route('siswa.daily-attendance.early-leave.cancel', $request) }}"
                          onsubmit="return confirm('Batalkan pengajuan ini? Wali kelas belum memprosesnya.')">
                        @csrf
                        <button class="rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-[10px] font-black text-rose-600 transition hover:bg-rose-100">Batalkan</button>
                    </form>
                @endif
            </div>

            @if($request->reviewer)
                <div class="mt-3 rounded-2xl bg-gray-50 px-3 py-2 ring-1 ring-gray-100">
                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">
                        Direview oleh {{ $request->reviewer->name }}
                        @if($request->reviewed_at)
                            • {{ $request->reviewed_at->translatedFormat('d M Y H:i') }}
                        @endif
                    </p>
                    @if($request->reviewer_note)
                        <p class="mt-1 text-[11px] font-semibold leading-4 text-gray-700">{{ $request->reviewer_note }}</p>
                    @endif
                </div>
            @endif
        </article>
    @empty
        <div class="rounded-3xl border border-gray-100 bg-white p-8 text-center shadow-sm">
            <p class="text-sm font-black text-gray-900">Belum ada pengajuan</p>
            <p class="mt-1 text-xs text-gray-500">Anda belum pernah mengajukan izin / dispensasi.</p>
            <a href="{{ route('siswa.daily-attendance.index') }}#pengajuan"
               class="mt-4 inline-flex rounded-xl bg-gray-900 px-4 py-2.5 text-xs font-black text-white shadow-lg shadow-gray-200/60">
                Buat pengajuan
            </a>
        </div>
    @endforelse
</div>
@endsection
