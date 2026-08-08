<x-filament-panels::page>
    @if ($this->scanSessionToken)
        <div wire:poll.3s="pollPhoneScans"></div>
    @endif

    <script>
        // Fired by RapidIntake::pollPhoneScans() whenever a phone scan adds a row —
        // scrolls the newest one into view so you don't have to keep scrolling the
        // desktop page down manually while scanning card after card on your phone.
        window.addEventListener('rapid-intake-row-added', () => {
            requestAnimationFrame(() => {
                const items = document.querySelectorAll('.fi-fo-repeater-item');
                items[items.length - 1]?.scrollIntoView({ behavior: 'smooth', block: 'center' });
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