<?php

namespace App\Filament\Resources\Stocks\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class StocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('symbol')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('category')
                    ->searchable(),
                TextColumn::make('current_price')
                    ->money()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->onIcon(Heroicon::CheckCircle)
                    ->offIcon(Heroicon::XCircle),
                ToggleColumn::make('is_featured')
                    ->onIcon(Heroicon::CheckCircle)
                    ->offIcon(Heroicon::XCircle),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('update_price')
                        ->icon(Heroicon::ArrowTrendingUp)
                        ->schema([
                            TextInput::make('new_price')
                                ->numeric()
                                ->prefix('$')
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $record->updatePrice($data['new_price'], Auth::user()->id);

                            Notification::make()
                                ->success()
                                ->title('Price Updated')
                                ->send();
                        }),
                ])
                    ->button()

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
