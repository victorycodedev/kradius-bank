<?php

namespace App\Filament\Resources\ExternalAccounts\Pages;

use App\Filament\Resources\ExternalAccounts\ExternalAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageExternalAccounts extends ManageRecords
{
    protected static string $resource = ExternalAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Account')
                ->icon(Heroicon::PlusCircle),
        ];
    }
}
