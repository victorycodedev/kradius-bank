<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Filament\Resources\Loans\LoanResource;
use App\Filament\Resources\Loans\Widgets\LoanOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListLoans extends ListRecords
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Loan')
                ->icon(Heroicon::PlusCircle)
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LoanOverview::class,
        ];
    }
}
