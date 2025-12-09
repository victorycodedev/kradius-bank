<?php

namespace App\Filament\Resources\Loans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Loan Information')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('user_id')
                                    ->label('User')
                                    ->relationship(name: 'user', titleAttribute: 'email', modifyQueryUsing: fn($query) => $query->role('User'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(fn(string $operation) => $operation === 'edit'),

                                Select::make('loan_type_id')
                                    ->label('Loan Type')
                                    ->relationship(name: 'loanType', titleAttribute: 'name')
                                    ->required()
                                    ->live()
                                    ->disabled(fn(string $operation) => $operation  === 'edit'),

                                Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'under_review' => 'Under Review',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        'disbursed' => 'Disbursed',
                                        'active' => 'Active',
                                        'completed' => 'Completed',
                                        'defaulted' => 'Defaulted',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->required()
                                    ->default('pending'),
                            ]),
                    ]),

                Section::make('Amount & Terms')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Requested Amount')
                                    ->numeric()
                                    ->prefix('₦')
                                    ->required()
                                    ->disabled(fn(string $operation) => $operation  === 'edit'),

                                TextInput::make('approved_amount')
                                    ->label('Approved Amount')
                                    ->numeric()
                                    ->prefix('₦')
                                    ->helperText('Can differ from requested amount'),

                                TextInput::make('interest_rate')
                                    ->label('Interest Rate (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->required()
                                    ->disabled(fn(string $operation) => $operation  === 'edit'),

                                TextInput::make('duration_months')
                                    ->label('Duration (Months)')
                                    ->numeric()
                                    ->required()
                                    ->disabled(fn(string $operation) => $operation  === 'edit'),

                                TextInput::make('monthly_payment')
                                    ->label('Monthly Payment')
                                    ->numeric()
                                    ->prefix('₦')
                                    ->disabled(),

                                TextInput::make('total_payable')
                                    ->label('Total Payable')
                                    ->numeric()
                                    ->prefix('₦')
                                    ->disabled(),
                            ]),
                    ]),

                Section::make('Applicant Information')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('employment_status')
                                    ->options([
                                        'employed' => 'Employed',
                                        'self_employed' => 'Self-Employed',
                                        'unemployed' => 'Unemployed',
                                        'retired' => 'Retired',
                                    ])
                                    ->disabled(fn(string $operation) => $operation  === 'edit'),

                                TextInput::make('monthly_income')
                                    ->label('Monthly Income')
                                    ->numeric()
                                    ->prefix('₦')
                                    ->disabled(fn(string $operation) => $operation  === 'edit'),
                            ]),

                        Textarea::make('purpose')
                            ->label('Loan Purpose')
                            // ->rows(2)
                            ->disabled(fn(string $operation) => $operation  === 'edit')
                            ->columnSpanFull(),

                        Textarea::make('additional_info')
                            ->label('Additional Information')
                            // ->rows(2)
                            ->disabled(fn(string $operation) => $operation  === 'edit')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Review Information')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('review_notes')
                            ->label('Review Notes')
                            // ->rows(3)
                            ->helperText('Internal notes about the loan review')
                            ->columnSpanFull(),

                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            // ->rows(3)
                            ->helperText('Will be sent to the user if loan is rejected')
                            ->visible(fn($get) => $get('status') === 'rejected')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn(string $operation) => $operation  === 'edit')
                    ->collapsible(),
            ]);
    }
}
