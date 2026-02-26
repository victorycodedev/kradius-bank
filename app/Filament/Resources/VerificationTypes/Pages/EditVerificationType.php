<?php

namespace App\Filament\Resources\VerificationTypes\Pages;

use App\Filament\Resources\VerificationTypes\VerificationTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVerificationType extends EditRecord
{
    protected static string $resource = VerificationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
}
