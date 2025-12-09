<?php

namespace App\Filament\Resources\Teams\Pages;

use App\Filament\Resources\Teams\TeamResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class EditTeam extends EditRecord
{
    protected static string $resource = TeamResource::class;
    protected static ?string $title = 'Update Team Member';
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Delete Member')
                ->icon(Heroicon::Trash)
                ->disabled(fn($record) => $record->id == Auth::user()->id)
                ->visible(Auth::user()->can('delete user')),
        ];
    }
}