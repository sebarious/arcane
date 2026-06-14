<?php

namespace App\Filament\Resources\CustomerSellSubmissions\Pages;

use App\Filament\Resources\CustomerSellSubmissions\CustomerSellSubmissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerSellSubmissions extends ListRecords
{
    protected static string $resource = CustomerSellSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
