<?php

namespace App\Filament\Resources\NonBatchInventory;

use App\Filament\Resources\CardInventories\CardInventoryResource;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Stock that can't go into batches — either held back on condition
 * (not_for_batches) or graded, since a slab is never sealed into a pack.
 * These are the cards that sell through the kiosk, card wall and eBay.
 *
 * Extends the main inventory resource rather than restating it: the form,
 * table, filters and row actions are identical, only the slice of rows and
 * the labelling differ. Pages are re-declared because each resource needs
 * its own routes.
 */
class NonBatchInventoryResource extends CardInventoryResource
{
    protected static ?string $slug = 'inventory';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Inventory';

    protected static ?int $navigationSort = 21;

    protected static ?string $modelLabel = 'Card';

    protected static ?string $pluralModelLabel = 'Inventory';

    protected static function inventoryScope(Builder $query): Builder
    {
        return $query->notBatchable();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNonBatchInventory::route('/'),
            'edit' => Pages\EditNonBatchInventory::route('/{record}/edit'),
        ];
    }
}
