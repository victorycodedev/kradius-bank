<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('User Management')
                    ->tabs([
                        // Tab 1: Basic Information
                        Tab::make('Basic Info')
                            ->icon(Heroicon::UserCircle)
                            ->schema([
                                Section::make('Personal Details')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('first_name')
                                            ->required()
                                            ->maxLength(255)
                                            ->label('First Name')
                                            ->placeholder('John'),

                                        TextInput::make('last_name')
                                            ->required()
                                            ->maxLength(255)
                                            ->label('Last Name')
                                            ->placeholder('Doe'),

                                        TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->placeholder('john@example.com'),

                                        TextInput::make('phone_number')
                                            ->tel()
                                            ->maxLength(255)
                                            ->placeholder('+234 800 000 0000')
                                            ->unique(ignoreRecord: true),

                                        DatePicker::make('date_of_birth')
                                            ->label('Date of Birth')
                                            ->maxDate(now()->subYears(18))
                                            ->displayFormat('d/m/Y')
                                            ->native(false),

                                        SpatieMediaLibraryFileUpload::make('avatars')
                                            ->label('Profile Photo')
                                            ->image()
                                            ->imageEditor()
                                            ->circleCropper()
                                            ->collection('avatars')
                                            ->disk('public')
                                            ->maxSize(2048)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Address Information')
                                    ->columns(3)
                                    ->schema([
                                        Textarea::make('address')
                                            ->rows(2)
                                            ->maxLength(500)
                                            ->placeholder('Street address')
                                            ->columnSpanFull(),

                                        TextInput::make('city')
                                            ->maxLength(255)
                                            ->placeholder('City'),

                                        TextInput::make('state')
                                            ->maxLength(255)
                                            ->placeholder('State/Province'),

                                        TextInput::make('zip_code')
                                            ->maxLength(255)
                                            ->placeholder('Postal Code'),

                                        TextInput::make('country')
                                            ->maxLength(255)
                                            ->placeholder('Country')
                                            ->default('Nigeria')
                                            ->columnSpan(2),
                                    ]),
                            ]),

                        // Tab 2: Authentication & Security
                        Tab::make('Security')
                            ->icon(Heroicon::LockClosed)
                            ->schema([
                                Section::make('Authentication')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('password')
                                            ->password()
                                            ->required(fn(string $context): bool => $context === 'create')
                                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                                            ->dehydrated(fn($state) => filled($state))
                                            ->rule(Password::default())
                                            ->placeholder('••••••••')
                                            ->helperText('Leave blank to keep current password'),

                                        TextInput::make('pin')
                                            ->password()
                                            ->revealable()
                                            ->maxLength(5)
                                            ->placeholder('4-digit PIN')
                                            ->formatStateUsing(fn(?string $state): ?string => filled($state) ? decrypt($state) : null)
                                            ->dehydrateStateUsing(function (?string $state) {
                                                return filled($state) ? encrypt($state) : null;
                                            })
                                            ->helperText('4-digit PIN for transactions'),

                                        // Toggle::make('two_factor_enabled')
                                        //     ->label('Two-Factor Authentication')
                                        //     ->inline(false)
                                        //     ->afterStateHydrated(function (Toggle $component, $record) {
                                        //         if ($record) {
                                        //             $component->state($record->two_factor_secret == null);
                                        //         }
                                        //     })
                                        //     ->dehydrated(false) // Don't save to 'two_factor_enabled' column
                                        //     ->disabled(fn($record) => $record && $record->two_factor_secret === null) // Disable if 2FA not set up
                                        //     ->afterStateUpdated(function ($state, $record) {
                                        //         if (!$state && $record) {
                                        //             // Admin turning OFF 2FA - clear the secrets
                                        //             $record->update([
                                        //                 'two_factor_secret' => null,
                                        //                 'two_factor_recovery_codes' => null,
                                        //                 'two_factor_confirmed_at' => null, // If you have this column
                                        //             ]);

                                        //             Notification::make()
                                        //                 ->title('Two-Factor Authentication Disabled')
                                        //                 ->success()
                                        //                 ->send();
                                        //         }
                                        //     })
                                        //     ->helperText(
                                        //         fn($record) =>
                                        //         $record && $record->two_factor_secret == null
                                        //             ? 'Admin can disable user\'s 2FA'
                                        //             : 'User has not enabled 2FA yet'
                                        //     ),

                                        Select::make('account_status')
                                            ->options([
                                                'active' => 'Active',
                                                'suspended' => 'Suspended',
                                                'closed' => 'Closed',
                                            ])
                                            ->default('active')
                                            ->required(),
                                    ]),

                                Section::make('Security Questions')
                                    ->schema([
                                        Repeater::make('security_questions')
                                            ->relationship('securityQuestions')
                                            ->schema([
                                                Select::make('question')
                                                    ->options([
                                                        'What is your mother\'s maiden name?' => 'What is your mother\'s maiden name?',
                                                        'What was the name of your first pet?' => 'What was the name of your first pet?',
                                                        'What city were you born in?' => 'What city were you born in?',
                                                        'What is your favorite color?' => 'What is your favorite color?',
                                                        'What was your childhood nickname?' => 'What was your childhood nickname?',
                                                    ])
                                                    ->required(),

                                                TextInput::make('answer')
                                                    ->required()
                                                    ->password()
                                                    ->revealable()
                                                    ->maxLength(255)
                                                    ->belowContent('Answers will be encrypted'),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->addActionLabel('Add Security Question')
                                            ->collapsible()
                                            ->itemLabel(fn(array $state): ?string => $state['question'] ?? 'Security Question'),
                                    ])
                                    ->visibleOn('edit'),

                                Section::make('Account Security Status')
                                    ->columns(3)
                                    ->schema([
                                        DatePicker::make('last_login_at')
                                            ->label('Last Login')
                                            ->disabled()
                                            ->displayFormat('d/m/Y H:i')
                                            ->native(false),

                                        TextInput::make('login_attempts')
                                            ->label('Failed Login Attempts')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled(),

                                        DatePicker::make('locked_until')
                                            ->label('Account Locked Until')
                                            ->native(false)
                                            ->displayFormat('d/m/Y H:i'),
                                    ])
                                    ->visibleOn('edit'),
                            ]),

                        // Tab 3: KYC Information
                        Tab::make('KYC')
                            ->icon(Heroicon::Identification)
                            ->schema([
                                Section::make('KYC Verification')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('kyc_status')
                                            ->label('KYC Status')
                                            ->options([
                                                'pending' => 'Pending',
                                                'verified' => 'Verified',
                                                'rejected' => 'Rejected',
                                            ])
                                            ->default('pending')
                                            ->required(),

                                        Select::make('kyc_document_type')
                                            ->label('Document Type')
                                            ->options([
                                                'passport' => 'International Passport',
                                                'driver_license' => 'Driver License',
                                                'national_id' => 'National ID Card',
                                                'voter_card' => 'Voter\'s Card',
                                            ])
                                            ->placeholder('Select document type'),

                                        TextInput::make('kyc_document_number')
                                            ->label('Document Number')
                                            ->maxLength(255)
                                            ->placeholder('Document ID number')
                                            ->columnSpanFull(),
                                        SpatieMediaLibraryFileUpload::make('kyc_document')
                                            ->collection('kyc_documents')
                                            ->label('KYC Document')
                                            ->multiple()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        // Tab 4: Cards Management
                        Tab::make('Cards')
                            ->icon(Heroicon::CreditCard)
                            ->badge(fn($record) => $record?->cards()->count())
                            ->schema([
                                Section::make('Manage Cards')
                                    ->description('Add and manage user cards')
                                    ->schema([
                                        Repeater::make('cards')
                                            ->relationship('cards')
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        Select::make('user_account_id')
                                                            ->required()
                                                            ->relationship(name: 'userAccount')
                                                            ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->account_number}({$record->account_type}) "),

                                                        TextInput::make('card_holder_name')
                                                            ->label('Card Holder Name')
                                                            ->required()
                                                            ->maxLength(255),

                                                        Select::make('card_type')
                                                            ->options([
                                                                'debit' => 'Debit',
                                                                'credit' => 'Credit',
                                                                'virtual' => 'Virtual',
                                                            ])
                                                            ->required()
                                                            ->default('debit'),

                                                        Select::make('card_brand')
                                                            ->options([
                                                                'visa' => 'Visa',
                                                                'mastercard' => 'Mastercard',
                                                                'verve' => 'Verve',
                                                            ])
                                                            ->required()
                                                            ->default('visa'),

                                                        TextInput::make('card_number')
                                                            ->label('Card Number')
                                                            ->required()
                                                            ->unique(ignoreRecord: true)
                                                            ->maxLength(19)
                                                            ->placeholder('XXXX XXXX XXXX XXXX')
                                                            ->formatStateUsing(fn(?string $state): ?string => filled($state) ? decrypt($state) : null)
                                                            ->dehydrateStateUsing(function (?string $state) {
                                                                return filled($state) ? encrypt($state) : null;
                                                            }),

                                                        TextInput::make('cvv')
                                                            ->label('CVV')
                                                            ->required()
                                                            ->password()
                                                            ->revealable()
                                                            ->maxLength(4)
                                                            ->placeholder('XXX')
                                                            ->formatStateUsing(fn(?string $state): ?string => filled($state) ? decrypt($state) : null)
                                                            ->dehydrateStateUsing(function (?string $state) {
                                                                return filled($state) ? encrypt($state) : null;
                                                            }),

                                                        DatePicker::make('expiry_date')
                                                            ->label('Expiry Date')
                                                            ->required()
                                                            ->native(false)
                                                            ->displayFormat('m/Y'),

                                                        TextInput::make('daily_limit')
                                                            ->label('Daily Limit')
                                                            ->numeric()
                                                            ->prefix('₦')
                                                            ->default(100000),

                                                        Select::make('card_status')
                                                            ->options([
                                                                'active' => 'Active',
                                                                'blocked' => 'Blocked',
                                                                'expired' => 'Expired',
                                                                'lost' => 'Lost',
                                                            ])
                                                            ->default('active')
                                                            ->required(),

                                                        Toggle::make('is_contactless_enabled')
                                                            ->label('Contactless Enabled')
                                                            ->default(true),
                                                    ]),
                                            ])
                                            ->columns(1)
                                            ->defaultItems(0)
                                            ->addActionLabel('Add Card')
                                            ->collapsible()
                                            ->itemLabel(fn(array $state): ?string => $state['card_holder_name'] ?? 'New Card'),
                                    ]),
                            ])
                            ->visibleOn('edit'),

                        // Tab 5: Verification Codes
                        Tab::make('Verification Codes')
                            ->icon(Heroicon::ShieldCheck)
                            ->badge(fn($record) => $record?->verificationCodes()->count())
                            ->schema([
                                Section::make('Transaction Verification Codes')
                                    ->description('Manage verification codes for transactions (COT, TAX, IMF, etc.)')
                                    ->schema([
                                        Repeater::make('verificationCodes')
                                            ->relationship('verificationCodes')
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        Select::make('verification_type_id')
                                                            ->label('Verification Type')
                                                            ->relationship(
                                                                name: 'verificationType',
                                                            )
                                                            ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->name}({$record->code}) ")
                                                            ->required()
                                                            ->searchable()
                                                            ->unique(ignoreRecord: true)
                                                            ->preload(),

                                                        TextInput::make('code')
                                                            ->label('Verification Code')
                                                            ->required()
                                                            ->maxLength(50)
                                                            ->placeholder('Enter code')
                                                            ->helperText('User will need this code for transactions'),

                                                        DatePicker::make('expires_at')
                                                            ->label('Expiry Date')
                                                            ->native(false)
                                                            ->default(now()->addMonths(3))
                                                            ->helperText('Leave blank for no expiry'),

                                                        Toggle::make('is_used')
                                                            ->label('Already Used')
                                                            ->default(false),
                                                    ]),
                                            ])
                                            ->columns(1)
                                            ->defaultItems(0)
                                            ->addActionLabel('Add Verification Code')
                                            ->collapsible()
                                            ->itemLabel(
                                                fn(array $state): ?string =>
                                                \App\Models\VerificationType::find($state['verification_type_id'] ?? null)?->name ?? 'Verification Code'
                                            ),
                                    ]),
                            ])
                            ->visibleOn('edit'),

                        // Tab 6: Beneficiaries
                        Tab::make('Beneficiaries')
                            ->icon(Heroicon::Users)
                            ->badge(fn($record) => $record?->beneficiaries()->count())
                            ->schema([
                                Section::make('Saved Beneficiaries')
                                    ->description('Manage user\'s saved beneficiaries')
                                    ->schema([
                                        Repeater::make('beneficiaries')
                                            ->relationship('beneficiaries')
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        TextInput::make('account_name')
                                                            ->label('Account Name')
                                                            ->required()
                                                            ->maxLength(255),

                                                        TextInput::make('account_number')
                                                            ->label('Account Number')
                                                            ->required()
                                                            ->maxLength(20),

                                                        TextInput::make('bank_name')
                                                            ->label('Bank Name')
                                                            ->required()
                                                            ->maxLength(255),

                                                        TextInput::make('bank_code')
                                                            ->label('Bank Code')
                                                            ->maxLength(10),

                                                        TextInput::make('nickname')
                                                            ->label('Nickname')
                                                            ->maxLength(100)
                                                            ->placeholder('Optional'),

                                                        Toggle::make('is_favorite')
                                                            ->label('Favorite')
                                                            ->default(false),
                                                    ]),
                                            ])
                                            ->columns(1)
                                            ->defaultItems(0)
                                            ->addActionLabel('Add Beneficiary')
                                            ->collapsible()
                                            ->itemLabel(fn(array $state): ?string => $state['account_name'] ?? 'Beneficiary'),
                                    ]),
                            ])
                            ->visibleOn('edit'),
                        Tab::make('Deposit Details')
                            ->icon(Heroicon::Banknotes)
                            ->schema([
                                Toggle::make('use_default_deposit_details')
                                    ->onIcon(Heroicon::CheckCircle)
                                    ->offIcon(Heroicon::XCircle)
                                    ->label('Use Default Deposit Details')
                                    ->belowContent('If checked, the default deposit details in your settings will be used for deposits.')
                                    ->default(true),
                                Section::make('Crypto Deposit Details')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('enable_crypto_payment')
                                            ->onIcon(Heroicon::CheckCircle)
                                            ->offIcon(Heroicon::XCircle)
                                            ->label('Enable Crypto Payment')
                                            ->default(true)
                                            ->columnSpanFull()
                                            ->live(),
                                        Select::make('coin')
                                            ->options([
                                                'USDT' => 'USDT',
                                                'BUSD' => 'BUSD',
                                                'DAI' => 'DAI',
                                                'BTC' => 'BTC',
                                                'ETH' => 'ETH',
                                                'LTC' => 'LTC',
                                                'DOGE' => 'DOGE',
                                                'XRP' => 'XRP',
                                                'SOL' => 'SOL',
                                                'MATIC' => 'MATIC',
                                            ])
                                            ->default('USDT')
                                            ->required(fn(Get $get) => $get('enable_crypto_payment')),
                                        TextInput::make('crypto_name')
                                            ->required(fn(Get $get) => $get('enable_crypto_payment')),
                                        TextInput::make('network')
                                            ->default('TRC20')
                                            ->required(fn(Get $get) => $get('enable_crypto_payment')),
                                        TextInput::make('wallet_address')
                                            ->placeholder('eg : 0x1a2b3c4d5e6f7g8h9i0j1ky6z7')
                                            ->required(fn(Get $get) => $get('enable_crypto_payment')),
                                        KeyValue::make('more_crypto_attributes')
                                            ->columnSpanFull()
                                            ->keyPlaceholder('eg : chain')
                                            ->valuePlaceholder('eg : tron'),
                                    ]),
                                Section::make('Bank Deposit Details')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('enable_bank_payment')
                                            ->onIcon(Heroicon::CheckCircle)
                                            ->offIcon(Heroicon::XCircle)
                                            ->label('Enable Bank Payment')
                                            ->default(true)
                                            ->columnSpanFull()
                                            ->live(),
                                        TextInput::make('account_holder_name')
                                            ->required(fn(Get $get) => $get('enable_bank_payment')),
                                        TextInput::make('account_number')
                                            ->required(fn(Get $get) => $get('enable_bank_payment')),
                                        TextInput::make('bank_name')
                                            ->required(fn(Get $get) => $get('enable_bank_payment')),
                                        TextInput::make('iban')
                                            ->placeholder('eg:  GB29 NWBK 6016 1012 3456 78'),
                                        TextInput::make('swift')
                                            ->placeholder('eg : GB33 NWBK 6016 1012 3456 78'),
                                        KeyValue::make('more_bank_attributes')
                                            ->columnSpanFull()
                                            ->keyPlaceholder('eg : branch')
                                            ->valuePlaceholder('eg : 123'),
                                    ])

                            ])
                            ->visibleOn('edit'),
                        Tab::make('Transfer')
                            ->icon(Heroicon::CurrencyDollar)
                            ->schema([
                                Toggle::make('transfer_success')
                                    ->onIcon(Heroicon::CheckCircle)
                                    ->offIcon(Heroicon::XCircle)
                                    ->label('Transfer Successful')
                                    ->live()
                                    ->belowContent('If checked, the transfer will be marked as successful, otherwise it will be marked as failed.')
                                    ->default(true),
                                Textarea::make('failed_transfer_message')
                                    ->maxLength(191)
                                    ->required(fn(Get $get) => !$get('transfer_success'))
                                    ->belowContent('Message to be shown to the user when the transfer is not successful.'),
                            ])
                            ->visibleOn('edit'),
                        Tab::make('Preferences')
                            ->icon(Heroicon::Cog6Tooth)
                            ->columns(2)
                            ->schema([
                                Toggle::make('can_add_card')
                                    ->onIcon(Heroicon::CheckCircle)
                                    ->offIcon(Heroicon::XCircle)
                                    ->label('Can Add Card')
                                    ->belowContent('If checked, the user will be able to add his debit or credit cards.')
                                    ->default(true),
                                Toggle::make('can_manage_card')
                                    ->onIcon(Heroicon::CheckCircle)
                                    ->offIcon(Heroicon::XCircle)
                                    ->label('Can Manage Card')
                                    ->belowContent('If checked, the user will be able to block or remove cards.')
                                    ->default(true),
                                Toggle::make('see_their_cards')
                                    ->onIcon(Heroicon::CheckCircle)
                                    ->offIcon(Heroicon::XCircle)
                                    ->label('See Their Cards')
                                    ->belowContent('If checked, the user will be able to see his debit or credit cards.')
                                    ->default(true),
                                Toggle::make('can_add_beneficiary')
                                    ->onIcon(Heroicon::CheckCircle)
                                    ->offIcon(Heroicon::XCircle)
                                    ->label('Can Add Beneficiary')
                                    ->belowContent('If checked, the user will be able to add beneficiaries.')
                                    ->default(true),
                                Toggle::make('can_manage_beneficiary')
                                    ->onIcon(Heroicon::CheckCircle)
                                    ->offIcon(Heroicon::XCircle)
                                    ->label('Can Manage Beneficiary')
                                    ->belowContent('If checked, the user will be able to manage beneficiaries.')
                                    ->default(true),
                                Toggle::make('see_their_beneficiaries')
                                    ->onIcon(Heroicon::CheckCircle)
                                    ->offIcon(Heroicon::XCircle)
                                    ->label('See Their Beneficiaries')
                                    ->belowContent('If checked, the user will be able to see his beneficiaries.')
                                    ->default(true),
                                Toggle::make('can_setup_2fa')
                                    ->onIcon(Heroicon::CheckCircle)
                                    ->offIcon(Heroicon::XCircle)
                                    ->label('Can Setup 2FA')
                                    ->belowContent('If checked, the user will be able to setup two-factor authentication.')
                                    ->default(true),
                                Toggle::make('can_change_trasnaction_pin')
                                    ->onIcon(Heroicon::CheckCircle)
                                    ->offIcon(Heroicon::XCircle)
                                    ->label('Can Change Transaction Pin')
                                    ->belowContent('If checked, the user will be able to change his transaction pin.')
                                    ->default(true),

                            ])
                            ->visibleOn('edit'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
