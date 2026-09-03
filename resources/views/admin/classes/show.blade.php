{{-- resources/views/admin/classes/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Kelas - ' . $class->name)
@section('header', 'Detail Kelas')

@section('content')
<div id="class-detail-content" class="pb-12">
    <!-- Breadcrumb & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.classes.index') }}" class="hover:text-indigo-600 transition-colors">Kelas</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">Detail Kelas</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">{{ $class->name }}</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.classes.edit', $class) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Kelas
            </a>
            <a href="{{ route('admin.classes.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 transition-all">
                Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Information Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-4">Informasi Kelas</h3>
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-gray-500">Tingkat</span>
                        <span class="font-semibold text-gray-900">Kelas {{ $class->grade_level }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-gray-500">Tahun Ajaran</span>
                        <span class="font-semibold text-gray-900">{{ $class->academic_year }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-gray-500">Wali Kelas</span>
                        <span class="font-semibold text-gray-900">{{ $class->homeroomTeacher->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-500">Status</span>
                        <span class="px-2 py-0.5 {{ $class->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} rounded-md text-xs font-bold uppercase">
                            {{ $class->is_active ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Student Count Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-4">Jumlah Siswa</h3>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <span class="text-2xl font-bold text-gray-900">{{ $class->students_count }}</span>
                        <span class="text-sm text-gray-500 ml-1">Siswa</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Student List Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Daftar Siswa</h3>
                    <a href="{{ route('admin.students.index', ['class_id' => $class->id]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600">Siswa</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-600">NIS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($class->students as $student)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $student->name }}</td>
                                <td class="px-6 py-4 text-right text-gray-500">{{ $student->nis }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="px-6 py-8 text-center text-gray-400">Belum ada siswa terdaftar.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Simple Schedule Grid -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900">Jadwal Pelajaran</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-px bg-gray-100">
                    @php
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                        $dayNames = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => "Jumat"];
                    @endphp
                    @foreach($days as $day)
                    <div class="bg-white p-6">
                        <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-3">{{ $dayNames[$day] }}</h4>
                        <div class="space-y-3">
                            @forelse($class->schedules->where('day', $day)->sortBy('start_time') as $schedule)
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-semibold text-gray-900">
                                        {{ $schedule->subject->name }}
                                        @if($schedule->week_type && $schedule->week_type !== 'semua')
                                            <span class="inline-flex items-center px-1.5 py-0.5 ml-1 rounded-full text-[8px] font-bold uppercase tracking-wider border {{ $schedule->week_type == 'ganjil' ? 'bg-purple-100 text-purple-800 border-purple-300' : 'bg-teal-100 text-teal-800 border-teal-300' }}">{{ $schedule->week_type == 'ganjil' ? 'Ganjil' : 'Genap' }}</span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 ml-1 rounded-full text-[8px] font-bold uppercase tracking-wider border bg-gray-100 text-gray-600 border-gray-300">Tiap</span>
                                        @endif
                                    </span>
                                    <span class="text-gray-500">
                                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-[10px] text-gray-300 italic uppercase">Libur</p>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent Agenda -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900">Agenda Terbaru</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($class->agendas->take(5) as $agenda)
                    <div class="p-6 hover:bg-gray-50/50 transition-colors">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="text-sm font-bold text-gray-900">{{ $agenda->title }}</h4>
                            <span class="text-[10px] font-bold text-gray-400 uppercase">{{ $agenda->date->translatedFormat('d M Y') }}</span>
                        </div>
                        <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">{{ strip_tags($agenda->description) }}</p>
                        <div class="mt-3 flex items-center text-[10px] text-gray-400 font-semibold uppercase">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            {{ $agenda->teacher->name }}
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-gray-400 text-sm italic">Belum ada agenda tercatat.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection