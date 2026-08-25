<?php

namespace App\Filament\Resources\Stores\Pages;

use App\Filament\Resources\Stores\StoreResource;
use App\Models\Store;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListStores extends ListRecords
{
    protected static string $resource = StoreResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'pending_onboarding' => Tab::make('Pending onboarding')
                ->query(fn (Builder $query) => $query->whereNotNull('onboarding_submitted_at')->where('public_page_enabled', false))
                ->badge(Store::query()->whereNotNull('onboarding_submitted_at')->where('public_page_enabled', false)->count()),
            'live' => Tab::make('Live')
                ->query(fn (Builder $query) => $query->live()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewPublicList')
                ->label('View stores page')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url('/stores')
                ->openUrlInNewTab(),
            CreateAction::make(),
        ];
    }
}
