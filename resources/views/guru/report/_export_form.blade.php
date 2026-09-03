@php $p = $prefix ?? ''; @endphp
<div class="space-y-1.5">
    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pilih Kelas</label>
    <select name="class_id" id="{{ $p }}export_class_id" required
            class="block w-full px-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all text-sm text-gray-800"
            placeholder="Pilih Kelas...">
        <option value="">Pilih Kelas...</option>
        @foreach($classes as $class)
            <option value="{{ $class->id }}">{{ $class->name }}</option>
        @endforeach
    </select>
</div>

<div class="space-y-1.5">
    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Periode</label>
    <select id="{{ $p }}export_period" required
            class="block w-full px-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all text-sm text-gray-800"
            placeholder="Pilih Periode...">
        <option value="">Pilih Periode...</option>
        @foreach($months as $period)
            <option value="{{ $period['value'] }}" data-month="{{ $period['month'] }}" data-year="{{ $period['year'] }}">
                {{ $period['label'] }}
            </option>
        @endforeach
    </select>
    <input type="hidden" name="month" id="{{ $p }}hidden_month">
    <input type="hidden" name="year" id="{{ $p }}hidden_year">
</div>

<button type="submit"
        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs uppercase tracking-widest shadow-sm transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
    </svg>
    Download Excel
</button>
