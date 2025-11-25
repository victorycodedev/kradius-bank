<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // User Overview Card
                Section::make('User Overview')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Group::make([
                                    TextEntry::make('first_name')
                                        ->label('First Name')
                                        ->weight(FontWeight::Bold),

                                    TextEntry::make('last_name')
                                        ->label('Last Name')
                                        ->weight(FontWeight::Bold),

                                    TextEntry::make('email')
                                        ->icon('heroicon-m-envelope')
                                        ->copyable()
                                        ->copyMessage('Email copied!')
                                        ->copyMessageDuration(1500),

                                    TextEntry::make('phone_number')
                                        ->icon('heroicon-m-phone')
                                        ->placeholder('-'),

                                    TextEntry::make('date_of_birth')
                                        ->date('d M, Y')
                                        ->icon('heroicon-m-cake')
                                        ->placeholder('-'),
                                ]),

                                Group::make([
                                    TextEntry::make('account_status')
                                        ->badge()
                                        ->color(fn(string $state): string => match ($state) {
                                            'active' => 'success',
                                            'suspended' => 'warning',
                                            'closed' => 'danger',
                                        }),

                                    TextEntry::make('kyc_status')
                                        ->label('KYC Status')
                                        ->badge()
                                        ->color(fn(string $state): string => match ($state) {
                                            'verified' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger',
                                        }),

                                    IconEntry::make('two_factor_enabled')
                                        ->label('2FA')
                                        ->boolean(),

                                    TextEntry::make('created_at')
                                        ->label('Member Since')
                                        ->date('d M, Y')
                                        ->icon('heroicon-m-calendar'),
                                ]),
                            ]),
                    ])
                    ->collapsible(),

                // Address Information
                Section::make('Address Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('address')
                                    ->columnSpanFull()
                                    ->placeholder('-'),

                                TextEntry::make('city')
                                    ->placeholder('-'),

                                TextEntry::make('state')
                                    ->placeholder('-'),

                                TextEntry::make('zip_code')
                                    ->label('Postal Code')
                                    ->placeholder('-'),

                                TextEntry::make('country')
                                    ->placeholder('-')
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->collapsible(),

                // KYC Information
                Section::make('KYC Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('kyc_status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'verified' => 'success',
                                        'pending' => 'warning',
                                        'rejected' => 'danger',
                                    }),

                                TextEntry::make('kyc_document_type')
                                    ->label('Document Type')
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'passport' => 'International Passport',
                                        'driver_license' => 'Driver License',
                                        'national_id' => 'National ID',
                                        'voter_card' => 'Voter\'s Card',
                                        default => $state,
                                    })
                                    ->placeholder('-'),

                                TextEntry::make('kyc_document_number')
                                    ->label('Document Number')
                                    ->copyable()
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->collapsible(),

                // Bank Accounts
                Section::make('Bank Accounts')
                    ->description('User bank accounts and balances')
                    ->headerActions([
                        // Action::make('manage_accounts')
                        //     ->label('Manage Accounts')
                        //     ->icon('heroicon-o-banknotes')
                        //     ->color('info')
                        //     ->url(fn($record) => route('filament.admin.resources.users.edit', [
                        //         'record' => $record->id,
                        //         'tab' => 'accounts'
                        //     ])),
                    ])
                    ->schema([
                        RepeatableEntry::make('accounts')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('account_number')
                                            ->label('Account Number')
                                            ->weight(FontWeight::Bold)
                                            ->copyable(),

                                        TextEntry::make('account_type')
                                            ->badge()
                                            ->formatStateUsing(fn($state) => ucfirst($state)),

                                        TextEntry::make('balance')
                                            ->money(fn($record) => $record->currency)
                                            ->weight(FontWeight::Bold),

                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn($state) => $state === 'active' ? 'success' : 'danger'),
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible(),

                // Cards
                Section::make('Cards')
                    ->description('User cards and payment methods')
                    ->schema([
                        RepeatableEntry::make('cards')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('card_holder_name')
                                            ->label('Card Holder')
                                            ->weight(FontWeight::Bold),

                                        TextEntry::make('card_type')
                                            ->badge()
                                            ->formatStateUsing(fn($state) => ucfirst($state)),

                                        TextEntry::make('card_brand')
                                            ->badge()
                                            ->color('info')
                                            ->formatStateUsing(fn($state) => strtoupper($state)),

                                        TextEntry::make('card_status')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn($state) => match ($state) {
                                                'active' => 'success',
                                                'blocked' => 'danger',
                                                'expired' => 'warning',
                                                'lost' => 'danger',
                                            }),
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible(),

                // Verification Codes
                Section::make('Verification Codes')
                    ->description('Transaction verification codes (COT, TAX, IMF, etc.)')
                    ->schema([
                        RepeatableEntry::make('verificationCodes')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('verificationType.name')
                                            ->label('Type')
                                            ->weight(FontWeight::Bold),

                                        TextEntry::make('code')
                                            ->copyable()
                                            ->copyMessage('Code copied!')
                                            ->weight(FontWeight::Bold)
                                            ->color('primary'),

                                        TextEntry::make('expires_at')
                                            ->label('Expires')
                                            ->date('d M, Y')
                                            ->placeholder('No expiry'),

                                        IconEntry::make('is_used')
                                            ->label('Used')
                                            ->boolean(),
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible(),

                // Security Questions
                Section::make('Security Questions')
                    ->schema([
                        RepeatableEntry::make('securityQuestions')
                            ->label('')
                            ->schema([
                                TextEntry::make('question')
                                    ->columnSpanFull()
                                    ->icon('heroicon-m-question-mark-circle'),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible(),

                // Beneficiaries
                Section::make('Beneficiaries')
                    ->description('Saved beneficiaries for transfers')
                    ->schema([
                        RepeatableEntry::make('beneficiaries')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('account_name')
                                            ->weight(FontWeight::Bold)
                                            ->icon('heroicon-m-user'),

                                        TextEntry::make('account_number')
                                            ->copyable(),

                                        TextEntry::make('bank_name'),

                                        IconEntry::make('is_favorite')
                                            ->label('Favorite')
                                            ->boolean(),
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible(),

                // Bill Payments History
                Section::make('Bill Payments')
                    ->description('Recent bill payment history')
                    ->schema([
                        RepeatableEntry::make('billPayments')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('biller_name')
                                            ->label('Biller'),

                                        TextEntry::make('biller_category')
                                            ->label('Category')
                                            ->badge(),

                                        TextEntry::make('amount')
                                            ->money('NGN')
                                            ->weight(FontWeight::Bold),

                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn($state) => match ($state) {
                                                'completed' => 'success',
                                                'pending' => 'warning',
                                                'failed' => 'danger',
                                            }),
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible(),

                // Disputes
                // Section::make('Disputes')
                //     ->description('Transaction disputes raised by user')
                //     ->schema([
                //         RepeatableEntry::make('disputes')
                //             ->label('')
                //             ->schema([
                //                 Grid::make(3)
                //                     ->schema([
                //                         TextEntry::make('reason')
                //                             ->weight(FontWeight::Bold),

                //                         TextEntry::make('status')
                //                             ->badge()
                //                             ->color(fn($state) => match ($state) {
                //                                 'resolved' => 'success',
                //                                 'pending' => 'warning',
                //                                 'investigating' => 'info',
                //                                 'rejected' => 'danger',
                //                             }),

                //                         TextEntry::make('created_at')
                //                             ->label('Raised On')
                //                             ->date('d M, Y'),

                //                         TextEntry::make('description')
                //                             ->columnSpanFull()
                //                             ->color('gray'),
                //                     ]),
                //             ])
                //             ->contained(false),
                //     ])
                //     ->collapsible(),

                // Login History
                Section::make('Recent Login Activity')
                    ->description('Last 10 login attempts')
                    ->schema([
                        RepeatableEntry::make('loginHistories')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('ip_address')
                                            ->label('IP Address')
                                            ->icon('heroicon-m-globe-alt'),

                                        TextEntry::make('device_type')
                                            ->badge()
                                            ->formatStateUsing(fn($state) => ucfirst($state)),

                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn($state) => $state === 'success' ? 'success' : 'danger'),

                                        TextEntry::make('created_at')
                                            ->label('Time')
                                            ->dateTime('d M, Y H:i'),
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible(),

                // Security Info
                Section::make('Security Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('last_login_at')
                                    ->dateTime('d M, Y H:i')
                                    ->icon('heroicon-m-clock')
                                    ->placeholder('Never'),

                                TextEntry::make('login_attempts')
                                    ->label('Failed Login Attempts')
                                    ->badge()
                                    ->color(fn($state) => $state > 0 ? 'danger' : 'success'),

                                TextEntry::make('locked_until')
                                    ->dateTime('d M, Y H:i')
                                    ->placeholder('Not locked')
                                    ->color('danger'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
