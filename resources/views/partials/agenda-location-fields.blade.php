@php
    $agendaLocationEnabled = \App\Models\Setting::get(
        'school_location_enabled',
        \App\Models\Setting::get('agenda_location_enabled', '0')
    ) === '1';
@endphp

@if($agendaLocationEnabled)
    <input type="hidden" name="agenda_latitude" data-agenda-location-latitude>
    <input type="hidden" name="agenda_longitude" data-agenda-location-longitude>
    <input type="hidden" name="agenda_accuracy" data-agenda-location-accuracy>
    <div data-agenda-location-status class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-700">
        Lokasi akan diminta saat agenda disimpan.
    </div>
@endif

@once
@if($agendaLocationEnabled)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(function(form) {
        var latInput = form.querySelector('[data-agenda-location-latitude]');
        var lngInput = form.querySelector('[data-agenda-location-longitude]');
        var accuracyInput = form.querySelector('[data-agenda-location-accuracy]');
        var statusEl = form.querySelector('[data-agenda-location-status]');

        if (!latInput || !lngInput || form.dataset.locationReady === '1') return;

        form.addEventListener('submit', function(event) {
            if (form.dataset.locationReady === '1') return;

            event.preventDefault();
            event.stopImmediatePropagation();

            if (!navigator.geolocation) {
                if (statusEl) {
                    statusEl.textContent = 'Perangkat/browser tidak mendukung deteksi lokasi.';
                    statusEl.className = 'mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700';
                }
                return;
            }

            var submitButtons = form.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(function(button) {
                button.disabled = true;
            });
            if (statusEl) {
                statusEl.textContent = 'Mengambil lokasi perangkat...';
                statusEl.className = 'mb-4 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-xs font-semibold text-indigo-700';
            }

            navigator.geolocation.getCurrentPosition(function(position) {
                latInput.value = position.coords.latitude;
                lngInput.value = position.coords.longitude;
                if (accuracyInput) accuracyInput.value = position.coords.accuracy || '';
                form.dataset.locationReady = '1';
                form.requestSubmit();
            }, function() {
                submitButtons.forEach(function(button) {
                    button.disabled = false;
                });
                if (statusEl) {
                    statusEl.textContent = 'Lokasi tidak dapat diakses. Izinkan akses lokasi lalu simpan kembali.';
                    statusEl.className = 'mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700';
                }
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        }, true);
    });
});
</script>
@endpush
@endif
@endonce
