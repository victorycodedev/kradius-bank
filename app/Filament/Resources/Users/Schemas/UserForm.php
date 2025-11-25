<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                                            ->maxLength(4)
                                            ->placeholder('4-digit PIN')
                                            ->belowContent('4-digit PIN for transactions'),

                                        Toggle::make('two_factor_enabled')
                                            ->label('Two-Factor Authentication')
                                            ->inline(false)
                                            ->default(fn(Model $record): bool => $record->two_factor_secret !== null)
                                            ->disabled(fn(Model $record): bool => $record->two_factor_secret !== null)
                                            ->onIcon(Heroicon::CheckCircle)
                                            ->offIcon(Heroicon::XCircle),

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
                                                            ->placeholder('XXXX XXXX XXXX XXXX'),

                                                        TextInput::make('cvv')
                                                            ->label('CVV')
                                                            ->required()
                                                            ->password()
                                                            ->maxLength(4)
                                                            ->placeholder('XXX'),

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
                                                            ->disabled()
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
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
