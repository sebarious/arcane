<x-filament-panels::page>
    @if ($this->scanSessionToken)
        <div wire:poll.3s="pollPhoneScans"></div>
    @endif

    <script>
        // Fired by RapidIntake::pollPhoneScans() whenever a phone scan adds a row —
        // scrolls the newest one into view and focuses its quantity field, so you
        // don't have to keep scrolling the desktop page down or clicking in
        // manually while scanning card after card on your phone. Quantity (not
        // the search field) because the number is already resolved by this
        // point — quantity is the one thing left worth adjusting before the
        // next scan.
        window.addEventListener('rapid-intake-row-added', () => {
            requestAnimationFrame(() => {
                const items = document.querySelectorAll('.fi-fo-repeater-item');
                const last = items[items.length - 1];
                if (! last) return;

                last.scrollIntoView({ behavior: 'smooth', block: 'center' });

                const quantityInput = last.querySelector('input[id$=".quantity"]');
                // preventScroll — focus shouldn't fight the smooth scroll above by
                // jumping the viewport straight to the field.
                quantityInput?.focus({ preventScroll: true });
                quantityInput?.select();
            });
        });
    </script>

    <form wire:submit="save">
        {{ $this->form }}
        <div class="mt-6 flex justify-end gap-2">
            <x-filament::button type="submit" size="lg">
                Save intake
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>