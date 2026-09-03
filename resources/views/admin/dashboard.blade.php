{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="space-y-8 pb-8">
    <!-- Welcome Banner -->
    <div class="relative overflow-hidden bg-linear-to-br from-blue-700 via-indigo-600 to-violet-700 rounded-3xl p-8 text-white shadow-xl shadow-blue-500/20">
        <!-- Abstract Shapes for Premium Feel -->
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
        
        <div class="relative flex items-center justify-between">
            <div class="flex-1">
                <h1 class="text-3xl font-black tracking-tight">Selamat Datang, {{ Auth::user()->name }}! 👋</h1>
                <p class="mt-2 text-lg text-blue-100 font-medium max-w-xl">
                    Sistem siap digunakan. Pantau aktivitas belajar mengajar dan kelola administrasi secara real-time hari ini.
                </p>
                <div class="mt-6 flex gap-3">
                    <div class="px-4 py-2 bg-white/20 backdrop-blur-md rounded-xl text-xs font-bold uppercase tracking-wider">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </div>
                    <div class="px-4 py-2 bg-emerald-500/20 backdrop-blur-md rounded-xl text-xs font-bold uppercase tracking-wider text-emerald-300 flex items-center">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full mr-2 animate-pulse"></span>
                        Sistem Aktif
                    </div>
                </div>
            </div>
            <div class="hidden lg:block">
                <div class="w-32 h-32 bg-white/10 backdrop-blur-md rounded-3xl flex items-center justify-center border border-white/20 shadow-inner">
                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 group hover:border-blue-500 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Kelas</p>
                    <p id="stat-total-classes" class="text-3xl font-black text-gray-900 mt-1">{{ $stats['total_classes'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-50 flex items-center text-xs text-gray-500">
                <span class="font-bold text-blue-600">Aktif</span>
                <span class="mx-2">•</span>
                Total kelas terdaftar
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 group hover:border-emerald-500 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Siswa</p>
                    <p id="stat-total-students" class="text-3xl font-black text-gray-900 mt-1">{{ $stats['total_students'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-50 flex items-center text-xs text-gray-500">
                <span class="font-bold text-emerald-600">Terdaftar</span>
                <span class="mx-2">•</span>
                Siswa aktif & pasif
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 group hover:border-violet-500 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Guru</p>
                    <p id="stat-total-teachers" class="text-3xl font-black text-gray-900 mt-1">{{ $stats['total_teachers'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-violet-50 rounded-2xl flex items-center justify-center text-violet-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-50 flex items-center text-xs text-gray-500">
                <span class="font-bold text-violet-600">Staf</span>
                <span class="mx-2">•</span>
                Guru & Tenaga Ahli
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 group hover:border-amber-500 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mata Pelajaran</p>
                    <p id="stat-total-subjects" class="text-3xl font-black text-gray-900 mt-1">{{ $stats['total_subjects'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-50 flex items-center text-xs text-gray-500">
                <span class="font-bold text-amber-600">Kurikulum</span>
                <span class="mx-2">•</span>
                Update terbaru
            </div>
        </div>
    </div>

    <!-- Lower Section: Presensi & Aktivitas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- Today's Attendance -->
        <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Presensi Siswa Hari Ini</h3>
                    <p class="text-xs text-gray-500">Statistik kehadiran real-time</p>
                </div>
                <div class="text-right">
                    <span id="stat-attendance-rate" class="text-2xl font-black text-blue-600">{{ $stats['attendance_rate'] ?? 0 }}%</span>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Bulan Ini</p>
                </div>
            </div>
            
            <div id="today-attendance-container">
                @if(isset($today_attendance) && $today_attendance->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @php
                            $statusMap = [
                                'present' => ['text' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'label' => 'Hadir'],
                                'sick' => ['text' => 'text-orange-700', 'bg' => 'bg-orange-50', 'label' => 'Sakit'],
                                'excused' => ['text' => 'text-sky-700', 'bg' => 'bg-sky-50', 'label' => 'Izin'],
                                'late' => ['text' => 'text-amber-700', 'bg' => 'bg-amber-50', 'label' => 'Terlambat'],
                                'absent' => ['text' => 'text-rose-700', 'bg' => 'bg-rose-50', 'label' => 'Alpha']
                            ];
                        @endphp
                        @foreach($statusMap as $statusCode => $style)
                            @php
                                $count = $today_attendance->where('status', $statusCode)->first()->total ?? 0;
                            @endphp
                            <div class="{{ $style['bg'] }} px-4 py-2.5 rounded-xl border border-transparent flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ str_replace('text', 'bg', $style['text']) }}"></span>
                                <span class="text-[11px] font-bold {{ $style['text'] }} uppercase tracking-wider">{{ $style['label'] }}</span>
                                <span class="text-sm font-black text-gray-900 ml-1">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 text-center py-6 italic text-sm">Belum ada data presensi hari ini</p>
                @endif
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100 flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Aktivitas Terbaru</h3>
                    <p class="text-xs text-gray-500">Log pengisian agenda terbaru</p>
                </div>
                <a href="{{ route('admin.activities.index') }}" class="text-xs font-bold text-blue-600 hover:underline">Lihat Semua</a>
            </div>
            
            <div id="recent-activities-list" class="space-y-4 overflow-y-auto max-h-[300px] custom-scrollbar pr-2">
                @forelse(($recent_activities ?? []) as $activity)
                <div class="flex items-center p-3 rounded-2xl hover:bg-gray-50 transition-all border border-transparent hover:border-gray-100">
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center shadow-sm mr-4 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $activity->title ?? 'No Title' }}</p>
                        <p class="text-[10px] text-gray-500 mt-0.5 truncate">
                            <span class="font-bold text-blue-600">{{ $activity->class->name ?? 'Kelas N/A' }}</span> • 
                            {{ $activity->teacher->name ?? 'Guru N/A' }}
                        </p>
                    </div>
                    <div class="text-right ml-3">
                        <p class="text-[10px] font-bold text-gray-400">{{ isset($activity->created_at) ? $activity->created_at->diffForHumans() : '-' }}</p>
                    </div>
                </div>
                @empty
                <p class="text-gray-400 text-center py-8 italic text-sm">Belum ada aktivitas</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-bold text-gray-900 mb-6">Aksi Cepat</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.classes.create') }}" class="flex flex-col items-center p-6 bg-blue-50 rounded-2xl hover:bg-blue-600 hover:text-white transition-all group">
                <div class="w-12 h-12 bg-white text-blue-600 rounded-xl flex items-center justify-center shadow-sm mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider">Tambah Kelas</span>
            </a>
            <a href="{{ route('admin.students.create') }}" class="flex flex-col items-center p-6 bg-emerald-50 rounded-2xl hover:bg-emerald-600 hover:text-white transition-all group">
                <div class="w-12 h-12 bg-white text-emerald-600 rounded-xl flex items-center justify-center shadow-sm mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider">Tambah Siswa</span>
            </a>
            <a href="{{ route('admin.teachers.create') }}" class="flex flex-col items-center p-6 bg-violet-50 rounded-2xl hover:bg-violet-600 hover:text-white transition-all group">
                <div class="w-12 h-12 bg-white text-violet-600 rounded-xl flex items-center justify-center shadow-sm mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider">Tambah Guru</span>
            </a>
            <a href="{{ route('admin.schedules.index') }}" class="flex flex-col items-center p-6 bg-amber-50 rounded-2xl hover:bg-amber-600 hover:text-white transition-all group">
                <div class="w-12 h-12 bg-white text-amber-600 rounded-xl flex items-center justify-center shadow-sm mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider">Atur Jadwal</span>
            </a>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const agendaCanvas = document.getElementById('agendaChart');
        let agendaChart;
        
        // Fungsi helper untuk meng-update metrik teks total dan rata-rata
        function updateAgendaMetrics(agendaData) {
            if (agendaData && agendaData.length > 0) {
                const total = agendaData.reduce((sum, item) => sum + parseInt(item.total), 0);
                const avg = Math.round(total / agendaData.length);
                
                const totalMetric = document.getElementById('agenda-total-metric');
                const avgMetric = document.getElementById('agenda-avg-metric');
                if (totalMetric) totalMetric.innerText = total + ' Agenda';
                if (avgMetric) avgMetric.innerText = avg + ' / Bulan';
            } else {
                const totalMetric = document.getElementById('agenda-total-metric');
                const avgMetric = document.getElementById('agenda-avg-metric');
                if (totalMetric) totalMetric.innerText = '0';
                if (avgMetric) avgMetric.innerText = '0';
            }
        }

        // Initialize Chart
        if (agendaCanvas && @json($monthly_agendas ?? []).length > 0) {
            const agendaData = @json($monthly_agendas ?? []);
            
            // Hitung metrik awal
            updateAgendaMetrics(agendaData);

            agendaChart = new Chart(agendaCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: agendaData.map(item => {
                        const [year, month] = item.month.split('-');
                        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                        return monthNames[parseInt(month) - 1] + ' ' + year;
                    }),
                    datasets: [{
                        label: 'Jumlah Agenda',
                        data: agendaData.map(item => item.total),
                        backgroundColor: 'rgba(79, 70, 229, 0.85)',
                        hoverBackgroundColor: 'rgba(79, 70, 229, 1)',
                        borderRadius: 8,
                        borderSkipped: false,
                        barThickness: 24,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleFont: { size: 11, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ` ${context.raw} Agenda`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 10, weight: '600' },
                                color: '#6b7280'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f3f4f6',
                                drawBorder: false
                            },
                            ticks: {
                                font: { size: 10, weight: '600' },
                                color: '#6b7280'
                            }
                        }
                    }
                }
            });
        } else if (agendaCanvas) {
            agendaCanvas.style.display = 'none';
            agendaCanvas.parentElement.insertAdjacentHTML('beforeend', '<p id="no-agenda-msg" class="text-gray-500 text-center py-8">Belum ada data agenda</p>');
        }

        // Real-time updates via Polling (throttled, no-overlap, backoff)
        function updateDashboard() {
            if (document.hidden) return Promise.resolve();
            fetch('{{ route('admin.dashboard.api.stats') }}')
                .then(response => response.json())
                .then(data => {
                    // Update Stats
                    document.getElementById('stat-total-classes').innerText = data.stats.total_classes;
                    document.getElementById('stat-total-students').innerText = data.stats.total_students;
                    document.getElementById('stat-total-teachers').innerText = data.stats.total_teachers;
                    document.getElementById('stat-total-subjects').innerText = data.stats.total_subjects;
                    document.getElementById('stat-attendance-rate').innerText = data.stats.attendance_rate + '%';

                    // Update Today's Attendance Cards dynamically (Real-time)
                    const attendanceContainer = document.getElementById('today-attendance-container');
                    if (attendanceContainer) {
                        if (data.today_attendance && data.today_attendance.length > 0) {
                            const styles = {
                                'present': { bg: 'bg-emerald-50', text: 'text-emerald-700', bar: 'bg-emerald-500', label: 'Hadir' },
                                'sick': { bg: 'bg-orange-50', text: 'text-orange-700', bar: 'bg-orange-500', label: 'Sakit' },
                                'excused': { bg: 'bg-sky-50', text: 'text-sky-700', bar: 'bg-sky-500', label: 'Izin' },
                                'late': { bg: 'bg-amber-50', text: 'text-amber-700', bar: 'bg-amber-500', label: 'Terlambat' },
                                'absent': { bg: 'bg-rose-50', text: 'text-rose-700', bar: 'bg-rose-500', label: 'Alpha' }
                            };

                            const statusOrder = ['present', 'sick', 'excused', 'late', 'absent'];
                            let cardsHtml = '<div class="flex flex-wrap gap-2">';
                            
                            statusOrder.forEach(status => {
                                const style = styles[status];
                                const bgDot = style.text.replace('text', 'bg');
                                const attData = data.today_attendance.find(a => a.status === status);
                                const total = attData ? attData.total : 0;
                                
                                cardsHtml += `
                                    <div class="${style.bg} px-4 py-2.5 rounded-xl border border-transparent flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full ${bgDot}"></span>
                                        <span class="text-[11px] font-bold ${style.text} uppercase tracking-wider">${style.label}</span>
                                        <span class="text-sm font-black text-gray-900 ml-1">${total}</span>
                                    </div>
                                `;
                            });
                            cardsHtml += '</div>';
                            attendanceContainer.innerHTML = cardsHtml;
                        } else {
                            attendanceContainer.innerHTML = `
                                <div class="flex flex-col items-center justify-center py-12 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-gray-500 text-sm font-medium">Belum ada data presensi hari ini</p>
                                </div>
                            `;
                        }
                    }
                    
                    // Update Recent Activities
                    const activitiesList = document.getElementById('recent-activities-list');
                    if (data.recent_activities.length > 0) {
                        activitiesList.innerHTML = data.recent_activities.map(activity => {
                            const timeAgo = activity.created_at ? new Date(activity.created_at) : new Date();
                            const now = new Date();
                            const diffInSeconds = Math.floor((now - timeAgo) / 1000);
                            
                            let timeText = 'Baru saja';
                            if (diffInSeconds > 60) {
                                const diffInMinutes = Math.floor(diffInSeconds / 60);
                                if (diffInMinutes > 60) {
                                    const diffInHours = Math.floor(diffInMinutes / 60);
                                    timeText = `${diffInHours} jam yang lalu`;
                                } else {
                                    timeText = `${diffInMinutes} menit yang lalu`;
                                }
                            }

                            return `
                            <div class="flex items-center p-3 rounded-2xl hover:bg-gray-50 transition-all border border-transparent hover:border-gray-100">
                                <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center shadow-sm mr-4 flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate">${activity.title || 'No Title'}</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5 truncate">
                                        <span class="font-bold text-blue-600">${activity.class ? activity.class.name : 'Kelas N/A'}</span> • 
                                        ${activity.teacher ? activity.teacher.name : 'Guru N/A'}
                                    </p>
                                </div>
                                <div class="text-right ml-3">
                                    <p class="text-[10px] font-bold text-gray-400">${timeText}</p>
                                </div>
                            </div>
                        `}).join('');
                    }
                    
                    // Update Chart & Metrics if data exists
                    if (data.monthly_agendas && data.monthly_agendas.length > 0) {
                        // Update metrik teks
                        updateAgendaMetrics(data.monthly_agendas);

                        if (agendaChart) {
                            agendaChart.data.labels = data.monthly_agendas.map(item => {
                                const [year, month] = item.month.split('-');
                                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                return monthNames[parseInt(month) - 1] + ' ' + year;
                            });
                            agendaChart.data.datasets[0].data = data.monthly_agendas.map(item => item.total);
                            agendaChart.update();
                            
                            // Show chart if it was hidden
                            if (agendaCanvas.style.display === 'none') {
                                agendaCanvas.style.display = 'block';
                                const msg = document.getElementById('no-agenda-msg');
                                if (msg) msg.remove();
                            }
                        }
                    }
                })
                .catch(error => console.error('Error fetching dashboard stats:', error));
        }

        // Smart polling: no overlap + backoff + pause when tab hidden.
        // Interval 30s sekarang sinkron dengan TTL cache 120s di server.
        poll(updateDashboard, { interval: 30000, backoffMax: 120000 });
    });
</script>
@endpush
@endsection