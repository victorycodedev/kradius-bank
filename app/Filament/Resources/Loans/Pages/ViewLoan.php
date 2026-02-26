<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\Loans\LoanResource;
use App\Mail\LoanApproved;
use App\Mail\LoanDisbursed;
use App\Mail\LoanRejected;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Infolists;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ViewLoan extends ViewRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon(Heroicon::PencilSquare)
                ->label('Edit Loan'),
            Actions\Action::make('approve')
                ->icon(Heroicon::CheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn() => in_array($this->record->status, ['pending', 'under_review']))
                ->schema([
                    Forms\Components\TextInput::make('approved_amount')
                        ->label('Approved Amount')
                        ->numeric()
                        ->prefix('₦')
                        ->required()
                        ->default(fn() => $this->record->amount),

                    Forms\Components\Textarea::make('review_notes')
                        ->label('Approval Notes (optional)')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $loanType = $this->record->loanType;
                    $approvedAmount = $data['approved_amount'];

                    $monthlyPayment = $loanType->calculateMonthlyPayment($approvedAmount, $this->record->duration_months);
                    $totalPayable = $monthlyPayment * $this->record->duration_months;

                    $this->record->update([
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

                    $this->record->logActivity('status_changed', 'Loan approved by ' . Auth::user()->name);
                    // Mail::to($this->record->user->email)->send(new LoanApproved($this->record, $data['review_notes'] ?? ''));

                    Notification::make()
                        ->success()
                        ->title('Loan Approved')
                        ->send();
                }),

            Actions\Action::make('reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn() => in_array($this->record->status, ['pending', 'under_review']))
                ->schema([
                    Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'rejected',
                        'rejection_reason' => $data['rejection_reason'],
                        'reviewed_by' => Auth::user()->id,
                        'reviewed_at' => now(),
                    ]);

                    $this->record->logActivity('status_changed', 'Loan rejected by ' . Auth::user()->name);
                    // Mail::to($this->record->user->email)->send(new LoanRejected($this->record, $data['rejection_reason']));

                    Notification::make()
                        ->success()
                        ->title('Loan Rejected')
                        ->send();
                }),

            Actions\Action::make('disburse')
                ->icon('heroicon-o-currency-dollar')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn() => $this->record->status === 'approved')
                ->action(function () {
                    $this->record->update([
                        'status' => 'disbursed',
                        'disbursed_at' => now(),
                        'due_date' => now()->addMonths($this->record->duration_months),
                    ]);

                    $account = $this->record->user->primaryAccount;

                    if ($account) {
                        $account->credit($this->record->approved_amount);
                    }

                    $this->record->logActivity('disbursed', 'Loan disbursed by ' . Auth::user()->name);
                    // Mail::to($this->record->user->email)->send(new LoanDisbursed($this->record));

                    Notification::make()
                        ->success()
                        ->title('Loan Disbursed')
                        ->send();
                }),

            Actions\DeleteAction::make()
                ->icon(Heroicon::Trash)
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Loan Overview')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Group::make([
                                    TextEntry::make('reference_number')
                                        ->label('Reference')
                                        ->weight('bold')
                                        ->copyable(),

                                    TextEntry::make('user.email')
                                        ->label('User'),

                                    TextEntry::make('loanType.name')
                                        ->label('Loan Type')
                                        ->badge(),

                                    TextEntry::make('status')
                                        ->badge()
                                        ->color(fn($state) => match ($state) {
                                            'pending' => 'warning',
                                            'approved', 'disbursed', 'active', 'completed' => 'success',
                                            'rejected', 'defaulted' => 'danger',
                                            default => 'info',
                                        }),
                                ]),

                                Group::make([
                                    TextEntry::make('amount')
                                        ->label('Requested Amount')
                                        ->money('NGN')
                                        ->weight('bold'),

                                    TextEntry::make('approved_amount')
                                        ->label('Approved Amount')
                                        ->money('NGN')
                                        ->weight('bold')
                                        ->placeholder('-'),

                                    TextEntry::make('interest_rate')
                                        ->suffix('% per annum'),

                                    TextEntry::make('duration_months')
                                        ->label('Duration')
                                        ->suffix(' months'),
                                ]),
                            ]),
                    ]),

                Section::make('Financial Details')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('monthly_payment')
                                    ->money('NGN')
                                    ->placeholder('-'),

                                TextEntry::make('total_payable')
                                    ->money('NGN')
                                    ->placeholder('-'),

                                TextEntry::make('outstanding_balance')
                                    ->money('NGN'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Applicant Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('employment_status')
                                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state)))
                                    ->placeholder('-'),

                                TextEntry::make('monthly_income')
                                    ->money('NGN')
                                    ->placeholder('-'),
                            ]),

                        TextEntry::make('purpose')
                            ->columnSpanFull(),

                        TextEntry::make('additional_info')
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ])
                    ->collapsible(),

                Section::make('Review Information')
                    ->schema([
                        TextEntry::make('reviewer.name')
                            ->label('Reviewed By')
                            ->placeholder('-'),

                        TextEntry::make('reviewed_at')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('review_notes')
                            ->columnSpanFull()
                            ->placeholder('-'),

                        TextEntry::make('rejection_reason')
                            ->columnSpanFull()
                            ->placeholder('-')
                            ->visible(fn() => $this->record->status === 'rejected'),
                    ])
                    ->collapsible(),

                Section::make('Activity Log')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('activities')
                            ->label('')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('activity_type')
                                            ->label('Type')
                                            ->badge(),

                                        TextEntry::make('description')
                                            ->columnSpan(2),

                                        TextEntry::make('created_at')
                                            ->dateTime()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
