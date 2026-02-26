<?php

namespace App\Filament\Resources\Investments\Pages;

use App\Filament\Resources\Investments\InvestmentResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class ViewInvestment extends ViewRecord
{
    protected static string $resource = InvestmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make()
            //     ->icon(Heroicon::Pencil),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('User & Account Info')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('user.name')
                                ->weight(FontWeight::ExtraBold)
                                ->label('User'),

                            TextEntry::make('stock.name')
                                ->weight(FontWeight::ExtraBold)
                                ->label('Stock'),
                        ]),

                        TextEntry::make('userAccount.account_number')
                            ->weight(FontWeight::ExtraBold)
                            ->label('User Account Number'),
                    ]),
                Section::make('Investment Details')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('reference_number')
                                    ->weight(FontWeight::ExtraBold),
                                TextEntry::make('investment_type')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'long_term' => 'primary',
                                        'short_term' => 'warning',
                                        'day_trade' => 'success',
                                        default => 'info',
                                    }),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'pending' => 'primary',
                                        'active'  => 'success',
                                        'completed' => 'gray',
                                        'cancelled' => 'danger',
                                        'liquidated' => 'secondary',
                                        default => 'secondary',
                                    }),
                            ]),

                        Grid::make(3)->schema([
                            TextEntry::make('amount')
                                ->weight(FontWeight::ExtraBold)
                                ->label('Amount Invested')
                                ->money(),

                            TextEntry::make('purchase_price')
                                ->weight(FontWeight::ExtraBold)
                                ->label('Purchase Price (per share)')
                                ->money(),

                            TextEntry::make('shares')
                                ->weight(FontWeight::ExtraBold)
                                ->label('Shares Purchased')
                                ->numeric(),
                        ]),

                        Grid::make(3)->schema([
                            TextEntry::make('current_value')
                                ->label('Current Total Value')
                                ->weight(FontWeight::ExtraBold)
                                ->money(),

                            TextEntry::make('profit_loss')
                                ->weight(FontWeight::ExtraBold)
                                ->label('Profit / Loss')
                                ->money(),

                            TextEntry::make('profit_loss_percentage')
                                ->weight(FontWeight::ExtraBold)
                                ->suffix('%'),
                        ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('total_profit_paid')
                                    ->weight(FontWeight::ExtraBold)
                                    ->money(),

                                TextEntry::make('duration_days')
                                    ->weight(FontWeight::ExtraBold)
                                    ->label('Duration (Days)'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('roi_percentage')
                                    ->weight(FontWeight::ExtraBold)
                                    ->suffix('%'),

                                TextEntry::make('maturity_date')
                                    ->weight(FontWeight::ExtraBold)
                                    ->date(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('activated_at')
                                    ->weight(FontWeight::ExtraBold)
                                    ->label('Activated At')
                                    ->dateTime(),

                                TextEntry::make('completed_at')
                                    ->label('Completed At')
                                    ->weight(FontWeight::ExtraBold)
                                    ->dateTime(),
                            ]),

                        TextEntry::make('admin_notes')
                            ->weight(FontWeight::ExtraBold)
                            ->columnSpanFull()
                            ->markdown(),
                    ]),


            ]);
    }
}
