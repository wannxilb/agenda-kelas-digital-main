{{-- resources/views/admin/reports/attendance.blade.php --}}
@extends('layouts.admin')

@section('title', 'Laporan Presensi')
@section('header', 'Laporan Presensi Siswa')

@section('content')
<div class="space-y-8 pb-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Laporan Presensi</h1>
            <p class="text-gray-500 mt-1 font-medium">Analisis data kehadiran siswa berdasarkan filter periode dan kelas.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.reports.export.pdf', request()->query()) }}" target="_blank"
               class="inline-flex items-center px-5 py-2.5 bg-rose-50 text-rose-700 rounded-2xl border border-rose-100 text-xs font-bold uppercase tracking-wider hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export PDF
            </a>
            <a href="{{ route('admin.reports.export.excel', request()->query()) }}"
               class="inline-flex items-center px-5 py-2.5 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100 text-xs font-bold uppercase tracking-wider hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <div class="flex items-center mb-6 pb-2 border-b border-gray-50">
            <div class="w-2 h-6 bg-blue-600 rounded-full mr-3"></div>
            <h3 class="text-lg font-bold text-gray-900">Filter Pencarian</h3>
        </div>
        <form method="GET" action="{{ route('admin.reports.attendance') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Tahun Ajaran</label>
                <select name="academic_year_id" class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm font-medium">
                    <option value="">Tahun Ajaran (Aktif)</option>
                    @foreach($academicYears->groupBy('name') as $name => $years)
                        @php
                            $ids = $years->pluck('id')->implode(',');
                        @endphp
                        <option value="{{ $ids }}" {{ request('academic_year_id') == $ids ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Pilih Kelas</label>
                <select name="class_id" class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm font-medium">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate ?? request('start_date') }}" 
                       class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm font-medium">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $endDate ?? request('end_date') }}" 
                       class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm font-medium">
            </div>
            <div class="lg:col-span-4 flex justify-end pt-2">
                <button type="submit" class="w-full md:w-auto px-10 py-3.5 bg-gray-900 text-white rounded-xl font-bold text-sm hover:bg-gray-800 transition-all shadow-lg shadow-gray-200 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Tampilkan Laporan
                </button>
            </div>
        </form>
    </div>
    
    <!-- Summary Stats Bar -->
    <div class="flex flex-wrap items-center gap-6 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-gray-900"></span>
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total:</span>
            <span class="text-sm font-black text-gray-900">{{ $summary['total'] }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Hadir:</span>
            <span class="text-sm font-black text-emerald-600">{{ $summary['present'] }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Alpha:</span>
            <span class="text-sm font-black text-rose-600">{{ $summary['absent'] }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-orange-500"></span>
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Sakit:</span>
            <span class="text-sm font-black text-orange-600">{{ $summary['sick'] }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-sky-500"></span>
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Izin:</span>
            <span class="text-sm font-black text-sky-600">{{ $summary['excused'] }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Terlambat:</span>
            <span class="text-sm font-black text-amber-600">{{ $summary['late'] }}</span>
        </div>
    </div>
    
    <!-- Tabel Data Presensi -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-900">Data Ringkasan Presensi Siswa</h3>
            <span class="px-4 py-1.5 bg-white text-[10px] font-bold text-gray-400 uppercase tracking-widest rounded-xl border border-gray-100">{{ $students->total() }} Siswa</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead>
                    <tr class="bg-gray-50/30">
                        <th class="px-8 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Siswa</th>
                        <th class="px-8 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kelas</th>
                        <th class="px-8 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Hadir</th>
                        <th class="px-8 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Alpha</th>
                        <th class="px-8 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sakit</th>
                        <th class="px-8 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Izin</th>
                        <th class="px-8 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Terlambat</th>
                        <th class="px-8 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($students as $student)
                    @php
                        $counts = $studentCounts[$student->id] ?? ['present' => 0, 'absent' => 0, 'sick' => 0, 'excused' => 0, 'late' => 0];
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-8 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-9 h-9 bg-linear-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white text-[10px] font-black shadow-sm mr-3">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate">{{ $student->name }}</div>
                                    <div class="text-[10px] font-medium text-gray-400">NIS: {{ $student->nis }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-4 whitespace-nowrap">
                            @php
                                $historicalClass = $student->classHistories->first();
                                $displayClass = $historicalClass ? $historicalClass->class : $student->class;
                            @endphp
                            <span class="px-3 py-1 text-[10px] font-bold bg-blue-50 text-blue-700 rounded-lg border border-blue-100 uppercase tracking-wider">
                                {{ $displayClass->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-8 py-4 text-center text-sm font-bold text-emerald-600">{{ $counts['present'] }}</td>
                        <td class="px-8 py-4 text-center text-sm font-bold text-rose-600">{{ $counts['absent'] }}</td>
                        <td class="px-8 py-4 text-center text-sm font-bold text-orange-600">{{ $counts['sick'] }}</td>
                        <td class="px-8 py-4 text-center text-sm font-bold text-sky-600">{{ $counts['excused'] }}</td>
                        <td class="px-8 py-4 text-center text-sm font-bold text-amber-600">{{ $counts['late'] }}</td>
                        <td class="px-8 py-4 whitespace-nowrap">
                            <button onclick="viewStudentAttendance({{ $student->id }}, '{{ $student->name }}')" 
                                    class="text-blue-600 hover:text-blue-900 font-bold text-xs uppercase tracking-wider">
                                Lihat Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-8 py-20 text-center">
                            <div class="text-gray-500">Tidak ada data siswa ditemukan.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($students->hasPages())
        <div class="px-8 py-6 border-t border-gray-50 bg-gray-50/30">
            {{ $students->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Detail Presensi -->
<div id="attendanceModal" class="fixed inset-0 bg-gray-900/10 backdrop-blur-md hidden items-center justify-center z-50 transition-all duration-300">
    <div class="bg-white/90 rounded-3xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden border border-white/50 backdrop-blur-md">
        <div class="px-8 py-6 border-b border-gray-200/50 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900" id="modalTitle">Detail Presensi</h3>
            <div class="flex items-center gap-2">
                <select id="monthFilter" onchange="loadStudentData()" class="bg-gray-50 border-transparent rounded-xl text-xs font-bold text-gray-600 px-3 py-2">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ date('m') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
        <div class="px-8 py-6 max-h-[60vh] overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead>
                    <tr>
                        <th class="px-2 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</th>
                        <th class="px-2 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-2 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Keterangan</th>
                    </tr>
                </thead>
                <tbody id="modalBody" class="divide-y divide-gray-50">
                    <!-- Data akan dimuat lewat JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let currentStudentId = null;
    let currentStudentName = '';

    async function viewStudentAttendance(studentId, studentName) {
        currentStudentId = studentId;
        currentStudentName = studentName;
        loadStudentData();
    }

    async function loadStudentData() {
        document.getElementById('modalTitle').innerText = 'Detail Presensi: ' + currentStudentName;
        const month = document.getElementById('monthFilter').value;
        const modalBody = document.getElementById('modalBody');
        
        modalBody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-gray-500 italic">Memuat data...</td></tr>';
        document.getElementById('attendanceModal').classList.remove('hidden');
        document.getElementById('attendanceModal').classList.add('flex');

        try {
            const response = await fetch(`/admin/reports/attendance/student/${currentStudentId}?month=${month}&year={{ date('Y') }}`);
            const student = await response.json();
            
            modalBody.innerHTML = '';
            if (student.attendances.length === 0) {
                modalBody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-gray-500">Tidak ada riwayat presensi di bulan ini.</td></tr>';
            } else {
                const statusLabels = {'present': 'Hadir', 'absent': 'Alpha', 'sick': 'Sakit', 'late': 'Terlambat', 'excused': 'Izin'};
                student.attendances.forEach(att => {
                    modalBody.innerHTML += `
                        <tr>
                            <td class="px-2 py-3 text-sm text-gray-900 font-medium">${att.date}</td>
                            <td class="px-2 py-3 text-sm font-bold text-gray-700">${statusLabels[att.status] || att.status}</td>
                            <td class="px-2 py-3 text-sm text-gray-500">${att.note || '-'}</td>
                        </tr>
                    `;
                });
            }
        } catch (error) {
            modalBody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-rose-500 font-medium">Gagal memuat data.</td></tr>';
        }
    }

    function closeModal() {
        document.getElementById('attendanceModal').classList.add('hidden');
        document.getElementById('attendanceModal').classList.remove('flex');
    }
</script>
@endsection
