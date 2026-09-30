<?php

namespace App\Filament\Resources\AllInventory;

use App\Filament\Resources\CardInventories\CardInventoryResource;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Every card in stock, batch-eligible or not — the unfiltered view of the
 * same table the other two inventory resources slice up.
 */
class AllInventoryResource extends CardInventoryResource
{
    protected static ?string $slug = 'all-inventory';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'All Inventory';

    protected static ?int $navigationSort = 22;

    protected static ?string $modelLabel = 'Card';

    protected static ?string $pluralModelLabel = 'All Inventory';

    /** No narrowing — this is the everything view. */
    protected static function inventoryScope(Builder $query): Builder
    {
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAllInventory::route('/'),
            // No create page here — manual entry lives on the non-batch
            // Inventory list, which is where a hand-added card belongs.
            'edit' => Pages\EditAllInventory::route('/{record}/edit'),
        ];
    }
}
