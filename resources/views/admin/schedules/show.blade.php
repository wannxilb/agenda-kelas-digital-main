{{-- resources/views/admin/schedules/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Jadwal')
@section('header', 'Detail Jadwal Pelajaran')

@section('content')
<div id="schedule-detail-content" class="pb-12">
    <!-- Breadcrumb & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.schedules.index') }}" class="hover:text-indigo-600 transition-colors">Jadwal Pelajaran</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">Detail Jadwal</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">Jadwal: {{ $schedule->class->name }} - {{ $schedule->subject->name }}</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.schedules.edit', $schedule) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Jadwal
            </a>
            <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 transition-all">
                Kembali
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Detail Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-linear-to-r from-blue-600 to-indigo-600 px-6 py-8 flex flex-col items-center text-center">
                    <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm shadow-inner mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">{{ $schedule->class->name }}</h2>
                    <p class="text-blue-100 font-medium mt-1">{{ $schedule->subject->name }}</p>
                </div>
                
                <div class="p-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-4">Informasi Jadwal</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-50">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Hari</span>
                            @php
                                $dayNames = [
                                    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                                    'Thursday' => 'Kamis', 'Friday' => "Jum'at"
                                ];
                            @endphp
                            <span class="px-2.5 py-1 text-xs font-bold bg-blue-50 text-blue-700 rounded-lg border border-blue-100">
                                {{ $dayNames[$schedule->day] }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-50">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Waktu</span>
                            <span class="text-sm font-bold text-gray-900">
                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }} WIB
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-50">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Durasi</span>
                            <span class="text-sm font-medium text-gray-700">
                                @php
                                    $start = \Carbon\Carbon::parse($schedule->start_time);
                                    $end = \Carbon\Carbon::parse($schedule->end_time);
                                    $duration = $start->diffInMinutes($end);
                                @endphp
                                {{ floor($duration / 60) }} Jam {{ $duration % 60 }} Menit
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-50">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Ruangan</span>
                            <span class="text-sm font-medium text-gray-900">{{ $schedule->room_model ? $schedule->room_model->name : ($schedule->room ?? '-') }}</span>
                        </div>
                    </div>

                    <h3 class="text-sm font-bold text-gray-900 mt-6 mb-4">Guru Pengampu</h3>
                    <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="w-12 h-12 bg-linear-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-sm">
                            {{ strtoupper(substr($schedule->teacher->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-gray-900 truncate">{{ $schedule->teacher->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $schedule->teacher->nip ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Preview Jadwal -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900">Preview Jadwal Kelas {{ $schedule->class->name }}</h3>
                </div>
                
                <div class="overflow-x-auto p-6">
                    @php
                        $allSchedules = App\Models\Schedule::where('class_id', $schedule->class_id)
                            ->with(['subject', 'teacher'])
                            ->orderBy('day')
                            ->orderBy('start_time')
                            ->get()
                            ->groupBy('day');
                            
// Time slots dari pengaturan "Jam Pelajaran" (setting schedule_time_slots)
                    $timeSlots = \App\Models\Setting::scheduleTimeSlots();
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                    @endphp
                    
                    <table class="min-w-full border-collapse border border-gray-100 rounded-xl overflow-hidden">
                        <thead>
                            <tr>
                                <th class="border-b border-r border-gray-100 p-3 bg-gray-50 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jam Mulai</th>
                                @foreach($days as $day)
                                    <th class="border-b border-r border-gray-100 p-3 bg-gray-50 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider last:border-r-0">{{ $dayNames[$day] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeSlots as $slot)
                            <tr>
                                <td class="border-b border-r border-gray-100 p-3 text-xs font-bold text-gray-700 bg-gray-50/50">{{ $slot['time'] }}</td>
                                @if($slot['label'])
                                    <td colspan="5" class="border-b border-gray-100 p-3 bg-gray-50 text-center">
                                        <div class="flex items-center justify-center space-x-2 text-gray-500 py-3">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span class="text-xs font-bold uppercase tracking-[0.2em]">{{ $slot['label'] }}</span>
                                        </div>
                                    </td>
                                @else
                                    @foreach($days as $day)
                                        <td class="border-b border-r border-gray-100 p-2 align-top min-h-[80px] bg-white last:border-r-0" style="height: 80px;">
                                            @php
                                                $scheduleAtTime = null;
                                                if(isset($allSchedules[$day])) {
                                                    foreach($allSchedules[$day] as $sch) {
                                                        $startTimeStr = \Carbon\Carbon::parse($sch->start_time)->format('H:i');
                                                        if($startTimeStr == $slot['time']) {
                                                            $scheduleAtTime = $sch;
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            @if($scheduleAtTime)
                                                <div class="bg-blue-50/80 rounded-xl p-2.5 text-xs border border-blue-100 {{ $scheduleAtTime->id == $schedule->id ? 'ring-2 ring-blue-500 shadow-sm' : '' }}">
                                                    <p class="font-bold text-blue-900 leading-tight mb-1">{{ $scheduleAtTime->subject->name }}</p>
                                                    <p class="text-[10px] font-medium text-blue-600 line-clamp-1">{{ $scheduleAtTime->teacher->name }}</p>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection