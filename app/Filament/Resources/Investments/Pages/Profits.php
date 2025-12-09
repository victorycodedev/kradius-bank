<?php

namespace App\Filament\Resources\Investments\Pages;

use App\Filament\Resources\Investments\InvestmentResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class Profits extends Page
{
    use InteractsWithRecord;

    protected static string $resource = InvestmentResource::class;

    protected string $view = 'filament.resources.investments.pages.profits';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
