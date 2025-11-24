<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create User')
                ->icon(Heroicon::PlusCircle),
        ];
    }

    public function getTabs(): array
    {
        return [
            // 'all' => Tab::make(),
            'active' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('account_status', 'active')),
            'suspended' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('account_status', 'suspended')),
            'closed' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('account_status', 'closed')),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'active';
    }
}
