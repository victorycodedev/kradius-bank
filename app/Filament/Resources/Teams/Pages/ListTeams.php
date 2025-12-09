<?php

namespace App\Filament\Resources\Teams\Pages;

use App\Filament\Resources\Teams\TeamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ListTeams extends ListRecords
{
    protected static string $resource = TeamResource::class;
    protected static ?string $title = 'Teams Accounts';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Member')
                ->icon(Heroicon::PlusCircle)
                ->visible(Auth::user()->can('add new team member')),
        ];
    }
}
