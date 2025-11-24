<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->columnSpanFull()
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
                            ->placeholder('+1 234 567 8900')
                            ->unique(ignoreRecord: true),

                        DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->maxDate(now()->subYears(18))
                            ->displayFormat('d/m/Y')
                            ->native(false),
                    ])
                    ->collapsible(),

                Section::make('Authentication')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->required(fn(string $context): bool => $context === 'create')
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->rule(Password::default())
                            ->placeholder('••••••••')
                            ->helperText('Leave blank to keep current password (edit mode)'),

                        TextInput::make('pin')
                            ->label('Transaction PIN')
                            ->password()
                            ->numeric()
                            ->maxLength(4)
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->placeholder('4-digit PIN')
                            ->helperText('4-digit PIN for transaction verification'),

                        Toggle::make('biometric_enabled')
                            ->label('Biometric Authentication')
                            ->default(false)
                            ->inline(false)
                            ->onIcon(Heroicon::CheckCircle)
                            ->offIcon(Heroicon::XCircle),

                        Toggle::make('two_factor_enabled')
                            ->label('Two-Factor Authentication')
                            ->default(false)
                            ->inline(false)
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
                    ])
                    ->collapsible(),

                Section::make('Address Information')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('address')
                            ->rows(2)
                            ->maxLength(500)
                            ->placeholder('Street address'),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('city')
                                    ->maxLength(255)
                                    ->placeholder('City'),

                                TextInput::make('state')
                                    ->maxLength(255)
                                    ->placeholder('State/Province'),

                                TextInput::make('zip_code')
                                    ->maxLength(255)
                                    ->placeholder('Postal Code'),
                            ]),

                        TextInput::make('country')
                            ->maxLength(255)
                            ->placeholder('Country')
                            ->default('Nigeria'),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('KYC Information')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
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
                            ]),

                        TextInput::make('kyc_document_number')
                            ->label('Document Number')
                            ->maxLength(255)
                            ->placeholder('Document ID number'),

                        SpatieMediaLibraryFileUpload::make('avatars')
                            ->label('Profile Photo')
                            ->image()
                            ->imageEditor()
                            ->circleCropper()
                            ->collection('avatars')
                            ->disk('public')
                            ->maxSize(2048)
                            ->helperText('Maximum file size: 2MB'),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Account Security')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
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
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->visibleOn('edit'),
            ]);
    }
}
