<?php

namespace App\Filament\Resources\Stores\Pages;

use App\Filament\Resources\Stores\StoreResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStore extends CreateRecord
{
    protected static string $resource = StoreResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $allPlatforms = [
            'physical_store' => false,
            'ebay'           => false,
            'cardmarket'     => false,
            'whatnot'        => false,
            'instagram'      => false,
            'tiktok_shop'    => false,
            'website'        => false,
        ];
        foreach (($data['platforms_form'] ?? []) as $platform) {
            if (array_key_exists($platform, $allPlatforms)) {
                $allPlatforms[$platform] = true;
            }
        }
        $data['platforms'] = $allPlatforms;
        $data['social_links'] = collect($data['social_links_form'] ?? [])
            ->filter(fn ($row) => ! empty($row['platform']) && ! empty($row['url']))
            ->mapWithKeys(fn ($row) => [
                $row['platform'] => $row['url'],
            ])
            ->all();
        unset($data['platforms_form'], $data['social_links_form']);
        return $data;
    }
}
