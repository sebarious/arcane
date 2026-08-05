<div
    x-data="{
        stream: null,
        active: false,
        busy: false,
        intervalId: null,
        statusText: 'Idle — press Start scanning',
        statusColor: 'gray',
        flashTimeout: null,

        async start() {
            try {
                // Cards are portrait-shaped — bias the stream toward portrait so the
                // preview box (also portrait, see below) doesn't crop the card's edges.
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 720 }, height: { ideal: 1280 } },
                });
            } catch (e) {
                this.statusText = 'Camera access denied or unavailable.';
                this.statusColor = 'danger';
                return;
            }

            this.$refs.video.srcObject = this.stream;
            this.active = true;
            this.statusText = 'Scanning…';
            this.statusColor = 'gray';
            this.intervalId = setInterval(() => this.captureAndScan(), 1500);
        },

        stop() {
            this.active = false;
            if (this.intervalId) clearInterval(this.intervalId);
            if (this.stream) {
                this.stream.getTracks().forEach((t) => t.stop());
                this.stream = null;
            }
            this.statusText = 'Idle — press Start scanning';
            this.statusColor = 'gray';
        },

        async captureAndScan() {
            const video = this.$refs.video;
            if (this.busy || !this.active || !video.videoWidth) return;

            this.busy = true;

            const canvas = this.$refs.canvas;
            const maxWidth = 900;
            const scale = Math.min(1, maxWidth / video.videoWidth);
            canvas.width = video.videoWidth * scale;
            canvas.height = video.videoHeight * scale;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.7);

            try {
                const result = await this.$wire.scanFrame(dataUrl);
                this.handleResult(result);
            } catch (e) {
                this.flash('Scan failed — retrying…', 'danger');
            } finally {
                this.busy = false;
            }
        },

        handleResult(result) {
            switch (result.status) {
                case 'resolved':
                    this.flash('✓ Added: ' + (result.card_name ?? result.number), 'success');
                    break;
                case 'ambiguous':
                    this.flash('⚠ ' + result.number + ' — multiple matches, resolve below', 'warning');
                    break;
                case 'not_found':
                    this.flash('✕ ' + result.number + ' — no match, nothing added. Set aside or try again.', 'warning');
                    break;
                case 'duplicate':
                    // Same card still in frame — stay quiet rather than re-flashing.
                    break;
                case 'limit_reached':
                    this.flash('Row limit reached (50) — scan stopped.', 'warning');
                    this.stop();
                    break;
                case 'no_number':
                case 'no_text':
                    this.statusText = 'Scanning…';
                    this.statusColor = 'gray';
                    break;
                case 'error':
                    this.flash(result.message ?? 'Scan error', 'danger');
                    break;
            }
        },

        flash(text, color) {
            this.statusText = text;
            this.statusColor = color;
            clearTimeout(this.flashTimeout);
            this.flashTimeout = setTimeout(() => {
                if (this.active) { this.statusText = 'Scanning…'; this.statusColor = 'gray'; }
            }, 2500);
        },

        destroy() { this.stop(); },
    }"
    x-init="$watch('active', () => {})"
    x-on:beforeunload.window="stop()"
    class="rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-4 mb-4"
>
    <div class="flex flex-col sm:flex-row gap-4 items-start">
        <div class="relative w-full sm:w-48 aspect-[3/4] bg-black rounded-md overflow-hidden shrink-0">
            <video x-ref="video" autoplay playsinline muted class="w-full h-full object-cover" x-show="active"></video>
            <div x-show="!active" class="absolute inset-0 flex items-center justify-center text-xs text-gray-400 px-4 text-center">
                Camera preview appears here once scanning starts.
            </div>
            <canvas x-ref="canvas" class="hidden"></canvas>
        </div>

        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1">Live camera scan</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                Hold each card's number up to the camera and move to the next once it's added —
                no need to take individual photos. Requires HTTPS (or localhost) for camera access.
            </p>

            <div class="flex items-center gap-3 mb-3">
                <button
                    type="button"
                    x-show="!active"
                    x-on:click="start()"
                    class="fi-btn fi-btn-color-primary inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium bg-primary-600 text-white hover:bg-primary-500"
                >
                    Start scanning
                </button>
                <button
                    type="button"
                    x-show="active"
                    x-on:click="stop()"
                    class="fi-btn inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium border border-gray-300 dark:border-white/20 text-gray-700 dark:text-gray-200"
                >
                    Stop scanning
                </button>
            </div>

            <p
                class="text-sm font-medium"
                :class="{
                    'text-gray-500 dark:text-gray-400': statusColor === 'gray',
                    'text-green-600 dark:text-green-400': statusColor === 'success',
                    'text-amber-600 dark:text-amber-400': statusColor === 'warning',
                    'text-red-600 dark:text-red-400': statusColor === 'danger',
                }"
                x-text="statusText"
            ></p>
        </div>
    </div>
</div>
