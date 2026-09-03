@if($isArchivePeriod ?? false)
    <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-12 8h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-black text-amber-800">
                    Mode Arsip: {{ $selectedAcademicPeriod?->name }} - Semester {{ $selectedAcademicPeriod?->semester }}
                </p>
                <p class="mt-0.5 text-xs leading-relaxed text-amber-700">
                    Anda sedang melihat data periode lama. Input baru tetap disimpan ke semester aktif.
                </p>
            </div>
            <form method="POST" action="{{ route('academic-period.reset') }}" class="shrink-0">
                @csrf
                <button type="submit" class="hidden sm:inline-flex rounded-xl bg-white px-3 py-2 text-xs font-bold text-amber-700 border border-amber-200 hover:bg-amber-100 transition-colors">
                    Kembali Aktif
                </button>
            </form>
        </div>
    </div>
@endif
