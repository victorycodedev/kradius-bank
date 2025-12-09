<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('userAccount.id')
                    ->state(function ($record) {
                        return $record->userAccount->account_number . ' - ' . $record->userAccount->user->name;
                    })
                    ->searchable(),
                TextColumn::make('transaction_type')
                    ->badge(),
                TextColumn::make('amount')
                    ->formatStateUsing(function ($state, Model $record) {
                        return number_format($state, 2) . ' ' . $record->currency;
                    }),
                TextColumn::make('balance_before')
                    ->formatStateUsing(function ($state, Model $record) {
                        return number_format($state, 2) . ' ' . $record->currency;
                    }),
                TextColumn::make('balance_after')
                    ->formatStateUsing(function ($state, Model $record) {
                        return number_format($state, 2) . ' ' . $record->currency;
                    }),
                TextColumn::make('reference_number')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->button()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
