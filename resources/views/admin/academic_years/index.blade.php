@extends('layouts.admin')

@section('title', 'Manajemen Tahun Ajaran')
@section('header', 'Manajemen Tahun Ajaran')

@section('content')
<div class="space-y-8 pb-8">
    
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Tahun Ajaran</h1>
            <p class="mt-2 text-base text-gray-500 max-w-2xl">
                Kelola periode tahun akademik sekolah untuk sistem informasi.
            </p>
        </div>
        <button onclick="document.getElementById('modal-create').classList.remove('hidden')" 
                class="inline-flex items-center px-6 py-3 text-sm font-semibold text-white bg-indigo-600 rounded-xl shadow-sm hover:bg-indigo-700 transition-all duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Tahun Ajaran
        </button>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-2xl flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-2xl flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <p class="font-bold">Terjadi kesalahan:</p>
                <ul class="list-disc list-inside mt-1 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @php
        $activeGroup = $groupedYears->first(function ($g) {
            return ($g['ganjil'] && $g['ganjil']->is_active) || ($g['genap'] && $g['genap']->is_active);
        });
        $inactiveGroups = $groupedYears->filter(function ($g) {
            return !($g['ganjil'] && $g['ganjil']->is_active) && !($g['genap'] && $g['genap']->is_active);
        });
    @endphp

    {{-- ====== ACTIVE YEAR CARD (Prominent) ====== --}}
    @if($activeGroup)
        @php
            $ganjil = $activeGroup['ganjil'];
            $genap  = $activeGroup['genap'];
        @endphp
        <div class="bg-white rounded-2xl shadow-md border-2 border-emerald-200 ring-1 ring-emerald-100 overflow-hidden">
            {{-- Year Header --}}
            <div class="px-6 py-5 bg-linear-to-r from-emerald-50 to-teal-50/40 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $activeGroup['name'] }}</h3>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Tahun Ajaran Aktif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Semester Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                {{-- Semester Ganjil --}}
                @include('admin.academic_years._semester_panel', ['semester' => $ganjil, 'label' => 'Ganjil', 'colorBg' => 'bg-amber-50', 'colorText' => 'text-amber-700', 'colorBorder' => 'border-amber-100'])

                {{-- Semester Genap --}}
                @include('admin.academic_years._semester_panel', ['semester' => $genap, 'label' => 'Genap', 'colorBg' => 'bg-blue-50', 'colorText' => 'text-blue-700', 'colorBorder' => 'border-blue-100'])
            </div>
        </div>
    @else
        <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-2xl flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
            <p class="font-medium">Belum ada Tahun Ajaran yang diaktifkan. Silakan buat dan aktifkan satu.</p>
        </div>
    @endif

    {{-- ====== RIWAYAT (Collapsible) ====== --}}
    @if($inactiveGroups->count() > 0)
    <div x-data="{ open: false }" class="mt-2">
        <button @click="open = !open" class="w-full flex items-center justify-between px-5 py-3.5 bg-gray-50 hover:bg-gray-100 rounded-2xl border border-gray-200 transition-all duration-200 group">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-sm font-semibold text-gray-600">Riwayat Tahun Ajaran</span>
                <span class="px-2 py-0.5 text-xs font-bold bg-gray-200 text-gray-500 rounded-full">{{ $inactiveGroups->count() }}</span>
            </div>
            <svg :class="open ? 'rotate-180' : ''" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>

        <div x-show="open" x-collapse class="mt-3 space-y-3">
            @foreach($inactiveGroups as $group)
                @php
                    $ganjil = $group['ganjil'];
                    $genap  = $group['genap'];
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-200 hover:shadow-md">
                    {{-- Year Header --}}
                    <div class="px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $group['name'] }}</h3>
                        </div>
                    </div>

                    {{-- Semester Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                        @include('admin.academic_years._semester_panel', ['semester' => $ganjil, 'label' => 'Ganjil', 'colorBg' => 'bg-amber-50', 'colorText' => 'text-amber-700', 'colorBorder' => 'border-amber-100'])
                        @include('admin.academic_years._semester_panel', ['semester' => $genap, 'label' => 'Genap', 'colorBg' => 'bg-blue-50', 'colorText' => 'text-blue-700', 'colorBorder' => 'border-blue-100'])
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($groupedYears->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <p class="text-gray-400 font-medium">Belum ada data Tahun Ajaran.</p>
            <p class="text-gray-400 text-sm mt-1">Klik tombol "Tambah Tahun Ajaran" untuk memulai.</p>
        </div>
    @endif
</div>

<!-- Modal Create -->
<div id="modal-create" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Tahun Ajaran
            </h3>
            <button onclick="document.getElementById('modal-create').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('admin.academic-years.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Periode</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Tahun Ajaran <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: 2023/2024"
                               class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Semester <span class="text-red-500">*</span></label>
                        <select name="semester" id="create-semester" required class="w-full text-sm">
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>
                </div>
            </div>
            <div>
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Tanggal</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date"
                               class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="end_date"
                               class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm">
                    </div>
                </div>
            </div>
            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')" class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-xl transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Tahun Ajaran
            </h3>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="edit-form" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Periode</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Tahun Ajaran <span class="text-red-500">*</span></label>
                        <input type="text" id="edit-name" name="name" required
                               class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Semester <span class="text-red-500">*</span></label>
                        <select name="semester" id="edit-semester" required class="w-full text-sm">
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>
                </div>
            </div>
            <div>
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Tanggal</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" id="edit-start" name="start_date"
                               class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Selesai</label>
                        <input type="date" id="edit-end" name="end_date"
                               class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm">
                    </div>
                </div>
            </div>
            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-xl transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new TomSelect('#create-semester', {
        create: false,
        placeholder: 'Pilih Semester',
        maxOptions: null,
    });
    new TomSelect('#edit-semester', {
        create: false,
        placeholder: 'Pilih Semester',
        maxOptions: null,
    });
});

function openEditModal(id, name, semester, start, end) {
    document.getElementById('edit-form').action = `/admin/academic-years/${id}`;
    document.getElementById('edit-name').value = name;
    if (document.getElementById('edit-semester').tomselect) {
        document.getElementById('edit-semester').tomselect.setValue(semester);
    } else {
        document.getElementById('edit-semester').value = semester;
    }
    document.getElementById('edit-start').value = start ? start.split(' ')[0] : '';
    document.getElementById('edit-end').value = end ? end.split(' ')[0] : '';
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endpush
@endsection