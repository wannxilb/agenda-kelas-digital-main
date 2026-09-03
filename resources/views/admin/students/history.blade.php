{{-- resources/views/admin/students/history.blade.php --}}
@extends('layouts.admin')

@section('title', 'Riwayat Lengkap Siswa - ' . $student->name)
@section('header', 'Riwayat Siswa')

@section('content')
<div class="pb-12">
    <div class="flex items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.students.index') }}" class="hover:text-indigo-600 transition-colors">Siswa</a>
                <span class="mx-2">/</span>
                <a href="{{ route('admin.students.show', $student) }}" class="hover:text-indigo-600 transition-colors">{{ $student->name }}</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">Riwayat Lengkap</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Akademik {{ $student->name }}</h1>
        </div>
        <a href="{{ route('admin.students.show', $student) }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 transition-all">
            Kembali ke Detail
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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
                            Belum ada riwayat kelas yang tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
