@extends('layouts.admin')

@section('title', 'Detail Mata Pelajaran - ' . $subject->name)
@section('header', 'Detail Mata Pelajaran')

@section('content')
<div class="pb-12 max-w-5xl mx-auto">
    <!-- Breadcrumb & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.subjects.index') }}" class="hover:text-indigo-600 transition-colors">Mata Pelajaran</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">{{ $subject->name }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-linear-to-br from-teal-500 to-emerald-600 rounded-2xl flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-teal-500/20">
                    {{ strtoupper(substr($subject->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $subject->name }}</h1>
                    <p class="text-sm text-gray-500">{{ $subject->credit_hours }} Jam Pelajaran / Minggu</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.schedules.create', ['subject_id' => $subject->id]) }}" class="inline-flex items-center px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Tambah Jadwal
            </a>
            <a href="{{ route('admin.subjects.edit', $subject) }}" class="inline-flex items-center px-4 py-2.5 bg-white text-gray-700 text-sm font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ $subject->teachers->count() }}</p>
                <p class="text-xs font-semibold text-gray-500">Guru</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ $stats['total_classes'] }}</p>
                <p class="text-xs font-semibold text-gray-500">Kelas</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ $stats['total_schedules'] }}</p>
                <p class="text-xs font-semibold text-gray-500">Jadwal</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-gray-900">{{ $stats['total_agendas'] }}</p>
                <p class="text-xs font-semibold text-gray-500">Agenda</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <!-- Guru Pengampu (Wider Section) -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm relative z-10">
                <div class="px-6 py-5 border-b border-gray-100 rounded-t-2xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">Guru Pengampu</h3>
                            <p class="text-xs text-gray-500">{{ $subject->teachers->count() }} guru mengampu mata pelajaran ini</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 relative z-20">
                    <!-- Guru List -->
                    <div class="space-y-3">
                        @forelse($subject->teachers as $teacher)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:border-gray-200 transition-colors group">
                            <div class="flex items-center gap-4">
                                <div class="w-11 h-11 rounded-full bg-linear-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                    {{ strtoupper(substr($teacher->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">{{ $teacher->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">NIP: {{ $teacher->nip ?? '-' }}</p>
                                </div>
                            </div>
                            @if($subject->teachers->count() > 1)
                            <form action="{{ route('admin.subjects.remove-teacher', [$subject, $teacher->id]) }}" method="POST" onsubmit="return confirm('Hapus {{ $teacher->name }} dari {{ $subject->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="opacity-0 group-hover:opacity-100 p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <p class="text-sm text-gray-500">Belum ada guru</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Add Guru -->
                    @if($allTeachers->count() > 0)
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <form action="{{ route('admin.subjects.add-teacher', $subject) }}" method="POST" class="flex gap-3">
                            @csrf
                            <div class="flex-1">
                                <select name="teacher_id" id="add_teacher_select">
                                    <option value="">Pilih guru untuk ditambahkan...</option>
                                    @foreach($allTeachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}{{ $teacher->nip ? ' (' . $teacher->nip . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="px-5 py-2.5 bg-teal-600 text-white rounded-xl text-sm font-bold hover:bg-teal-700 transition-all shadow-sm shadow-teal-500/20 flex items-center gap-2 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Tambah
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <p class="text-sm text-gray-500 text-center">Semua guru sudah terdaftar di mata pelajaran ini</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Deskripsi -->
            @if($subject->description)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mt-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Deskripsi</h3>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">{{ $subject->description }}</p>
            </div>
            @endif
        </div>

        <!-- Jadwal Mengajar -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">Jadwal</h3>
                            <p class="text-xs text-gray-500">{{ $subject->schedules->count() }} jadwal aktif</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.schedules.create', ['subject_id' => $subject->id]) }}" class="p-2 text-gray-400 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-all" title="Tambah Jadwal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </a>
                </div>

                <div class="p-4">
                    @php
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                        $dayNames = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat'];
                        $dayColors = [
                            'Monday' => 'bg-blue-500',
                            'Tuesday' => 'bg-emerald-500',
                            'Wednesday' => 'bg-amber-500',
                            'Thursday' => 'bg-purple-500',
                            'Friday' => 'bg-rose-500',
                        ];
                    @endphp

                    @if($subject->schedules->count() > 0)
                        <div class="space-y-2">
                            @foreach($subject->schedules->sortBy(['day', 'start_time']) as $schedule)
                            <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                                <div class="w-10 h-10 rounded-lg {{ $dayColors[$schedule->day] ?? 'bg-gray-500' }} flex items-center justify-center text-white text-xs font-bold shrink-0 shadow-sm">
                                    {{ strtoupper(substr($dayNames[$schedule->day], 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-900 text-sm truncate" title="{{ $schedule->class->name }}">{{ $schedule->class->name }}</p>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</span>
                                        <span class="text-[10px] text-gray-300">|</span>
                                        <span class="text-[11px] text-gray-400">{{ $dayNames[$schedule->day] }}</span>
                                    </div>
                                </div>
                                @if($schedule->room_model || $schedule->room)
                                <div class="flex items-center gap-1 text-[11px] text-gray-400 shrink-0 max-w-20">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    <span class="truncate" title="{{ $schedule->room_model ? $schedule->room_model->name : $schedule->room }}">{{ $schedule->room_model ? $schedule->room_model->name : $schedule->room }}</span>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <p class="text-sm text-gray-500 mb-2">Belum ada jadwal</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Daftar Kelas -->
            @if($subject->schedules->count() > 0)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
                <div class="px-6 py-5 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">Kelas</h3>
                            <p class="text-xs text-gray-500">{{ $subject->schedules->unique('class_id')->count() }} kelas</p>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <div class="space-y-2">
                        @foreach($subject->schedules->unique('class_id') as $schedule)
                        <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-linear-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                {{ strtoupper(substr($schedule->class->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-900 text-sm">{{ $schedule->class->name }}</p>
                                <p class="text-xs text-gray-500">Kelas {{ $schedule->class->grade_level }}{{ $schedule->class->major ? ' - ' . $schedule->class->major : '' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#add_teacher_select', {
            create: false,
            placeholder: 'Cari guru...',
            sortField: { field: 'text', direction: 'asc' },
            maxOptions: null,
        });
    });
</script>
@endpush
