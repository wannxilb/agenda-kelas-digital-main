{{-- resources/views/admin/academic_records/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Riwayat Siswa - ' . $class->name . ' (' . $yearName . ')')
@section('header', 'Riwayat Siswa - ' . $class->name)

@section('content')
<div class="pb-12">
    <div class="mb-8">
        <a href="{{ route('admin.academic-records.index', ['academic_year_name' => $yearName]) }}" class="text-sm text-indigo-600 hover:underline">
            &larr; Kembali ke daftar kelas
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $class->name }} - Tahun {{ $yearName }}</h1>
        <p class="text-gray-600">Wali Kelas: {{ $histories->first()?->homeroomTeacher?->name ?? 'Data tidak tersimpan' }}</p>
    </div>

    <div class="space-y-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 font-bold text-gray-800">Data Siswa ({{ $histories->count() }} siswa)</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">NIS</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($histories as $index => $history)
                        @php
                            $userStatus = $history->user->status ?? 'unknown';
                            $gradeLevel = $class->grade_level ?? '';
                            
                            if ($gradeLevel === 'XII' && $userStatus === 'graduated') {
                                $statusLabel = 'Lulus';
                                $statusClass = 'bg-purple-100 text-purple-800';
                            } elseif ($userStatus === 'active') {
                                $statusLabel = 'Aktif';
                                $statusClass = 'bg-green-100 text-green-800';
                            } elseif ($userStatus === 'inactive') {
                                $statusLabel = 'Non-Aktif';
                                $statusClass = 'bg-red-100 text-red-700';
                            } elseif ($userStatus === 'graduated') {
                                $statusLabel = 'Lulus';
                                $statusClass = 'bg-purple-100 text-purple-800';
                            } else {
                                $statusLabel = 'Tidak Diketahui';
                                $statusClass = 'bg-gray-100 text-gray-500';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $history->user->nis ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $history->user->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
