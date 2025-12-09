<?php

namespace App\Filament\Resources\InvestmentProfits\Pages;

use App\Filament\Resources\InvestmentProfits\InvestmentProfitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListInvestmentProfits extends ListRecords
{
    protected static string $resource = InvestmentProfitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Profit')
                ->icon(Heroicon::PlusCircle)
        ];
    }
}
