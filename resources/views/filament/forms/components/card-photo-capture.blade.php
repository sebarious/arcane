{{--
    Photograph a card with a phone while editing it on the desktop. The admin
    machine is usually a desktop with no usable camera and the card is in
    someone's hand at the counter, so the capture happens on the phone and
    syncs back — the same handoff Rapid Intake uses for scanning.

    Sits alongside the ordinary file upload rather than replacing it: an
    existing photo on disk still wants the plain uploader.
--}}
<div
    x-data="{
        starting: false,
        url: null,
        svg: null,
        received: false,
        error: '',
        pollId: null,

        async start() {
            this.starting = true;
            this.error = '';
            this.received = false;

            try {
                const session = await $wire.startCardPhotoSession();
                this.url = session.url;
                this.svg = session.svg;
                this.poll();
            } catch (e) {
                this.error = 'Could not start a photo session — please try again.';
            } finally {
                this.starting = false;
            }
        },

        poll() {
            this.stopPolling();

            // Polled rather than pushed: this is a two-device handoff over a
            // cache-backed session, and three seconds is well inside how long
            // it takes someone to line a card up and tap Capture.
            this.pollId = setInterval(async () => {
                const path = await $wire.pollCardPhoto();

                if (path) {
                    this.received = true;
                    this.url = null;
                    this.svg = null;
                    this.stopPolling();
                }
            }, 3000);
        },

        stopPolling() {
            if (this.pollId) clearInterval(this.pollId);
            this.pollId = null;
        },

        async cancel() {
            this.stopPolling();
            this.url = null;
            this.svg = null;
            await $wire.endCardPhotoSession();
        },
    }"
    x-on:livewire:navigating.window="stopPolling()"
    x-destroy="stopPolling()"
    class="space-y-3"
>
    <div x-show="!url && !received">
        <x-filament::button type="button" color="gray" icon="heroicon-o-device-phone-mobile"
            x-on:click="start()" x-bind:disabled="starting">
            <span x-show="!starting">Take photo with phone</span>
            <span x-show="starting" x-cloak>Starting…</span>
        </x-filament::button>

        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Opens a camera page on your phone — the photo appears here automatically.
        </p>
    </div>

    <div x-show="url" x-cloak class="space-y-3">
        <div class="inline-block rounded-lg bg-white p-3" x-html="svg"></div>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            Scan this with your phone, take the photo, and it'll drop in here. Waiting…
        </p>

        <p class="text-xs text-gray-400 dark:text-gray-500 break-all">
            Or open: <span class="font-mono" x-text="url"></span>
        </p>

        <x-filament::button type="button" color="gray" size="sm" x-on:click="cancel()">
            Cancel
        </x-filament::button>
    </div>

    <p x-show="received" x-cloak class="text-sm text-success-600 dark:text-success-400">
        Photo received — it's set on this card and saves with the form.
    </p>

    <p x-show="error" x-text="error" x-cloak class="text-sm text-danger-600 dark:text-danger-400"></p>
</div>
