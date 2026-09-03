{{-- resources/views/admin/academic_years/_semester_panel.blade.php --}}
{{-- @param $semester (AcademicYear model or null) --}}
{{-- @param $label (string: 'Ganjil' or 'Genap') --}}
{{-- @param $colorBg, $colorText, $colorBorder --}}

<div class="p-5 {{ $semester && $semester->is_active ? 'bg-emerald-50/30' : '' }}">
    <div class="flex items-center justify-between mb-3">
        <span class="px-2.5 py-1 text-xs font-semibold {{ $colorBg }} {{ $colorText }} border {{ $colorBorder }} rounded-lg">
            {{ $label }}
        </span>
        @if($semester)
            @if($semester->is_active)
                <span class="px-2.5 py-1 text-xs font-bold bg-green-50 text-green-700 border border-green-100 rounded-lg shadow-sm">Aktif</span>
            @else
                <span class="px-2.5 py-1 text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200 rounded-lg">Tidak Aktif</span>
            @endif
        @else
            <span class="text-xs text-gray-400 italic">Belum dibuat</span>
        @endif
    </div>

    @if($semester)
        <div class="space-y-2 text-sm text-gray-600 mb-4">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>{{ $semester->start_date ? $semester->start_date->format('d M Y') : '-' }} — {{ $semester->end_date ? $semester->end_date->format('d M Y') : '-' }}</span>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if(!$semester->is_active)
            <form action="{{ route('admin.academic-years.set-active', $semester->id) }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition-colors" title="Set Aktif">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Aktifkan
                </button>
            </form>
            @endif
            <button onclick="openEditModal({{ $semester->id }}, '{{ $semester->name }}', '{{ $semester->semester }}', '{{ $semester->start_date }}', '{{ $semester->end_date }}')" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors" title="Edit">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit
            </button>
            @if(!$semester->is_active)
            <form action="{{ route('admin.academic-years.destroy', $semester->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus semester {{ $label }} {{ $semester->name }}?');">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-colors" title="Hapus">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Hapus
                </button>
            </form>
            @endif
        </div>
    @else
        <p class="text-sm text-gray-400 italic mt-2">Semester {{ $label }} belum dibuat untuk tahun ini.</p>
    @endif
</div>
