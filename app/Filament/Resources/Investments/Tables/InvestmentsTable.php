<?php

namespace App\Filament\Resources\Investments\Tables;

use App\Models\Investment;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class InvestmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('stock.name')
                    ->searchable(),
                // TextColumn::make('userAccount.id')
                //     ->searchable(),
                TextColumn::make('reference_number')
                    ->searchable(),
                TextColumn::make('amount')
                    ->numeric(),
                TextColumn::make('shares')
                    ->numeric(),
                TextColumn::make('purchase_price')
                    ->money(),
                TextColumn::make('current_value')
                    ->numeric(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'gray',
                        'liquidated' => 'warning',
                        'active' => 'success',
                        'pending' => 'danger',
                        default => 'secondary',
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn(Investment $record): bool => $record->status !== 'completed'),
                    Action::make('add_profit')
                        ->icon(Heroicon::CurrencyDollar)
                        ->disabled(fn(Investment $record): bool => $record->status === 'completed')
                        ->schema([
                            TextInput::make('amount')
                                ->numeric()
                                ->prefix('₦')
                                ->required(),
                            Textarea::make('description'),
                        ])
                        ->action(function (Investment $record, array $data) {
                            $profit = $record->addProfit(
                                $data['amount'],
                                'manual',
                                $data['description']
                            );

                            $record->payProfit($profit);

                            Notification::make()
                                ->success()
                                ->title('Profit Added')
                                ->send();
                        }),
                    Action::make('complete')
                        ->icon(Heroicon::CheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(Investment $record): bool => $record->status !== 'completed')
                        ->action(function (Investment $record) {
                            $record->complete();

                            Notification::make()
                                ->success()
                                ->title('Investment Completed')
                                ->body('Funds returned to user account')
                                ->send();
                        }),
                    DeleteAction::make(),
                ])
                    ->button()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
