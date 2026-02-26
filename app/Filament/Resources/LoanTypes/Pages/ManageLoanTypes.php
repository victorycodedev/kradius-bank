<?php

namespace App\Filament\Resources\LoanTypes\Pages;

use App\Filament\Resources\LoanTypes\LoanTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageLoanTypes extends ManageRecords
{
    protected static string $resource = LoanTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add New')
                ->icon(Heroicon::PlusCircle)
        ];
    }
}
