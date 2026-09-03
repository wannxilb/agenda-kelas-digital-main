{{-- resources/views/admin/subjects/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Manajemen Mata Pelajaran')
@section('header', 'Manajemen Mata Pelajaran')

@section('content')
<div class="space-y-8 pb-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 mb-6">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Mata Pelajaran</h1>
            <p class="mt-2 text-base text-gray-500 max-w-2xl">Kelola data mata pelajaran, guru pengampu, dan beban jam pelajaran.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 sm:justify-end">
            {{-- Impor & Ekspor group --}}
            <div class="inline-flex items-center rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden divide-x divide-gray-200">
                <button type="button" id="btn-open-subject-wizard" onclick="openSubjectWizard()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-50 transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Impor
                </button>
                <a href="{{ route('admin.subjects.export.template') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    </svg>
                    Ekspor Template
                </a>
            </div>
            {{-- Primary action --}}
            <a href="{{ route('admin.subjects.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-linear-to-r from-teal-600 to-emerald-600 rounded-xl shadow-lg shadow-teal-500/20 hover:from-teal-700 hover:to-emerald-700 hover:shadow-xl transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Mata Pelajaran
            </a>
        </div>
    </div>

    {{-- Import Modal --}}
    <div id="importModalSubject" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title-subject" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeSubjectImport()"></div>
            {{-- Modal Panel --}}
            <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all">
                <form action="{{ route('admin.subjects.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900" id="modal-title-subject">Import Mata Pelajaran</h3>
                            <button type="button" onclick="closeSubjectImport()" class="text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-lg p-1.5 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4">
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-emerald-500 transition-all hover:bg-emerald-50 cursor-pointer group"
                                 onclick="document.getElementById('subjectFile').click()">
                                <input type="file" name="file" id="subjectFile" class="hidden" accept=".xlsx,.xls,.csv" required onchange="subjectFileSelected(this)">
                                <div class="w-16 h-16 mx-auto bg-emerald-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-700 font-medium">Klik untuk pilih file atau drag and drop</p>
                                <p class="text-sm text-gray-500 mt-2">Format: .xlsx, .xls, .csv (Maks 2 MB)</p>
                                <div id="subjectFileName" class="mt-4 text-sm font-semibold text-emerald-600 bg-emerald-50 py-2 px-4 rounded-lg hidden"></div>
                            </div>

                            <div class="mt-4 text-xs text-gray-500">
                                💡 Unduh <a href="{{ route('admin.subjects.export.template') }}" class="underline font-semibold text-emerald-600">template Excel</a> sebelum mengisi data.
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-100">
                        <button type="button" onclick="closeSubjectImport()"
                                class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 shadow-sm transition-colors">
                            Import Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-2">
        <form method="GET" action="{{ route('admin.subjects.index') }}" class="flex flex-col lg:flex-row gap-4">
            <!-- Search Input -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari kode atau nama mata pelajaran..." 
                       class="block w-full pl-12 pr-4 py-3.5 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm">
            </div>

            <!-- Filter Guru -->
            <div class="relative w-full lg:w-64">
                <select name="teacher_id" onchange="this.form.submit()" 
                        class="appearance-none block w-full pl-4 pr-10 py-3.5 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm font-semibold text-gray-700">
                    <option value="">Semua Guru Pengampu</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center px-6 py-3.5 text-sm font-semibold text-white bg-gray-800 rounded-xl hover:bg-gray-900 transition-all duration-200">
                    Cari
                </button>
                @if(request('search') || request('teacher_id') || request('per_page'))
                    <a href="{{ route('admin.subjects.index') }}" 
                       class="inline-flex items-center justify-center p-3.5 text-red-500 hover:bg-red-50 rounded-xl transition-all" 
                       title="Reset Filter">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Mata Pelajaran -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Guru Pengampu</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">JP</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Statistik</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($subjects as $subject)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-linear-to-br from-teal-500 to-emerald-600 rounded-xl flex items-center justify-center text-white text-sm font-bold shadow-sm">
                                    {{ strtoupper(substr($subject->name, 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-bold text-gray-900">{{ $subject->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center mr-3 border border-purple-100">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $subject->teachers->pluck('name')->implode(', ') ?: '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100 rounded-lg">
                                {{ $subject->credit_hours }} Jam/Minggu
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900">{{ $subject->schedules_count }} Jadwal</span>
                                <span class="text-[10px] text-gray-500">{{ $subject->agendas_count }} Agenda dibuat</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex space-x-3">
                                <a href="{{ route('admin.subjects.show', $subject) }}" class="text-gray-400 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.subjects.edit', $subject) }}" class="text-gray-400 hover:text-green-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus mata pelajaran ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Belum Ada Mata Pelajaran</h3>
                                <p class="text-gray-500 mt-1">Silakan tambahkan data mata pelajaran untuk memulai.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($subjects->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $subjects->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    async function pollSubjects() {
        try {
            const response = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            });
            const html = await response.text();
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.querySelector('table');
            const oldTable = document.querySelector('table');
            
            if (newTable && oldTable && newTable.innerHTML !== oldTable.innerHTML) {
                oldTable.style.opacity = '0.5';
                setTimeout(() => {
                    oldTable.innerHTML = newTable.innerHTML;
                    oldTable.style.opacity = '1';
                    oldTable.closest('.overflow-x-auto').classList.add('bg-blue-50');
                    setTimeout(() => oldTable.closest('.overflow-x-auto').classList.remove('bg-blue-50'), 1000);
                }, 300);
            }
        } catch (e) {}
    }
    // Smart polling: no overlap + backoff + pause when tab hidden.
    try { poll(pollSubjects, { interval: 30000, backoffMax: 120000 }); } catch (e) {}

    // ===== Import Modal (Mapel) =====
    function openSubjectWizard() {
        document.getElementById('importModalSubject').classList.remove('hidden');
    }
    function closeSubjectImport() {
        document.getElementById('importModalSubject').classList.add('hidden');
    }

    function subjectFileSelected(input) {
        const fileName = input.files[0] ? input.files[0].name : '';
        const fileNameDiv = document.getElementById('subjectFileName');
        if (fileName) {
            fileNameDiv.textContent = 'File terpilih: ' + fileName;
            fileNameDiv.classList.remove('hidden');
        } else {
            fileNameDiv.textContent = '';
            fileNameDiv.classList.add('hidden');
        }
    }
    (function() {
        var openBtn = document.getElementById('btn-open-subject-wizard');
        if (openBtn) openBtn.addEventListener('click', openSubjectWizard);
    })();
</script>
@endpush
@endsection