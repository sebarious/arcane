<?php

namespace App\Filament\Resources\CustomerSellSubmissions\Pages;

use App\Filament\Resources\CustomerSellSubmissions\CustomerSellSubmissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerSellSubmission extends CreateRecord
{
    protected static string $resource = CustomerSellSubmissionResource::class;
}
