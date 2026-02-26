<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Models\Loan;
use App\Models\Settings;
use App\Notifications\LoanStatusNotification;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\View;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Copied')
                    ->weight('bold'),

                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable(),

                TextColumn::make('loanType.name')
                    ->label('Loan Type')
                    ->badge(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('NGN'),

                TextColumn::make('approved_amount')
                    ->label('Approved')
                    ->money('NGN'),

                TextColumn::make('duration_months')
                    ->label('Duration')
                    ->suffix(' months'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'under_review' => 'gray',
                        'pending' => 'warning',
                        'disbursed' => 'success',
                        'active' => 'success',
                        'completed' => 'success',
                        'defaulted' => 'danger',
                        'rejected' => 'danger',
                        'cancelled' => 'danger',
                        default => 'info',
                    }),
                TextColumn::make('created_at')
                    ->dateTime('d M, Y')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('updated_at')
                    ->dateTime('d M, Y')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->dateTime('d M, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'pending' => 'Pending',
                        'under_review' => 'Under Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'disbursed' => 'Disbursed',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'defaulted' => 'Defaulted',
                    ]),

                SelectFilter::make('loan_type')
                    ->relationship('loanType', 'name'),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    Action::make('approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(Loan $record) => in_array($record->status, ['pending', 'under_review']))
                        ->schema([
                            TextInput::make('approved_amount')
                                ->label('Approved Amount')
                                ->numeric()
                                ->prefix('₦')
                                ->required()
                                ->default(fn(Loan $record) => $record->amount),

                            Textarea::make('review_notes')
                                ->label('Approval Notes (optional)')
                                ->rows(3),
                        ])
                        ->action(function (Loan $record, array $data) {
                            $loanType = $record->loanType;
                            $approvedAmount = $data['approved_amount'];

                            // Calculate monthly payment and total payable
                            $monthlyPayment = $loanType->calculateMonthlyPayment($approvedAmount, $record->duration_months);
                            $totalPayable = $monthlyPayment * $record->duration_months;

                            $record->update([
                                'status' => 'approved',
                                'approved_amount' => $approvedAmount,
                                'monthly_payment' => $monthlyPayment,
                                'total_payable' => $totalPayable,
                                'outstanding_balance' => $totalPayable,
                                'review_notes' => $data['review_notes'] ?? null,
                                'reviewed_by' => Auth::user()->id,
                                'reviewed_at' => now(),
                                'approved_at' => now(),
                            ]);

                            // Log activity
                            $record->logActivity('status_changed', 'Loan approved by ' . Auth::user()->name);

                            $settings = Settings::get();

                            try {
                                if ($settings->notify_on_loan_status) {
                                    $record->user->notify(new LoanStatusNotification($record, 'approved'));
                                }
                            } catch (\Throwable $th) {
                                //throw $th;
                            }

                            Notification::make()
                                ->success()
                                ->title('Loan Approved')
                                ->body('The loan has been approved and the user has been notified.')
                                ->send();
                        }),

                    Action::make('reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn(Loan $record) => in_array($record->status, ['pending', 'under_review']))
                        ->schema([
                            Textarea::make('rejection_reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->rows(4)
                                ->helperText('This will be sent to the user'),
                        ])
                        ->action(function (Loan $record, array $data) {
                            $record->update([
                                'status' => 'rejected',
                                'rejection_reason' => $data['rejection_reason'],
                                'reviewed_by' => Auth::user()->id,
                                'reviewed_at' => now(),
                            ]);

                            // Log activity
                            $record->logActivity('status_changed', 'Loan rejected by ' . Auth::user()->name);

                            $settings = Settings::get();

                            try {
                                if ($settings->notify_on_loan_status) {
                                    $record->user->notify(new LoanStatusNotification($record, 'rejected'));
                                }
                            } catch (\Throwable $th) {
                                //throw $th;
                            }

                            Notification::make()
                                ->success()
                                ->title('Loan Rejected')
                                ->body('The loan has been rejected and the user has been notified.')
                                ->send();
                        }),

                    Action::make('disburse')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn(Loan $record) => $record->status === 'approved')
                        ->modalHeading('Disburse Loan')
                        ->modalDescription('Are you sure you want to disburse this loan? The funds will be credited to the user\'s account.')
                        ->action(function (Loan $record) {
                            $record->update([
                                'status' => 'disbursed',
                                'disbursed_at' => now(),
                                'due_date' => now()->addMonths($record->duration_months),
                            ]);

                            // Credit user's primary account
                            // $account = $record->user->primaryAccount();
                            $account = $record->user->primaryAccount;

                            if ($account) {
                                $account->credit($record->approved_amount);

                                // Create transaction record
                                $account->transactions()->create([
                                    'transaction_type' => 'credit',
                                    'amount' => $record->approved_amount,
                                    'currency' => $account->currency,
                                    'balance_before' => $account->balance - $record->approved_amount,
                                    'balance_after' => $account->balance,
                                    'description' => 'Loan disbursement - ' . $record->reference_number,
                                    'status' => 'completed',
                                    'channel' => 'bank_transfer',
                                ]);
                            }

                            // Log activity
                            $record->logActivity('disbursed', 'Loan disbursed by ' . Auth::user()->name);

                            $settings = Settings::get();

                            try {
                                if ($settings->notify_on_loan_status) {
                                    $record->user->notify(new LoanStatusNotification($record, 'disbursed'));
                                }
                            } catch (\Throwable $th) {
                                //throw $th;
                            }

                            Notification::make()
                                ->success()
                                ->title('Loan Disbursed')
                                ->body('The loan has been disbursed successfully.')
                                ->send();
                        }),
                ])
                    ->button()

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
