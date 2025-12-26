<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Models\Settings;
use App\Notifications\DepositStatusNotification;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
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
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'pending_verification' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'secondary',
                    })
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
                    Action::make('mark_as_completed')
                        ->label('Mark as Completed')
                        ->visible(fn(Model $record) => $record->status !== 'completed' && $record->transaction_type === 'deposit')
                        ->requiresConfirmation()
                        ->icon(Heroicon::CheckCircle)
                        ->color(Color::Blue)
                        ->modalHeading('Mark as Completed')
                        ->modalDescription(fn(Model $record) => 'Are you sure you want to mark this transaction as completed? User will be credited with the amount of ' . $record->amount . ' ' . $record->currency . ' to their account.')
                        ->action(function (Model $record) {

                            try {
                                $record->update(['status' => 'completed']);

                                // add to account balance
                                $record->userAccount->update([
                                    'balance' => $record->userAccount->balance + $record->amount
                                ]);

                                $settings = Settings::get();

                                if ($settings->notify_on_transaction) {
                                    // Notify user
                                    $record->user->notify(new DepositStatusNotification($record, 'completed'));
                                }

                                Notification::make()
                                    ->success()
                                    ->title('Transaction marked as completed')
                                    ->send();
                            } catch (\Throwable $th) {
                                Notification::make()
                                    ->danger()
                                    ->title('Failed to send email')
                                    ->send();
                            }
                        }),

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
