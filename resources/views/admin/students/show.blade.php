{{-- resources/views/admin/students/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Siswa - ' . $student->name)
@section('header', 'Detail Siswa')

@section('content')
<div id="student-detail-content" class="pb-12">
    <!-- Breadcrumb & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.students.index') }}" class="hover:text-indigo-600 transition-colors">Siswa</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">Detail Siswa</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">{{ $student->name }}</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.students.edit', $student) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Siswa
            </a>
            <a href="{{ route('admin.students.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 transition-all">
                Kembali
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Profile Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-linear-to-r from-blue-600 to-indigo-600 px-6 py-8 flex flex-col items-center">
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-lg mb-4">
                        <span class="text-4xl font-bold text-blue-600">
                            {{ strtoupper(substr($student->name, 0, 1)) }}
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-white text-center">{{ $student->name }}</h2>
                    <p class="text-blue-100 mt-1">NIS: {{ $student->nis }}</p>
                    
                    <div class="mt-4">
                        @if($student->status == 'active')
                            <span class="px-3 py-1 text-xs font-bold bg-green-400/20 text-green-100 rounded-full border border-green-400/30 uppercase tracking-wider">Aktif</span>
                        @elseif($student->status == 'graduated')
                            <span class="px-3 py-1 text-xs font-bold bg-indigo-400/20 text-indigo-100 rounded-full border border-indigo-400/30 uppercase tracking-wider">Lulus</span>
                        @else
                            <span class="px-3 py-1 text-xs font-bold bg-red-400/20 text-red-100 rounded-full border border-red-400/30 uppercase tracking-wider">Non-Aktif</span>
                        @endif
                    </div>
                </div>
                
                <div class="p-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Informasi Personal
                    </h3>
                    <div class="space-y-4 text-sm">
                        <div class="flex flex-col py-2 border-b border-gray-50">
                            <span class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Kelas</span>
                            <span class="font-medium text-gray-950">{{ $student->class->name ?? 'Belum ada kelas' }}</span>
                        </div>
                        <div class="flex flex-col py-2 border-b border-gray-50">
                            <span class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">NISN</span>
                            <span class="font-medium text-gray-955 font-mono">{{ $student->nisn ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col py-2 border-b border-gray-50">
                            <span class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Gender</span>
                            <span class="font-medium text-gray-950">{{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                        </div>
                        <div class="flex flex-col py-2 border-b border-gray-50">
                            <span class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Tempat, Tanggal Lahir</span>
                            <span class="font-medium text-gray-950">
                                @if($student->tempat_lahir || $student->tanggal_lahir)
                                    {{ $student->tempat_lahir ?? '-' }}, {{ $student->tanggal_lahir ? \Carbon\Carbon::parse($student->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="flex flex-col py-2 border-b border-gray-50">
                            <span class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">No Telepon</span>
                            <span class="font-medium text-gray-950">{{ $student->phone ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col py-2 border-b border-gray-50">
                            <span class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Email</span>
                            <span class="font-medium text-gray-950 text-xs">{{ $student->email ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col py-2 border-b border-gray-50">
                            <span class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Alamat Lengkap</span>
                            <span class="font-medium text-gray-950">
                                {{ $student->address ?? '-' }}
                                @if($student->rt || $student->rw || $student->kelurahan || $student->kecamatan)
                                    <div class="text-xs text-gray-500 mt-1 font-normal leading-relaxed">
                                        @if($student->rt) RT {{ $student->rt }} @endif
                                        @if($student->rw) RW {{ $student->rw }} @endif
                                        @if($student->kelurahan) <br>Kel. {{ $student->kelurahan }} @endif
                                        @if($student->kecamatan) <br>Kec. {{ $student->kecamatan }} @endif
                                    </div>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Stats Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-4">Statistik Kehadiran</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-green-50 rounded-xl p-4 border border-green-100 text-center">
                        <p class="text-2xl font-black text-green-600">{{ $attendance_stats['present'] }}</p>
                        <p class="text-[10px] font-bold text-green-800 uppercase tracking-wider mt-1">Hadir</p>
                    </div>
                    <div class="bg-red-50 rounded-xl p-4 border border-red-100 text-center">
                        <p class="text-2xl font-black text-red-600">{{ $attendance_stats['absent'] }}</p>
                        <p class="text-[10px] font-bold text-red-800 uppercase tracking-wider mt-1">Alpha</p>
                    </div>
                    <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-100 text-center">
                        <p class="text-2xl font-black text-yellow-600">{{ $attendance_stats['late'] }}</p>
                        <p class="text-[10px] font-bold text-yellow-800 uppercase tracking-wider mt-1">Terlambat</p>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 text-center">
                        <p class="text-2xl font-black text-blue-600">{{ $attendance_stats['excused'] }}</p>
                        <p class="text-[10px] font-bold text-blue-800 uppercase tracking-wider mt-1">Izin</p>
                    </div>
                    <div class="bg-orange-50 rounded-xl p-4 border border-orange-100 text-center">
                        <p class="text-2xl font-black text-orange-600">{{ $attendance_stats['sick'] }}</p>
                        <p class="text-[10px] font-bold text-orange-800 uppercase tracking-wider mt-1">Sakit</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Riwayat Kelas -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900">Riwayat Kelas</h3>
                    <a href="{{ route('admin.students.history', $student) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        Lihat Semua
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tahun Ajaran</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kelas</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tingkat</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Wali Kelas</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($student->classHistories->sortByDesc(function($history) { return $history->academicYear->name ?? ''; }) as $history)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $history->academicYear->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                    {{ $history->class->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-lg">
                                        Kelas {{ $history->class->grade_level ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div class="flex items-center gap-2">
                                        @if($history->homeroomTeacher)
                                            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs">
                                                {{ strtoupper(substr($history->homeroomTeacher->name, 0, 1)) }}
                                            </div>
                                            <span>{{ $history->homeroomTeacher->name }}</span>
                                        @else
                                            <span class="text-gray-400 italic">Data tidak tersimpan</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                        <p class="text-sm">Belum ada riwayat kelas.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Riwayat Kehadiran -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900">Riwayat Kehadiran (10 Terakhir)</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($recentAttendance as $attendance)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'present' => 'bg-green-50 text-green-700 border-green-100',
                                            'absent' => 'bg-red-50 text-red-700 border-red-100',
                                            'late' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                            'excused' => 'bg-blue-50 text-blue-700 border-blue-100',
                                            'sick' => 'bg-orange-50 text-orange-700 border-orange-100',
                                            'unknown' => 'bg-amber-50 text-amber-700 border-amber-100'
                                        ];
                                        $statusLabels = [
                                            'present' => 'Hadir',
                                            'absent' => 'Alpha',
                                            'late' => 'Terlambat',
                                            'excused' => 'Izin',
                                            'sick' => 'Sakit',
                                            'unknown' => 'Perlu Verifikasi'
                                        ];
                                    @endphp
                                    <span class="px-2.5 py-1 text-xs font-bold {{ $statusColors[$attendance->status] }} border rounded-lg">
                                        {{ $statusLabels[$attendance->status] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                    {{ $attendance->check_in_time ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $attendance->note ?? '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-sm">Belum ada riwayat kehadiran.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection