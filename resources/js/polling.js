// Client-side polling utility: throttles requests and prevents overlapping
// calls to avoid overloading the server with rapid/frequent requests.

(function (global) {
    'use strict';

    /**
     * Smart polling wrapper.
     * - Skips a tick if a previous request is still in-flight (no overlap).
     * - Backs off (increases interval) when the server is busy or errors,
     *   then recovers once a request succeeds.
     *
     * @param {Function} fn      async function that performs one fetch/update.
     * @param {Object}   options
     *   @param {number}  options.interval   base interval ms (default 15000)
     *   @param {number}  options.backoffMax max interval ms after failures (default 60000)
     *   @param {number}  options.immediate  whether to run immediately (default true)
     * @returns {Object} { stop() } handle to stop polling.
     */
    function poll(fn, options) {
        const opts = Object.assign({
            interval: 15000,
            backoffMax: 60000,
            immediate: true,
        }, options || {});

        let running = false;
        let stopped = false;
        let timer = null;
        let consecutiveErrors = 0;

        async function tick() {
            if (running || stopped) return; // skip if previous still in-flight

            running = true;
            try {
                const ok = await fn();
                if (ok === false) {
                    consecutiveErrors = 0; // a "no change" result is still a success
                } else {
                    consecutiveErrors = 0;
                }
            } catch (e) {
                consecutiveErrors += 1;
            } finally {
                running = false;
            }
        }

        function schedule() {
            if (stopped) return;
            const backoffFactor = Math.min(consecutiveErrors, 4);
            const delay = Math.min(opts.interval * Math.pow(2, backoffFactor), opts.backoffMax);
            timer = setTimeout(loop, delay);
        }

        function loop() {
            if (stopped) return;
            tick().then(schedule);
        }

        // Pause when the tab is hidden to save requests; resume when visible.
        function onVisibility() {
            if (stopped) return;
            if (document.hidden) {
                if (timer) clearTimeout(timer);
                timer = null;
            } else {
                tick().then(schedule);
            }
        }

        document.addEventListener('visibilitychange', onVisibility);

        if (opts.immediate) {
            tick().then(schedule);
        } else {
            schedule();
        }

        return {
            stop() {
                stopped = true;
                if (timer) clearTimeout(timer);
                document.removeEventListener('visibilitychange', onVisibility);
            },
        };
    }

    global.poll = global.poll || poll;
})(window);
