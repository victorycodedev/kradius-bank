<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Filament\Resources\Loans\LoanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class EditLoan extends EditRecord
{
    protected static string $resource = LoanResource::class;

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

    protected function afterSave(): void
    {
        $this->record->logActivity(
            'updated',
            'Loan details updated by ' . Auth::user()->name,
            Auth::user()->id(),
            ['changes' => $this->record->getChanges()]
        );
    }
}
