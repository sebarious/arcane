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

            // A manual override — update OUR sync timestamp and re-band accordingly.
            // Leave market_value_updated_at alone; it's PulseAPI's own timestamp, and we
            // didn't just get a fresh calculation from them.
            $data['synced_at'] = now();
            $data['rarity_band'] = (new RarityBander())->bandFor($data['market_value_pence']);
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
