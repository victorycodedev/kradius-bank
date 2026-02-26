<?php

namespace App\Filament\Resources\AdNotifications\Pages;

use App\Filament\Resources\AdNotifications\AdNotificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageAdNotifications extends ManageRecords
{
    protected static string $resource = AdNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add New')
                ->icon(Heroicon::PlusCircle)
        ];
    }
}
