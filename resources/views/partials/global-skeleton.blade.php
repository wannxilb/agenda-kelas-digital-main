{{-- Full-page loading skeleton. Included in every role layout. --}}
<div id="global-skeleton" class="skeleton-screen">
    <div class="flex h-full flex-col">
        <div class="flex shrink-0 items-center gap-3 border-b border-gray-100 px-5 py-4 sm:px-8">
            <div class="h-9 w-9 shrink-0 rounded-xl skeleton"></div>
            <div class="space-y-2">
                <div class="h-3 w-36 rounded skeleton"></div>
                <div class="h-2.5 w-24 rounded skeleton"></div>
            </div>
            <div class="ml-auto h-9 w-9 shrink-0 rounded-xl skeleton"></div>
        </div>
        <div class="flex flex-1 overflow-hidden">
            <aside class="hidden w-64 shrink-0 border-r border-gray-100 p-5 lg:block">
                <div class="space-y-3">
                    <div class="h-4 w-40 rounded skeleton"></div>
                    <div class="h-4 w-32 rounded skeleton"></div>
                    <div class="h-4 w-36 rounded skeleton"></div>
                    <div class="h-4 w-28 rounded skeleton"></div>
                    <div class="h-4 w-40 rounded skeleton"></div>
                    <div class="h-4 w-32 rounded skeleton"></div>
                </div>
            </aside>
            <main class="flex-1 overflow-hidden px-5 py-6 sm:px-8">
                <div class="space-y-6">
                    <div class="h-6 w-56 rounded-lg skeleton"></div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="h-28 rounded-2xl skeleton"></div>
                        <div class="h-28 rounded-2xl skeleton"></div>
                        <div class="hidden h-28 rounded-2xl skeleton lg:block"></div>
                    </div>
                    <div class="h-44 rounded-2xl skeleton"></div>
                    <div class="h-24 rounded-2xl skeleton"></div>
                    <div class="h-20 rounded-2xl skeleton"></div>
                </div>
            </main>
        </div>
    </div>
</div>
<script>
(function () {
    var overlay = document.getElementById('global-skeleton');
    if (!overlay) return;

    document.documentElement.appendChild(overlay);

    var startedAt = Date.now();

    function hide() {
        var remaining = 300 - (Date.now() - startedAt);
        window.setTimeout(function () {
            overlay.classList.add('skeleton-screen--done');
            window.setTimeout(function () {
                if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
            }, 300);
        }, Math.max(0, remaining));
    }

    if (document.readyState !== 'loading') {
        hide();
    } else {
        document.addEventListener('DOMContentLoaded', hide);
    }
})();
</script>
