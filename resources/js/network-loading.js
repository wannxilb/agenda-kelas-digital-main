let networkToast = null;
let hideTimer = null;

function ensureNetworkToast() {
    if (networkToast) return networkToast;

    networkToast = document.createElement('div');
    networkToast.className = 'network-toast';
    networkToast.setAttribute('role', 'status');
    networkToast.setAttribute('aria-live', 'polite');
    document.body.appendChild(networkToast);

    return networkToast;
}

function showToast(message, type) {
    const toast = ensureNetworkToast();

    clearTimeout(hideTimer);

    toast.textContent = message;
    toast.className = 'network-toast is-shown ' + (type === 'offline' ? 'is-offline' : 'is-online');

    hideTimer = setTimeout(() => {
        toast.classList.remove('is-shown');
    }, 3000);
}

function applyOfflineState(offline) {
    document.body.classList.toggle('is-offline', offline);
}

document.addEventListener('DOMContentLoaded', () => {
    const offline = !navigator.onLine;
    applyOfflineState(offline);
    if (offline) {
        showToast('Koneksi tidak stabil', 'offline');
    }
});

window.addEventListener('online', () => {
    applyOfflineState(false);
    showToast('Koneksi kembali', 'online');
});

window.addEventListener('offline', () => {
    applyOfflineState(true);
    showToast('Koneksi tidak stabil', 'offline');
});