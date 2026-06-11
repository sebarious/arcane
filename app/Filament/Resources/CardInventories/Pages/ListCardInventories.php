<?php
namespace App\Filament\Resources\CardInventories\Pages;

use App\Filament\Resources\CardInventories\CardInventoryResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Support\Icons\Heroicon;
use Filament\Resources\Pages\ListRecords;

class ListCardInventories extends ListRecords
{
    protected static string $resource = CardInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rapidIntake')
                ->label('Rapid intake')
                ->icon(Heroicon::OutlinedBolt)
                ->url(CardInventoryResource::getUrl('rapid'))
                ->color('primary'),
            CreateAction::make()->label('Add single card'),
        ];
    }
}
