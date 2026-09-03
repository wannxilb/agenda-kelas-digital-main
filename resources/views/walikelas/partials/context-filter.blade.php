@if(($waliContexts ?? collect())->count() > 1)
    @php
        $selected = $selectedWaliContext ?? null;
        $contextOptions = $waliContexts
            ->map(fn ($context) => [
                'value' => $context['key'],
                'label' => $context['label'],
            ])
            ->values();
        $preservedQuery = collect(request()->query())
            ->except(['wali_context', 'page'])
            ->filter(fn ($value) => filled($value));
    @endphp

    <form method="GET" action="{{ url()->current() }}" class="relative z-20 bg-white rounded-2xl border border-gray-100">
        @foreach($preservedQuery as $name => $value)
            @if(is_array($value))
                @foreach($value as $item)
                    <input type="hidden" name="{{ $name }}[]" value="{{ $item }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endif
        @endforeach

        <div class="p-4 sm:p-5">
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-black text-gray-900 uppercase tracking-widest">Riwayat Wali Kelas</p>
                        <p class="mt-1 text-xs sm:text-sm text-gray-500 font-medium">
                            Pilih kelas aktif atau riwayat kelas yang pernah Anda wali.
                        </p>
                    </div>
                </div>

                <div class="w-full lg:w-105">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Konteks Data</label>
                    <div class="relative"
                         x-data="waliContextSearchableSelect({
                            value: @js($selectedWaliContextKey ?? ''),
                            placeholder: 'Pilih konteks data',
                            options: @js($contextOptions)
                         })"
                         @keydown.escape.window="open = false">
                        <input type="hidden" name="wali_context" :value="value">

                        <button type="button"
                                @click="toggle(); $nextTick(() => open && $refs.searchInput?.focus())"
                                class="w-full flex items-center justify-between gap-2 px-3 py-2.5 bg-gray-50 border-0 rounded-xl text-left text-xs font-semibold transition-all focus:outline-none"
                                :class="open ? 'bg-white ring-2 ring-indigo-500/20' : 'hover:bg-gray-100'">
                            <span class="truncate" :class="value ? 'text-gray-900' : 'text-gray-400'" x-text="selectedLabel"></span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open" x-cloak @click.away="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             class="absolute left-0 right-0 mt-2 z-50 bg-white rounded-xl border border-gray-100 overflow-hidden">
                            <div class="p-2 border-b border-gray-50">
                                <div class="relative">
                                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"></path>
                                    </svg>
                                    <input x-ref="searchInput" x-model="search" type="text" placeholder="Cari kelas atau tahun ajaran..."
                                           class="w-full pl-9 pr-3 py-2 bg-gray-50 border-0 rounded-lg text-xs font-semibold text-gray-900 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                </div>
                            </div>
                            <div class="max-h-56 overflow-y-auto py-1">
                                <template x-for="opt in filteredOptions" :key="opt.value">
                                    <button type="button"
                                            @click="select(opt.value); $nextTick(() => $el.closest('form').submit())"
                                            class="w-full flex items-center justify-between gap-2 px-3 py-2.5 text-xs text-left transition-colors"
                                            :class="opt.value === value ? 'bg-indigo-50 text-indigo-700 font-black' : 'text-gray-700 hover:bg-gray-50 font-semibold'">
                                        <span class="truncate" x-text="opt.label"></span>
                                        <svg x-show="opt.value === value" class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </template>
                                <p x-show="filteredOptions.length === 0" class="px-3 py-3 text-xs text-gray-400 text-center font-semibold">Tidak ada hasil</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($selected)
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-100 rounded-lg text-[10px] sm:text-[11px] font-black uppercase tracking-widest">
                        {{ $selected['class']->name ?? '-' }}
                    </span>
                    @if($selected['academic_year'] ?? null)
                        <span class="inline-flex items-center px-2.5 py-1 bg-gray-50 text-gray-600 border border-gray-100 rounded-lg text-[10px] sm:text-[11px] font-bold">
                            {{ $selected['academic_year']->name }} - {{ $selected['academic_year']->semester }}
                        </span>
                    @endif
                    @if($selected['is_active_context'] ?? false)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-lg text-[10px] sm:text-[11px] font-black uppercase tracking-widest">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-100 rounded-lg text-[10px] sm:text-[11px] font-black uppercase tracking-widest">
                            Riwayat
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </form>
@endif

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('waliContextSearchableSelect', (config) => ({
                    open: false,
                    search: '',
                    value: config.value ?? '',
                    placeholder: config.placeholder ?? 'Pilih...',
                    options: config.options ?? [],
                    get selectedLabel() {
                        const selected = this.options.find((option) => option.value === this.value);
                        return selected ? selected.label : this.placeholder;
                    },
                    get filteredOptions() {
                        const query = this.search.trim().toLowerCase();
                        if (!query) return this.options;
                        return this.options.filter((option) => option.label.toLowerCase().includes(query));
                    },
                    toggle() {
                        this.open = !this.open;
                        if (this.open) this.search = '';
                    },
                    select(value) {
                        this.value = value;
                        this.open = false;
                        this.search = '';
                    },
                }));
            });
        </script>
    @endpush
@endonce
