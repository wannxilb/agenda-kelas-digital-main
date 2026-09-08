@php
    $queryWithoutClass = request()->except(['class_id', 'academic_year_id', 'academic_year_name', 'grade_level', 'page']);
    $classList = $availableClasses ?? collect();
    $hasMultipleClasses = $classList->count() > 1;
    $currentClassId = (int) ($selectedClassId ?? 0);
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-visible">
    <div class="flex items-center gap-3 sm:gap-4 p-4 sm:p-5">
        <div class="w-11 h-11 sm:w-12 sm:h-12 shrink-0 bg-linear-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-200/50">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kelas</p>
            <p class="text-base sm:text-lg font-black text-gray-900 truncate">
                {{ optional($selectedClass ?? null)->name ?? 'Kelas tidak ditemukan' }}
            </p>
            <p class="mt-0.5 text-[11px] sm:text-xs text-gray-400 font-medium">Data sekretaris ditampilkan berdasarkan kelas</p>
        </div>

        @if($hasMultipleClasses)
            <div class="relative shrink-0" x-data="{ classMenuOpen: false }">
                <button type="button" @click="classMenuOpen = !classMenuOpen"
                        class="flex items-center gap-1.5 px-3.5 py-2 bg-blue-50 text-blue-700 rounded-xl text-[11px] font-bold border border-blue-100 hover:bg-blue-100 active:scale-95 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    <span class="hidden sm:inline">Ganti Kelas</span>
                    <span class="sm:hidden">Ganti</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="classMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="classMenuOpen" x-cloak @click.away="classMenuOpen = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                     class="absolute right-0 top-full mt-2 w-56 z-[100] bg-white rounded-xl border border-gray-100 shadow-xl overflow-hidden">
                    <form method="GET" action="{{ url()->current() }}">
                        @foreach($queryWithoutClass as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $item)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <div class="py-1">
                            @foreach($classList as $classOption)
                                <button type="submit" name="class_id" value="{{ $classOption->id }}"
                                        class="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 text-left text-sm transition-colors {{ $currentClassId === (int) $classOption->id ? 'bg-blue-50 text-blue-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                                    <span class="truncate">{{ $classOption->name }}</span>
                                    @if($currentClassId === (int) $classOption->id)
                                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
