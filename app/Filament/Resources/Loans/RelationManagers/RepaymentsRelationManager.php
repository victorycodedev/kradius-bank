<?php

namespace App\Filament\Resources\Loans\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RepaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'repayments';

    protected static ?string $title = 'Repayment Schedule';

    protected static ?string $recordTitleAttribute = 'reference_number';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Details')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Total Payment Amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₦')
                                    ->default(fn() => $this->getOwnerRecord()->monthly_payment)
                                    ->live()

                                    // ✅ RUNS WHEN MODAL FIRST OPENS
                                    ->afterStateHydrated(function ($state, Set $set) {
                                        $loan = $this->getOwnerRecord();

                                        $monthlyRate = ($loan->interest_rate / 100) / 12;
                                        $balance = $loan->outstanding_balance;

                                        $interestAmount = $balance * $monthlyRate;
                                        $principalAmount = $state - $interestAmount;

                                        $set('interest_amount', round($interestAmount, 2));
                                        $set('principal_amount', round($principalAmount, 2));
                                    })

                                    // ✅ RUNS WHEN USER CHANGES AMOUNT
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $loan = $this->getOwnerRecord();

                                        $monthlyRate = ($loan->interest_rate / 100) / 12;
                                        $balance = $loan->outstanding_balance;

                                        $interestAmount = $balance * $monthlyRate;
                                        $principalAmount = $state - $interestAmount;

                                        $set('interest_amount', round($interestAmount, 2));
                                        $set('principal_amount', round($principalAmount, 2));
                                    }),

                                TextInput::make('principal_amount')
                                    ->label('Principal Amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₦')
                                    ->helperText('Amount that reduces loan balance'),

                                TextInput::make('interest_amount')
                                    ->label('Interest Amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₦')
                                    ->helperText('Interest portion of payment'),
                            ]),
                    ]),

                Section::make('Schedule & Status')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('due_date')
                                    ->label('Due Date')
                                    ->required()
                                    ->native(false)
                                    ->default(fn() => now()->addMonth())
                                    ->minDate(now()),

                                Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'paid' => 'Paid',
                                        'overdue' => 'Overdue',
                                        'partial' => 'Partial',
                                    ])
                                    ->default('pending')
                                    ->required()
                                    ->native(false),
                            ]),

                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('paid_at')
                                    ->label('Payment Date')
                                    ->native(false)
                                    ->visible(fn($get) => in_array($get('status'), ['paid', 'partial'])),

                                Select::make('payment_method')
                                    ->options([
                                        'bank_transfer' => 'Bank Transfer',
                                        'card' => 'Card Payment',
                                        'deduction' => 'Auto Deduction',
                                        'cash' => 'Cash',
                                    ])
                                    ->native(false)
                                    ->visible(fn($get) => in_array($get('status'), ['paid', 'partial'])),
                            ]),
                    ]),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->placeholder('Any additional notes about this payment...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference_number')
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->icon('heroicon-m-hashtag'),

                TextColumn::make('amount')
                    ->label('Payment')
                    ->money('NGN')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('principal_amount')
                    ->label('Principal')
                    ->money('NGN')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('interest_amount')
                    ->label('Interest')
                    ->money('NGN')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d M, Y')
                    ->sortable()
                    ->color(fn($record) => $record->isOverdue() && $record->status !== 'paid' ? 'danger' : null)
                    ->icon(fn($record) => $record->isOverdue() && $record->status !== 'paid' ? 'heroicon-m-exclamation-triangle' : null),

                TextColumn::make('paid_at')
                    ->label('Paid On')
                    ->dateTime('d M, Y H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'overdue',
                        'info' => 'partial',
                    ]),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('due_date', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'partial' => 'Partial',
                    ])
                    ->multiple(),

                SelectFilter::make('payment_method')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'card' => 'Card Payment',
                        'deduction' => 'Auto Deduction',
                        'cash' => 'Cash',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Repayment')
                    ->icon(Heroicon::PlusCircle)
                    ->modalHeading('Create Repayment Schedule')
                    ->mutateDataUsing(function (array $data): array {
                        // Auto-calculate if not set
                        if (empty($data['principal_amount']) || empty($data['interest_amount'])) {
                            $loan = $this->getOwnerRecord();
                            $monthlyRate = ($loan->interest_rate / 100) / 12;
                            $balance = $loan->outstanding_balance;

                            $interestAmount = $balance * $monthlyRate;
                            $principalAmount = $data['amount'] - $interestAmount;

                            $data['interest_amount'] = round($interestAmount, 2);
                            $data['principal_amount'] = round($principalAmount, 2);
                        }
                        return $data;
                    })
                    ->successNotificationTitle('Repayment schedule created'),

                Action::make('generate_schedule')
                    ->label('Generate Full Schedule')
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Generate Repayment Schedule')
                    ->modalDescription('This will create repayment entries for all remaining months. Existing repayments will not be affected.')
                    ->action(function () {
                        $loan = $this->getOwnerRecord();
                        $existingCount = $loan->repayments()->count();
                        $remainingMonths = $loan->duration_months - $existingCount;

                        if ($remainingMonths <= 0) {
                            Notification::make()
                                ->warning()
                                ->title('Schedule Already Complete')
                                ->body('All repayment entries have already been created.')
                                ->send();
                            return;
                        }

                        $monthlyPayment = $loan->monthly_payment;
                        $balance = $loan->outstanding_balance;
                        $monthlyRate = ($loan->interest_rate / 100) / 12;

                        // Get the last due date
                        $lastRepayment = $loan->repayments()->orderBy('due_date', 'desc')->first();
                        $nextDueDate = $lastRepayment
                            ? $lastRepayment->due_date->copy()->addMonth()
                            : now()->addMonth();

                        for ($i = 0; $i < $remainingMonths; $i++) {
                            $interestAmount = $balance * $monthlyRate;
                            $principalAmount = $monthlyPayment - $interestAmount;
                            $balance -= $principalAmount;

                            $loan->repayments()->create([
                                'amount' => $monthlyPayment,
                                'principal_amount' => round($principalAmount, 2),
                                'interest_amount' => round($interestAmount, 2),
                                'due_date' => $nextDueDate->copy(),
                                'status' => 'pending',
                            ]);

                            $nextDueDate->addMonth();
                        }

                        Notification::make()
                            ->success()
                            ->title('Schedule Generated')
                            ->body("{$remainingMonths} repayment(s) created successfully.")
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'paid')
                    ->requiresConfirmation()
                    ->modalHeading('Record Payment')
                    ->schema([
                        DateTimePicker::make('paid_at')
                            ->label('Payment Date')
                            ->default(now())
                            ->required()
                            ->native(false),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer',
                                'card' => 'Card Payment',
                                'deduction' => 'Auto Deduction',
                                'cash' => 'Cash',
                            ])
                            ->required()
                            ->native(false),

                        Textarea::make('notes')
                            ->label('Payment Notes'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'paid',
                            'paid_at' => $data['paid_at'],
                            'payment_method' => $data['payment_method'],
                            'notes' => $data['notes'] ?? $record->notes,
                        ]);

                        // Update loan outstanding balance
                        $loan = $record->loan;
                        $loan->outstanding_balance -= $record->amount;

                        // Check if loan is fully paid
                        if ($loan->outstanding_balance <= 0) {
                            $loan->status = 'completed';
                            $loan->outstanding_balance = 0;
                        } elseif ($loan->status === 'disbursed') {
                            $loan->status = 'active';
                        }

                        $loan->save();

                        // Log activity
                        $loan->logActivity(
                            'payment_received',
                            'Payment of ₦' . number_format($record->amount, 2) . ' received for repayment ' . $record->reference_number,
                            Auth::user()->id
                        );

                        Notification::make()
                            ->success()
                            ->title('Payment Recorded')
                            ->body('Outstanding balance: ₦' . number_format($loan->outstanding_balance, 2))
                            ->send();
                    }),

                EditAction::make()
                    ->modalHeading('Edit Repayment'),

                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Repayment')
                    ->modalDescription('Are you sure? This will affect the loan balance.')
                    ->after(function ($record) {
                        // Recalculate loan balance if paid repayment is deleted
                        if ($record->status === 'paid') {
                            $loan = $record->loan;
                            $loan->outstanding_balance += $record->amount;
                            $loan->save();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_paid_bulk')
                        ->label('Mark as Paid')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->schema([
                            DateTimePicker::make('paid_at')
                                ->label('Payment Date')
                                ->default(now())
                                ->required()
                                ->native(false),

                            Select::make('payment_method')
                                ->label('Payment Method')
                                ->options([
                                    'bank_transfer' => 'Bank Transfer',
                                    'card' => 'Card Payment',
                                    'deduction' => 'Auto Deduction',
                                    'cash' => 'Cash',
                                ])
                                ->required()
                                ->native(false),
                        ])
                        ->action(function ($records, array $data) {
                            $totalAmount = 0;
                            $loan = $this->getOwnerRecord();

                            foreach ($records as $record) {
                                if ($record->status !== 'paid') {
                                    $record->update([
                                        'status' => 'paid',
                                        'paid_at' => $data['paid_at'],
                                        'payment_method' => $data['payment_method'],
                                    ]);
                                    $totalAmount += $record->amount;
                                }
                            }

                            // Update loan balance
                            $loan->outstanding_balance -= $totalAmount;
                            if ($loan->outstanding_balance <= 0) {
                                $loan->status = 'completed';
                                $loan->outstanding_balance = 0;
                            }
                            $loan->save();

                            Notification::make()
                                ->success()
                                ->title('Payments Recorded')
                                ->body(count($records) . ' payment(s) marked as paid. Outstanding: ₦' . number_format($loan->outstanding_balance, 2))
                                ->send();
                        }),

                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('No repayments yet')
            ->emptyStateDescription('Create a repayment schedule or generate the full schedule automatically.')
            ->emptyStateIcon('heroicon-o-calendar')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Add First Repayment')
                    // ->url(fn(): string => static::getUrl('create', ['owner' => $this->getOwnerRecord()]))
                    ->icon('heroicon-m-plus'),
            ]);
    }
}
