<?php

namespace App\Filament\Resources\Affiliates\Pages;

use App\Filament\Resources\Affiliates\AffiliateResource;
use App\Models\Affiliate;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListAffiliates extends ListRecords
{
    protected static string $resource = AffiliateResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'pending' => Tab::make('Pending approval')
                ->query(fn ($query) => $query->where('status', 'pending'))
                ->badge(Affiliate::query()->where('status', 'pending')->count()),
            'active' => Tab::make('Active')
                ->query(fn ($query) => $query->where('status', 'active')),
            'suspended' => Tab::make('Suspended')
                ->query(fn ($query) => $query->where('status', 'suspended')),
        ];
    }
}
