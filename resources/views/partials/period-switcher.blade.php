@if(($academicPeriods ?? collect())->isNotEmpty())
    <div class="px-4 py-3 border-y border-gray-100 bg-gray-50/70">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Periode Data</p>
                <p class="mt-1 text-sm font-bold text-gray-900 truncate">
                    {{ $selectedAcademicPeriod?->name ?? 'Semester aktif' }}
                </p>
                <p class="text-xs font-semibold {{ ($isArchivePeriod ?? false) ? 'text-amber-600' : 'text-emerald-600' }}">
                    {{ $selectedAcademicPeriod?->semester ? 'Semester ' . $selectedAcademicPeriod->semester : '-' }}
                    @if($isArchivePeriod ?? false)
                        - Mode Arsip
                    @else
                        - Aktif
                    @endif
                </p>
            </div>
            <span class="shrink-0 rounded-lg px-2 py-1 text-[10px] font-black {{ ($isArchivePeriod ?? false) ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                {{ ($isArchivePeriod ?? false) ? 'ARSIP' : 'AKTIF' }}
            </span>
        </div>

        <form method="POST" action="{{ route('academic-period.switch') }}" class="mt-3">
            @csrf
            <select name="academic_year_id" onchange="this.form.submit()"
                    class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                @foreach($academicPeriods as $period)
                    <option value="{{ $period->id }}" {{ (string) $selectedAcademicPeriod?->id === (string) $period->id ? 'selected' : '' }}>
                        {{ $period->name }} - {{ $period->semester }}{{ $period->is_active ? ' (Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
        </form>

        @if($isArchivePeriod ?? false)
            <form method="POST" action="{{ route('academic-period.reset') }}" class="mt-2">
                @csrf
                <button type="submit" class="w-full rounded-xl bg-white px-3 py-2 text-xs font-bold text-emerald-700 border border-emerald-100 hover:bg-emerald-50 transition-colors">
                    Kembali ke Semester Aktif
                </button>
            </form>
            <p class="mt-2 text-[10px] leading-relaxed text-amber-600">
                Data lama ditampilkan sebagai arsip. Input baru tetap diarahkan ke semester aktif.
            </p>
        @endif
    </div>
@endif
