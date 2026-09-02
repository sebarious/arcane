<?php

namespace App\Filament\Resources\CardInventories\Pages;

use App\Filament\Resources\CardInventories\CardInventoryResource;
use App\Services\Banding\RarityBander;
use App\Services\Pricing\PulseApiPriceProvider;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditCardInventory extends EditRecord
{
    protected static string $resource = CardInventoryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['cost_pounds'])) {
            $data['cost_pence'] = Money::toPence($data['cost_pounds']);
            unset($data['cost_pounds']);
        }

        if (isset($data['market_value_pounds'])) {
            $data['market_value_pence'] = Money::toPence($data['market_value_pounds']);
            unset($data['market_value_pounds']);

            // A manual override — update OUR sync timestamp. Leave
            // market_value_updated_at alone; it's PulseAPI's own timestamp,
            // and we didn't just get a fresh calculation from them.
            $data['synced_at'] = now();

            // Only re-band while the card is still unclaimed — this field is
            // present (and its value re-submitted) on every save of this
            // form regardless of which field an admin actually meant to
            // change, so without this guard, simply opening and re-saving
            // an already-dispatched/sold card (to fix an unrelated field
            // like acquisition_lot) would silently reband it against
            // whatever the market value had already drifted to. Once
            // locked, the rarity_band <select> above is the only way to
            // deliberately change it. See CardInventory::isBandLocked().
            if (! $this->record->isBandLocked()) {
                $data['rarity_band'] = (new RarityBander)->bandFor($data['market_value_pence']);
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resyncPrice')
                ->label('Resync price')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->visible(fn () => filled($this->record->product_id))
                ->disabled(fn () => $this->record->price_locked)
                ->tooltip(fn () => $this->record->price_locked ? 'Price is locked — unlock it to resync' : null)
                ->action(function () {
                    app(PulseApiPriceProvider::class)->refreshPrice($this->record);
                    $this->fillForm();

                    Notification::make()
                        ->title('Price resynced')
                        ->body('New market value: '.Money::format($this->record->market_value_pence))
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
