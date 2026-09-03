{{-- resources/views/admin/teachers/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Guru - ' . $teacher->name)
@section('header', 'Detail Guru')

@section('content')
<div id="teacher-detail-content" class="pb-12">
    <!-- Breadcrumb & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.teachers.index') }}" class="hover:text-indigo-600 transition-colors">Guru</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">Detail Guru</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">{{ $teacher->name }}</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.teachers.edit', $teacher) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Guru
            </a>
            <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 transition-all">
                Kembali
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Profile Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-linear-to-r from-purple-600 to-pink-600 px-6 py-8 flex flex-col items-center">
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-lg mb-4">
                        <span class="text-4xl font-bold text-purple-600">
                            {{ strtoupper(substr($teacher->name, 0, 1)) }}
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-white text-center">{{ $teacher->name }}</h2>
                    <p class="text-purple-100 mt-1">NIP: {{ $teacher->nip ?? '-' }}</p>
                </div>
                
                <div class="p-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-4">Informasi Kontak</h3>
                    <div class="space-y-4 text-sm">
                        <div class="flex flex-col py-2 border-b border-gray-50">
                            <span class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Email</span>
                            <span class="font-medium text-gray-900 break-all">{{ $teacher->email }}</span>
                        </div>
                        <div class="flex flex-col py-2 border-b border-gray-50">
                            <span class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Telepon</span>
                            <span class="font-medium text-gray-900">{{ $teacher->phone ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col py-2 border-b border-gray-50">
                            <span class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Alamat</span>
                            <span class="font-medium text-gray-900">{{ $teacher->address ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Teaching Stats Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-4">Statistik Mengajar</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 text-center">
                        <p class="text-2xl font-black text-blue-600">{{ $teaching_stats['total_subjects'] }}</p>
                        <p class="text-[10px] font-bold text-blue-800 uppercase tracking-wider mt-1">Mapel</p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 border border-green-100 text-center">
                        <p class="text-2xl font-black text-green-600">{{ $teaching_stats['total_agendas'] }}</p>
                        <p class="text-[10px] font-bold text-green-800 uppercase tracking-wider mt-1">Total Agenda</p>
                    </div>
                    <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-100 text-center">
                        <p class="text-2xl font-black text-yellow-600">{{ $teaching_stats['monthly_agendas'] }}</p>
                        <p class="text-[10px] font-bold text-yellow-800 uppercase tracking-wider mt-1">Agenda Bulan Ini</p>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-4 border border-purple-100 text-center">
                        <p class="text-2xl font-black text-purple-600">{{ $teacher->classHomeroom->count() }}</p>
                        <p class="text-[10px] font-bold text-purple-800 uppercase tracking-wider mt-1">Kelas Wali</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Mata Pelajaran yang Diampu -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900">Mata Pelajaran yang Diampu</h3>
                    <a href="{{ route('admin.subjects.create', ['teacher_id' => $teacher->id]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                        + Tambah Mapel
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">JP</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($teacher->subjects as $subject)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $subject->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $subject->credit_hours }} Jam/Minggu</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <a href="{{ route('admin.subjects.edit', $subject) }}" class="text-green-600 hover:text-green-800 transition-colors">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                        <p class="text-sm">Belum ada mata pelajaran yang diampu</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Riwayat Agenda -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900">Riwayat Agenda Terbaru</h3>
                </div>
                
                <div class="divide-y divide-gray-100">
                    @forelse($teacher->agendas()->latest()->take(5)->get() as $agenda)
                    <div class="p-6 hover:bg-gray-50/50 transition-colors">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="text-sm font-bold text-gray-900">{{ $agenda->title }}</h4>
                            <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider {{ $agenda->status == 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }} rounded-md">
                                {{ $agenda->status == 'published' ? 'Dipublikasikan' : 'Draf' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed mb-3">{{ strip_tags($agenda->description) }}</p>
                        <div class="flex items-center space-x-4 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            <span class="flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $agenda->date->format('d M Y') }}
                            </span>
                            <span class="flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                {{ $agenda->class->name }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-sm text-gray-500">Belum ada agenda yang dibuat.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection