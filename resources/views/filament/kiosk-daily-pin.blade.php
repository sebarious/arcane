{{--
    Today's kiosk unlock code, sat next to the global search so it's to hand
    when someone on the shop floor asks for it. Derived from the date, so it
    rotates on its own at midnight — see KioskDailyPin.
--}}
@php($pin = app(\App\Services\Kiosk\KioskDailyPin::class)->for())

<div
    x-data="{
        shown: false,
        copied: false,
        copy() {
            navigator.clipboard?.writeText(@js($pin));
            this.copied = true;
            setTimeout(() => (this.copied = false), 1500);
        },
    }"
    class="hidden items-center gap-2 md:flex"
>
    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kiosk PIN</span>

    {{-- Hidden until asked for: it sits on a screen that's often visible from
         the counter, and it's the one thing that unlocks discounts. --}}
    <button
        type="button"
        x-on:click="shown ? copy() : (shown = true)"
        class="rounded-md bg-gray-100 px-2 py-1 font-mono text-sm tracking-widest text-gray-900 transition hover:bg-gray-200 dark:bg-white/10 dark:text-white dark:hover:bg-white/20"
        x-bind:title="shown ? 'Click to copy' : 'Click to reveal today\'s kiosk PIN'"
    >
        <span x-show="!shown">••••••</span>
        <span x-show="shown" x-cloak x-text="copied ? 'Copied' : @js($pin)"></span>
    </button>
</div>
