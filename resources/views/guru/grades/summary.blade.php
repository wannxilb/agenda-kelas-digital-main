@extends('layouts.guru')

@section('title', 'Rekap Nilai Tugas')
@section('header', 'Rekap Nilai Tugas')

@php
    $completion = $totalCells > 0 ? round(($filledCount / $totalCells) * 100) : 0;
@endphp

@section('content')
<div class="space-y-4 sm:space-y-6 pb-24 sm:pb-6">
    <div>
        <nav class="mb-3 flex items-center gap-2 text-xs font-semibold text-gray-400">
            <a href="{{ route('guru.grades.index', array_filter(['academic_year_id' => $academicYear?->id])) }}" class="hover:text-emerald-600 transition-colors">Nilai Tugas</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-gray-700">Rekap</span>
        </nav>

        <div class="sm:flex sm:items-start sm:justify-between sm:gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-gray-900">{{ $class->name }} - {{ $subject->name }}</h1>
                <p class="mt-1 text-xs sm:text-sm text-gray-500">
                    {{ $academicYear?->name ?? 'Tahun ajaran aktif' }} - Semester {{ $academicYear?->semester ?? '-' }}
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-2">
                <a href="{{ route('guru.grades.summary.export', array_filter(['class' => $class->id, 'subject' => $subject->id, 'academic_year_id' => $academicYear?->id])) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white text-emerald-700 border border-emerald-200 rounded-xl text-sm font-bold shadow-sm hover:bg-emerald-50 active:scale-[0.98] transition-all">
                    Export Rekap
                </a>
                <a href="{{ route('guru.grades.create', array_filter(['class_id' => $class->id, 'subject_id' => $subject->id])) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-200/50 hover:bg-emerald-700 active:scale-[0.98] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"></path>
                    </svg>
                    Tambah Tugas
                </a>
            </div>
        </div>
    </div>

    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-5 gap-3 sm:gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0">
            <div class="snap-center shrink-0 w-[132px] sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Siswa</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900">{{ $students->count() }}</p>
            </div>
            <div class="snap-center shrink-0 w-[132px] sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tugas</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900">{{ $assignments->count() }}</p>
            </div>
            <div class="snap-center shrink-0 w-[132px] sm:w-auto bg-emerald-50/70 rounded-2xl border border-emerald-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Terisi</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-700">{{ $completion }}%</p>
            </div>
            <div class="snap-center shrink-0 w-[132px] sm:w-auto bg-rose-50/70 rounded-2xl border border-rose-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-rose-600/70 uppercase tracking-wider mb-1">Terlambat</p>
                <p class="text-xl sm:text-2xl font-black text-rose-700">{{ $overdueCells }}</p>
            </div>
            <div class="snap-center shrink-0 w-[132px] sm:w-auto bg-sky-50/70 rounded-2xl border border-sky-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-sky-600/70 uppercase tracking-wider mb-1">Rata-rata</p>
                <p class="text-xl sm:text-2xl font-black text-sky-700">{{ $averageScore !== null ? number_format($averageScore, 1) : '-' }}</p>
            </div>
        </div>
    </div>

    @if($assignments->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m-7 4h8m-8 4h5m-7 6h12a2 2 0 002-2V5a2 2 0 00-2-2H8.5L4 7.5V19a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-800">Belum ada tugas</h3>
            <p class="text-sm text-gray-500 mt-1">Tambahkan tugas pertama untuk membuat kolom nilai.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="border-b border-gray-100 p-4 sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-black text-gray-900">Buku Nilai Semester</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Geser tabel ke samping untuk melihat semua tugas.</p>
                    </div>
                    <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-gray-600">
                        {{ $filledCount }}/{{ $totalCells }} terisi
                    </span>
                </div>
                <div class="mt-4 h-2 rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-full rounded-full bg-linear-to-r from-emerald-500 to-teal-500" style="width: {{ $completion }}%"></div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-[10px] uppercase tracking-wider text-gray-500">
                            <th class="sticky left-0 z-20 bg-gray-50 px-4 py-3 text-left font-black min-w-[220px]">Siswa</th>
                            @foreach($assignments as $assignment)
                                <th class="px-3 py-3 text-center font-black min-w-[112px]">
                                    <a href="{{ route('guru.grades.show', $assignment) }}" class="hover:text-emerald-700">
                                        <span class="block truncate max-w-[104px]">{{ $assignment->title }}</span>
                                        <span class="mt-0.5 block text-[9px] font-bold text-gray-400">{{ $assignment->assigned_date?->format('d/m') ?? '-' }}</span>
                                        @if($assignment->is_overdue)
                                            <span class="mt-1 inline-flex rounded-md bg-rose-50 px-1.5 py-0.5 text-[8px] font-black text-rose-700">Terlambat</span>
                                        @endif
                                    </a>
                                </th>
                            @endforeach
                            <th class="px-3 py-3 text-center font-black min-w-[96px]">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($students as $student)
                            @php
                                $summary = $studentSummaries->get($student->id);
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="sticky left-0 z-10 bg-white px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-xs font-black text-emerald-700">
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-bold text-gray-900">{{ $student->name }}</p>
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $student->nis ?? $student->nisn ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                @foreach($assignments as $assignment)
                                    @php
                                        $grade = $gradeMatrix->get($student->id . ':' . $assignment->id);
                                        $cellOverdue = $assignment->is_overdue && $grade?->score === null;
                                    @endphp
                                    <td class="px-3 py-3 text-center">
                                        @if($grade?->score !== null)
                                            <span class="inline-flex min-w-12 justify-center rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-xs font-black text-emerald-700">
                                                {{ number_format((float) $grade->score, 1) }}
                                            </span>
                                        @elseif($cellOverdue)
                                            <span class="inline-flex min-w-12 justify-center rounded-lg border border-rose-100 bg-rose-50 px-2.5 py-1 text-[10px] font-black text-rose-700">
                                                Terlambat
                                            </span>
                                        @else
                                            <span class="text-xs font-bold text-gray-300">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex min-w-12 justify-center rounded-lg border border-sky-100 bg-sky-50 px-2.5 py-1 text-xs font-black text-sky-700">
                                        {{ $summary && $summary['average_score'] !== null ? number_format($summary['average_score'], 1) : '-' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush
