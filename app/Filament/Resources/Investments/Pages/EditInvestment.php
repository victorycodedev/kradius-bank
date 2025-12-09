<?php

namespace App\Filament\Resources\Investments\Pages;

use App\Filament\Resources\Investments\InvestmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditInvestment extends EditRecord
{
    protected static string $resource = InvestmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->icon(Heroicon::Trash),
            ForceDeleteAction::make()
                ->icon(Heroicon::Trash),
            RestoreAction::make()
                ->icon(Heroicon::ArrowPathRoundedSquare),
        ];
    }
}
