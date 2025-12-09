<?php

namespace App\Filament\Resources\InvestmentProfits\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvestmentProfitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('investment.reference_number')
                    ->state(function ($record) {
                        return $record->investment->reference_number . ' - ' . $record->investment->user->name;
                    })
                    ->searchable(),
                TextColumn::make('amount')
                    ->money(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    }),
                IconColumn::make('is_auto_generated')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Date Created')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
