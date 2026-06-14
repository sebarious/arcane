<?php

namespace App\Filament\Resources\CustomerSellSubmissions\Pages;

use App\Filament\Resources\CustomerSellSubmissions\CustomerSellSubmissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerSellSubmission extends EditRecord
{
    protected static string $resource = CustomerSellSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
