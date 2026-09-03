{{-- resources/views/admin/search_results.blade.php --}}
@extends('layouts.admin')

@section('title', 'Hasil Pencarian')
@section('header', 'Hasil Pencarian: "' . $query . '"')

@section('content')
<div class="space-y-8">
    <!-- Search Summary -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Menampilkan hasil untuk "{{ $query }}"</h2>
                <p class="text-sm text-gray-500 mt-1">Ditemukan di berbagai kategori data</p>
            </div>
            <div class="bg-blue-50 text-blue-600 px-4 py-2 rounded-xl text-sm font-medium">
                {{ $totalResults }} Total Hasil
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Students Results -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-green-50 text-green-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800">Siswa ({{ $resultStudents->count() }})</h3>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($resultStudents as $student)
                <a href="{{ route('admin.students.show', $student) }}" class="flex items-center p-4 hover:bg-gray-50 transition-all group">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold mr-4 transition-transform group-hover:scale-110">
                        {{ substr($student->name, 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $student->name }}</p>
                        <p class="text-xs text-gray-500 truncate">
                            NIS: {{ $student->nis }} • 
                            Kelas: {{ $student->class->name ?? '-' }}
                        </p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @empty
                <div class="p-8 text-center text-gray-500 text-sm italic">Tidak ada siswa ditemukan</div>
                @endforelse
            </div>
        </div>

        <!-- Teachers Results -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800">Guru ({{ $resultTeachers->count() }})</h3>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($resultTeachers as $teacher)
                <a href="{{ route('admin.teachers.show', $teacher) }}" class="flex items-center p-4 hover:bg-gray-50 transition-all group">
                    <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-700 font-bold mr-4 transition-transform group-hover:scale-110">
                        {{ substr($teacher->name, 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $teacher->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $teacher->email }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @empty
                <div class="p-8 text-center text-gray-500 text-sm italic">Tidak ada guru ditemukan</div>
                @endforelse
            </div>
        </div>

        <!-- Classes Results -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800">Kelas ({{ $resultClasses->count() }})</h3>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($resultClasses as $class)
                <a href="{{ route('admin.classes.show', $class) }}" class="flex items-center p-4 hover:bg-gray-50 transition-all group">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-700 font-bold mr-4 transition-transform group-hover:scale-110">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $class->name }}</p>
                        <p class="text-xs text-gray-500 truncate">
                            Wali Kelas: {{ $class->homeroomTeacher->name ?? '-' }}
                        </p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @empty
                <div class="p-8 text-center text-gray-500 text-sm italic">Tidak ada kelas ditemukan</div>
                @endforelse
            </div>
        </div>

        <!-- Subjects Results -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-yellow-50 text-yellow-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800">Mata Pelajaran ({{ $resultSubjects->count() }})</h3>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($resultSubjects as $subject)
                <a href="{{ route('admin.subjects.show', $subject) }}" class="flex items-center p-4 hover:bg-gray-50 transition-all group">
                    <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center text-yellow-700 font-bold mr-4 text-[10px] transition-transform group-hover:scale-110">
                        {{ strtoupper(substr($subject->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $subject->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $subject->teachers->pluck('name')->implode(', ') ?: 'Belum Ditentukan' }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @empty
                <div class="p-8 text-center text-gray-500 text-sm italic">Tidak ada mata pelajaran ditemukan</div>
                @endforelse
            </div>
        </div>

        <!-- Academic Years Results -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-red-50 text-red-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800">Tahun Akademik ({{ $resultAcademicYears->count() }})</h3>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($resultAcademicYears as $ay)
                <a href="{{ route('admin.academic-years.index') }}" class="flex items-center p-4 hover:bg-gray-50 transition-all group">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $ay->name }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @empty
                <div class="p-8 text-center text-gray-500 text-sm italic">Tidak ada tahun akademik ditemukan</div>
                @endforelse
            </div>
        </div>

        <!-- Rooms Results -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-orange-50 text-orange-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-800">Ruangan ({{ $resultRooms->count() }})</h3>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($resultRooms as $room)
                <a href="{{ route('admin.rooms.index') }}" class="flex items-center p-4 hover:bg-gray-50 transition-all group">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $room->name }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @empty
                <div class="p-8 text-center text-gray-500 text-sm italic">Tidak ada ruangan ditemukan</div>
                @endforelse
            </div>
        </div>
    </div>


    <!-- Agenda Results -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-50">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800">Agenda Kelas ({{ $resultAgendas->count() }})</h3>
            </div>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($resultAgendas as $agenda)
            <div class="p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-3">
                        <div class="w-1.5 h-8 bg-indigo-500 rounded-full mt-1"></div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $agenda->title }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Kelas: {{ $agenda->class->name ?? '-' }} • 
                                Guru: {{ $agenda->teacher->name ?? '-' }} • 
                                Tanggal: {{ \Carbon\Carbon::parse($agenda->date)->translatedFormat('d F Y') }}
                            </p>
                            <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ strip_tags($agenda->description) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-gray-500 text-sm italic">Tidak ada agenda ditemukan</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
