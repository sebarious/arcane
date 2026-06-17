<?php

namespace App\Filament\Resources\SellerApplications\Pages;

use App\Filament\Resources\SellerApplications\SellerApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSellerApplication extends EditRecord
{
    protected static string $resource = SellerApplicationResource::class;
}
