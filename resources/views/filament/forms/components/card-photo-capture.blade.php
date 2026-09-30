{{--
    Take a photo of the physical card with the device camera, for stock where
    PulseAPI's artwork isn't what the buyer is actually getting (a graded slab
    above all). Same getUserMedia + canvas approach as the rapid-intake
    scanner, so there's one camera pattern in the admin rather than two.

    Sits alongside the ordinary file upload rather than replacing it: on a
    desktop with no camera the upload is the only route, and on a tablet the
    camera is much faster than saving to the gallery first.
--}}
<div
    x-data="{
        stream: null,
        active: false,
        busy: false,
        error: '',

        async start() {
            this.error = '';

            try {
                // Rear camera, portrait-biased — cards and slabs are taller
                // than they are wide.
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 1080 }, height: { ideal: 1440 } },
                });
            } catch (e) {
                this.error = 'Camera unavailable — check the browser has permission, or use the upload above.';
                return;
            }

            this.$refs.video.srcObject = this.stream;
            this.active = true;
        },

        stop() {
            this.active = false;
            if (this.stream) {
                this.stream.getTracks().forEach((t) => t.stop());
                this.stream = null;
            }
        },

        async capture() {
            const video = this.$refs.video;
            if (this.busy || !this.active || !video.videoWidth) return;

            this.busy = true;

            const canvas = this.$refs.canvas;
            // Caps the long edge — a full-resolution frame is needlessly
            // large for a catalogue thumbnail and slow to post back.
            const maxWidth = 1200;
            const scale = Math.min(1, maxWidth / video.videoWidth);
            canvas.width = video.videoWidth * scale;
            canvas.height = video.videoHeight * scale;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

            try {
                await this.$wire.storeCapturedCardPhoto(canvas.toDataURL('image/jpeg', 0.85));
                this.stop();
            } catch (e) {
                this.error = 'Could not save that photo — please try again.';
            } finally {
                this.busy = false;
            }
        },
    }"
    x-on:livewire:navigating.window="stop()"
    x-destroy="stop()"
    class="space-y-3"
>
    <div x-show="!active">
        <x-filament::button type="button" color="gray" icon="heroicon-o-camera" x-on:click="start()">
            Take photo with camera
        </x-filament::button>
    </div>

    <div x-show="active" x-cloak class="space-y-3">
        <video x-ref="video" autoplay playsinline muted
            class="w-full max-w-xs rounded-lg border border-gray-300 dark:border-gray-700"></video>

        <div class="flex gap-3">
            <x-filament::button type="button" icon="heroicon-o-camera" x-on:click="capture()" x-bind:disabled="busy">
                <span x-show="!busy">Capture</span>
                <span x-show="busy" x-cloak>Saving…</span>
            </x-filament::button>

            <x-filament::button type="button" color="gray" x-on:click="stop()">
                Cancel
            </x-filament::button>
        </div>
    </div>

    <canvas x-ref="canvas" class="hidden"></canvas>

    <p x-show="error" x-text="error" x-cloak class="text-sm text-danger-600 dark:text-danger-400"></p>
</div>
