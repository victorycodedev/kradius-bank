<?php

namespace App\Filament\Resources\VerificationTypes\Pages;

use App\Filament\Resources\VerificationTypes\VerificationTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListVerificationTypes extends ListRecords
{
    protected static string $resource = VerificationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::PlusCircle)
                ->label('New Type'),
        ];
    }
}
