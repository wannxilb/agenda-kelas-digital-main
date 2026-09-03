{{-- resources/views/teacher-status/_report-content.blade.php --}}
{{-- Partial konten rekap izin/tugas luar — dipakai bersama wakasek & admin.
     Variabel yang harus di-pass: $rows, $month, $year, $reportUrl, $csvUrl, $pdfUrl. --}}
<div class="space-y-4 sm:space-y-6 pb-24 sm:pb-6">
    {{-- Hero Header --}}
    <div class="bg-linear-to-br from-blue-600 to-indigo-700 rounded-2xl p-5 sm:p-7 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex items-center gap-3">
            <div class="w-11 h-11 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Rekap Bulanan Izin / Sakit / Tugas Luar</h2>
                <p class="text-[11px] text-blue-100">Hanya pengajuan yang disetujui</p>
            </div>
        </div>
    </div>

    {{-- Filter bulan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ $reportUrl }}" class="flex items-center gap-2.5">
            <select name="month" class="w-full sm:w-48 px-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm text-gray-700">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <select name="year" class="w-full sm:w-32 px-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm text-gray-700">
                @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 active:scale-95 transition-all">Tampilkan</button>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between gap-3">
            <h3 class="text-sm font-bold text-gray-900">
                {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}
            </h3>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-gray-500 hidden sm:inline">{{ $rows->count() }} guru</span>
                <a href="{{ $csvUrl }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-[11px] font-bold hover:bg-gray-50 active:scale-95 transition-all">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    CSV
                </a>
                <a href="{{ $pdfUrl }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-[11px] font-bold hover:bg-gray-50 active:scale-95 transition-all">
                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    PDF
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="text-left px-5 sm:px-6 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Guru</th>
                        <th class="text-center px-4 py-3.5 text-[10px] font-black text-amber-500 uppercase tracking-widest">Izin (hari)</th>
                        <th class="text-center px-4 py-3.5 text-[10px] font-black text-orange-500 uppercase tracking-widest">Sakit (hari)</th>
                        <th class="text-center px-4 py-3.5 text-[10px] font-black text-blue-500 uppercase tracking-widest">Tugas Luar (hari)</th>
                        <th class="text-center px-5 sm:px-6 py-3.5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rows as $row)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 sm:px-6 py-4">
                            <span class="font-semibold text-gray-900 text-sm">{{ $row['teacher']?->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center justify-center min-w-7 px-2 py-1 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold">{{ $row['izin_days'] }}</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center justify-center min-w-7 px-2 py-1 bg-orange-50 text-orange-700 rounded-lg text-xs font-bold">{{ $row['sakit_days'] }}</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center justify-center min-w-7 px-2 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold">{{ $row['tugas_days'] }}</span>
                        </td>
                        <td class="px-5 sm:px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center min-w-7 px-2 py-1 bg-gray-100 text-gray-800 rounded-lg text-xs font-bold">{{ $row['total_days'] }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-14 text-center">
                            <p class="text-xs font-bold text-gray-400">Tidak ada data untuk periode ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
