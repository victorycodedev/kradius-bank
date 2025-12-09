<?php

namespace App\Filament\Resources\InvestmentProfits\Pages;

use App\Filament\Resources\InvestmentProfits\InvestmentProfitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInvestmentProfit extends EditRecord
{
    protected static string $resource = InvestmentProfitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
