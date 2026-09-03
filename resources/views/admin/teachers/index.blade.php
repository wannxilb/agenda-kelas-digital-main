{{-- resources/views/admin/teachers/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Manajemen Guru')
@section('header', 'Manajemen Guru')

@section('content')
<div class="space-y-8 pb-8">
    <!-- Header Section -->
    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6 mb-6">
        <!-- Left Section: Title & Description -->
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                Data Guru
            </h1>
            <p class="mt-2 text-base text-gray-500 max-w-2xl leading-relaxed">
                Kelola data guru, mata pelajaran yang diampu, dan pantau statistik mengajar secara efisien.
            </p>
        </div>

        <!-- Right Section: Action Buttons -->
        <div class="flex flex-wrap items-center gap-3">
            <!-- Group: Data Operations -->
            <div class="flex items-center bg-white border border-gray-200 rounded-2xl p-1.5 shadow-sm">
                <button onclick="openImportModal()" 
                        class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Import
                </button>
                <div class="w-px h-6 bg-gray-200 mx-1"></div>
                <a href="{{ route('admin.teachers.export.template') }}" 
                   class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                    </svg>
                    Template
                </a>
            </div>

            <!-- Action: Tambah Guru -->
            <a href="{{ route('admin.teachers.create') }}" 
               class="inline-flex items-center px-6 py-3 text-sm font-semibold text-white bg-linear-to-r from-blue-600 to-indigo-600 rounded-2xl shadow-lg shadow-blue-500/20 hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Guru
            </a>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-2">
        <form method="GET" action="{{ route('admin.teachers.index') }}" class="flex flex-col lg:flex-row gap-4">
            <!-- Search Input -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama, NIP, atau email guru..." 
                       class="block w-full pl-12 pr-4 py-3.5 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm">
            </div>

            <!-- Filter Kelas -->
            <div class="relative w-full lg:w-64">
                <select name="class_id" onchange="this.form.submit()" 
                        class="appearance-none block w-full pl-4 pr-10 py-3.5 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm font-semibold text-gray-700">
                    <option value="">Semua Kelas (Mengajar di...)</option>
                    @foreach($allClasses as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>

            <!-- Per Page Selector & Action Buttons -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                <button type="submit" class="inline-flex items-center px-6 py-3.5 text-sm font-semibold text-white bg-gray-800 rounded-xl hover:bg-gray-900 transition-all duration-200">
                    Cari Data
                </button>
                @if(request('search') || request('class_id') || request('per_page'))
                    <a href="{{ route('admin.teachers.index') }}" 
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
    
    <!-- Tabel Guru -->
    <div class="table-container">
        <div class="overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: calc(100vh - 350px); min-height: 400px;">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-header-glass">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">NIP</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Guru</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Statistik</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($teachers as $teacher)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-mono text-gray-600 bg-gray-100 px-2 py-1 rounded-md">{{ $teacher->nip ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-linear-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white text-sm font-bold shadow-sm">
                                    {{ strtoupper(substr($teacher->name, 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-bold text-gray-900">{{ $teacher->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $teacher->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($teacher->subjects->take(2) as $subject)
                                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100 rounded-lg">
                                        {{ $subject->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400 italic">Belum ada mapel</span>
                                @endforelse
                                @if($teacher->subjects->count() > 2)
                                    <span class="px-2 py-1 text-[10px] font-bold bg-gray-100 text-gray-600 rounded-lg">
                                        +{{ $teacher->subjects->count() - 2 }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                {{ $teacher->phone ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900">{{ $teacher->agendas_count }} Agenda</span>
                                <span class="text-[10px] text-gray-500">Bulan ini: {{ $teacher->monthly_agendas_count }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex space-x-3 items-center">
                                <a href="{{ route('admin.teachers.show', $teacher) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Lihat Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.teachers.edit', $teacher) }}" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus guru ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Hapus">
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
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Belum Ada Data Guru</h3>
                                <p class="text-gray-500 mt-1">Silakan tambah secara manual atau gunakan fitur import.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($teachers->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-sm text-gray-500 font-medium">
                Menampilkan {{ $teachers->firstItem() }} - {{ $teachers->lastItem() }} dari {{ $teachers->total() }} guru
            </div>
            <div>
                {{ $teachers->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal Import Excel (Consistent with Students) -->
<div id="importModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeImportModal()"></div>
        
        <!-- Modal Panel -->
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all">
            <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900" id="modal-title">
                            Import Data Guru
                        </h3>
                        <button type="button" onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-lg p-1.5 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="mt-4">
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 transition-all hover:bg-blue-50 cursor-pointer group"
                             onclick="document.getElementById('file').click()">
                            <input type="file" name="file" id="file" class="hidden" accept=".xlsx,.xls,.csv" required>
                            <div class="w-16 h-16 mx-auto bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                            </div>
                            <p class="text-gray-700 font-medium">Klik untuk pilih file atau drag and drop</p>
                            <p class="text-sm text-gray-500 mt-2">Format: .xlsx, .xls, .csv (Max 2MB)</p>
                            <div id="fileName" class="mt-4 text-sm font-semibold text-blue-600 bg-blue-50 py-2 px-4 rounded-lg hidden"></div>
                        </div>
                        
                        <div class="mt-6 p-4 bg-amber-50 border border-amber-100 rounded-xl">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-amber-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm text-amber-800 leading-relaxed">
                                    <strong>Petunjuk:</strong> Pastikan file Excel memiliki kolom: NIP, NAMA, EMAIL, TELEPON, MATA PELAJARAN (pisah dengan koma jika lebih dari satu).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-100">
                    <button type="button" onclick="closeImportModal()" 
                            class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-sm transition-colors">
                        Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        // Reset file input
        document.getElementById('file').value = '';
        document.getElementById('fileName').classList.add('hidden');
    }
    
    // Tampilkan nama file yang dipilih
    document.getElementById('file')?.addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        const fileNameSpan = document.getElementById('fileName');
        if (fileName) {
            fileNameSpan.textContent = `✓ ${fileName}`;
            fileNameSpan.classList.remove('hidden');
        } else {
            fileNameSpan.classList.add('hidden');
        }
    });
    
    // Tutup modal dengan ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImportModal();
        }
    });
    
    // Drag and drop untuk modal import
    const dropZone = document.querySelector('#importModal .border-2');
    if (dropZone) {
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-blue-500', 'bg-blue-50');
        });
        
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-500', 'bg-blue-50');
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-500', 'bg-blue-50');
            const file = e.dataTransfer.files[0];
            const fileInput = document.getElementById('file');
            fileInput.files = e.dataTransfer.files;
            
            // Trigger change event
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
        });
    }

    // Real-time Polling for Teachers
    async function pollTeachers() {
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
    poll(pollTeachers, { interval: 30000, backoffMax: 120000 });
</script>
@endpush
@endsection