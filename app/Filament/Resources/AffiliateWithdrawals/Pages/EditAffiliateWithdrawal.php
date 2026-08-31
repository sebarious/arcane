<?php

namespace App\Filament\Resources\AffiliateWithdrawals\Pages;

use App\Filament\Resources\AffiliateWithdrawals\AffiliateWithdrawalResource;
use Filament\Resources\Pages\EditRecord;

class EditAffiliateWithdrawal extends EditRecord
{
    protected static string $resource = AffiliateWithdrawalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            AffiliateWithdrawalResource::markPaidAction(),
            AffiliateWithdrawalResource::rejectAction(),
        ];
    }
}
