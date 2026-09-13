@php
    $isBlue = ($accent ?? 'blue') === 'blue';
    $buttonClass = $isBlue
        ? 'bg-blue-600 text-white shadow-lg shadow-blue-200/50 hover:bg-blue-700'
        : 'bg-gray-900 text-white shadow-lg shadow-gray-200/70 hover:bg-gray-800';
    $softClass = $isBlue ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-700';
    $iconClass = $isBlue ? 'bg-blue-600 text-white' : 'bg-gray-900 text-white';
    $available = $available ?? true;
    $lockedMessage = $lockedMessage ?? 'Absensi belum dibuka.';
    $badges = $badges ?? (isset($badge['label']) ? [$badge] : []);
@endphp

<form method="POST" action="{{ $route }}" data-skip-global-loading="true" class="group rounded-3xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5"
      x-data="cameraAttendance({{ $locationEnabled ? 'true' : 'false' }})"
      @submit="prepareSubmit($event)"
      @attendance-tab-change.window="stopCamera()">
    @csrf
    <input type="hidden" name="photo_data" x-model="photoData">
    <input type="hidden" name="latitude" x-model="latitude">
    <input type="hidden" name="longitude" x-model="longitude">
    <input type="hidden" name="accuracy" x-model="accuracy">
    <input type="hidden" name="device_fingerprint" x-model="deviceFingerprint">

    <div class="flex items-start justify-between gap-3">
        <div class="flex min-w-0 items-start gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $iconClass }}">
                @if($isBlue)
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                @else
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"></path></svg>
                @endif
            </div>
            <div class="min-w-0">
                <h2 class="text-base font-black text-gray-900">{{ $title }}</h2>
                <p class="mt-1 text-xs leading-5 text-gray-500">{{ $subtitle }}</p>
            </div>
        </div>
        <div class="flex shrink-0 flex-wrap justify-end gap-1">
            @foreach($badges as $item)
                <span class="rounded-xl border px-2.5 py-1 text-[10px] font-black {{ $item['tone'] }}">{{ $item['label'] }}</span>
            @endforeach
        </div>
    </div>

    <div class="mt-4">
        <div x-show="submitting" x-cloak class="mb-3 flex items-center gap-2 rounded-2xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-black text-blue-700">
            <div class="h-4 w-4 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></div>
            Mengirim absensi...
        </div>
        @if($photo)
            <div class="relative mx-auto max-w-65 overflow-hidden rounded-3xl border border-gray-100 bg-gray-950 shadow-inner">
                <img src="{{ $photo }}" alt="{{ $photoAlt }}" class="aspect-3/4 w-full object-cover">
                <div class="absolute bottom-3 left-3 rounded-full bg-black/55 px-3 py-1 text-[10px] font-bold text-white backdrop-blur">Foto tersimpan</div>
            </div>
        @elseif(!$available)
            <div class="rounded-3xl border border-gray-100 bg-gray-50 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-gray-400 ring-1 ring-gray-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-black text-gray-900">Kamera belum tersedia</p>
                        <p class="mt-1 text-xs font-semibold leading-5 text-gray-500">{{ $lockedMessage }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="relative mx-auto max-w-65 overflow-hidden rounded-3xl border border-gray-100 bg-gray-950 shadow-inner">
                <video x-ref="video" x-show="!photoData" autoplay playsinline muted class="aspect-3/4 w-full scale-x-[-1] object-cover"></video>
                <img x-show="photoData" :src="photoData" alt="{{ $photoAlt }}" class="aspect-3/4 w-full object-cover">
                <button type="button" @click="cancelCamera()" x-show="cameraReady || photoData" x-cloak
                        class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-black/60 px-3 py-1.5 text-[11px] font-black text-white backdrop-blur active:scale-95">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Batal
                </button>
                <div x-show="!cameraReady && !photoData" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-950 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <p class="mt-3 text-sm font-bold text-white">Kamera belum aktif</p>
                    <p class="mt-1 max-w-48 text-xs leading-5 text-gray-400">Izinkan akses kamera saat browser meminta izin.</p>
                </div>
            </div>

            <div x-show="error" x-cloak class="mt-3 rounded-2xl border border-rose-100 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700" x-text="error"></div>

            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:justify-end">
                <button type="button" @click="startCamera()" x-show="!cameraReady && !photoData" class="min-h-11 w-full rounded-2xl {{ $buttonClass }} px-4 py-3 text-sm font-black active:scale-[0.98]">
                    Buka Kamera
                </button>
                <button type="button" @click="capture()" x-show="cameraReady && !photoData" class="min-h-11 w-full rounded-2xl {{ $buttonClass }} px-4 py-3 text-sm font-black active:scale-[0.98]">
                    {{ $captureLabel }}
                </button>
                <button type="button" @click="retake()" x-show="photoData" x-cloak
                        class="inline-flex min-h-11 w-full items-center justify-center gap-1.5 rounded-2xl sm:w-auto {{ $softClass }} px-4 py-3 text-sm font-black active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Ulangi
                </button>
                <button type="submit" x-show="photoData" x-cloak :disabled="submitting"
                        class="inline-flex min-h-11 w-full items-center justify-center gap-1.5 rounded-2xl sm:w-auto {{ $buttonClass }} px-4 py-3 text-sm font-black disabled:cursor-wait disabled:opacity-70 active:scale-[0.98]">
                    <span x-show="submitting" class="h-4 w-4 animate-spin rounded-full border-2 border-white/80 border-t-transparent"></span>
                    <svg x-show="!submitting" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 6l2.788 13.029a.5.5 0 01-.932.408l-4.722-7.422a.5.5 0 00-.94.212l-.597 2.985a.5.5 0 01-.972.007l-.808-4.367a.5.5 0 00-.965-.005L7.04 13.4a.5.5 0 01-.968.02L6.4 8.72a.5.5 0 01.495-.56L16 6z"></path>
                    </svg>
                    {{ $primaryLabel }}
                </button>
            </div>
        @endif
    </div>
</form>
