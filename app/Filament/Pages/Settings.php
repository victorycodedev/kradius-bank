<?php

namespace App\Filament\Pages;

use App\Models\Settings as ModelsSettings;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Settings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $navigationLabel = 'System Configuration';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 4;


    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public ModelsSettings $settings;

    public function mount(): void
    {
        $this->settings = ModelsSettings::get();
        $this->form->fill($this->settings->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(1)
            ->components([
                Tabs::make('System Settings')
                    ->tabs([
                        // ✅ APP IDENTITY & BRANDING
                        Tab::make('App Identity')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Section::make('Basic Information')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('app_name')
                                                ->label('Application Name')
                                                ->required()
                                                ->maxLength(255),

                                            TextInput::make('app_short_name')
                                                ->label('Short Name')
                                                ->maxLength(50),

                                            TextInput::make('app_slogan')
                                                ->label('Slogan/Tagline')
                                                ->maxLength(255),

                                            TextInput::make('app_url')
                                                ->label('Website URL')
                                                ->url()
                                                ->prefix('https://'),

                                            TextInput::make('app_version')
                                                ->label('Version')
                                                ->required(),

                                            TextInput::make('copyright_text')
                                                ->label('Copyright Text')
                                                ->placeholder('© 2024 Company Name. All rights reserved.'),
                                        ]),
                                    ]),

                                Section::make('Branding & Colors')
                                    ->schema([
                                        Grid::make(4)->schema([
                                            TextInput::make('primary_color')
                                                ->label('Primary Color')
                                                ->required()
                                                ->type('color'),

                                            TextInput::make('secondary_color')
                                                ->label('Secondary Color')
                                                ->required()
                                                ->type('color'),

                                            TextInput::make('accent_color')
                                                ->label('Accent Color')
                                                ->required()
                                                ->type('color'),

                                            Select::make('font_family')
                                                ->label('Font Family')
                                                ->required()
                                                ->options([
                                                    'Inter' => 'Inter (Modern, Clean)',
                                                    'Roboto' => 'Roboto (Google Default)',
                                                    'Open Sans' => 'Open Sans (Friendly)',
                                                    'Lato' => 'Lato (Professional)',
                                                    'Montserrat' => 'Montserrat (Bold, Stylish)',
                                                    'Poppins' => 'Poppins (Geometric)',
                                                    'Raleway' => 'Raleway (Elegant)',
                                                    'Ubuntu' => 'Ubuntu (Humanist)',
                                                    'Nunito' => 'Nunito (Rounded, Friendly)',
                                                    'Playfair Display' => 'Playfair Display (Serif, Elegant)',
                                                    'Merriweather' => 'Merriweather (Readable Serif)',
                                                    'Source Sans Pro' => 'Source Sans Pro (Adobe)',
                                                    'Oswald' => 'Oswald (Condensed, Bold)',
                                                    'PT Sans' => 'PT Sans (Universal)',
                                                    'Work Sans' => 'Work Sans (Versatile)',
                                                ])
                                                ->default('Inter')
                                                ->searchable()
                                                ->native(false)
                                                ->helperText('Choose the primary font for your application'),
                                        ]),

                                        Toggle::make('dark_mode_enabled')
                                            ->label('Enable Dark Mode')
                                            ->helperText('Allow users to switch to dark mode'),
                                    ]),

                                Section::make('Media Assets')
                                    ->description('Upload logos and branding images (optimized automatically)')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            SpatieMediaLibraryFileUpload::make('logo')
                                                ->collection('logo')
                                                ->image()
                                                ->imageEditor()
                                                ->maxSize(2048)
                                                ->helperText('Main logo (PNG, JPG, WebP, SVG)'),

                                            SpatieMediaLibraryFileUpload::make('favicon')
                                                ->collection('favicon')
                                                ->image()
                                                ->maxSize(512)
                                                ->helperText('Favicon (ICO, PNG, 32x32)'),

                                            SpatieMediaLibraryFileUpload::make('login_banner')
                                                ->collection('login_banner')
                                                ->image()
                                                ->imageEditor()
                                                ->maxSize(5120)
                                                ->helperText('Login page banner'),
                                        ]),
                                    ]),
                            ]),

                        // ✅ CONTACT & SOCIAL
                        Tab::make('Contact & Social')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Section::make('Support Contact')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('support_email')
                                                ->email()
                                                ->label('Support Email'),

                                            TextInput::make('notifiable_email')
                                                ->email()
                                                ->label('Admin Notification Email')
                                                ->helperText('Receives system notifications'),

                                            TextInput::make('support_phone')
                                                ->tel()
                                                ->label('Support Phone'),

                                            TextInput::make('support_whatsapp')
                                                ->tel()
                                                ->label('WhatsApp Number'),

                                            TextInput::make('support_working_hours')
                                                ->label('Working Hours')
                                                ->placeholder('Mon-Fri: 9AM-5PM'),
                                        ]),

                                        Textarea::make('support_address')
                                            ->label('Physical Address')
                                            ->rows(2),
                                    ]),

                                Section::make('Social Media')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('facebook_url')
                                                ->url()
                                                ->prefix('https://facebook.com/'),

                                            TextInput::make('twitter_url')
                                                ->url()
                                                ->prefix('https://twitter.com/'),

                                            TextInput::make('instagram_url')
                                                ->url()
                                                ->prefix('https://instagram.com/'),

                                            TextInput::make('linkedin_url')
                                                ->url()
                                                ->prefix('https://linkedin.com/'),
                                        ]),
                                    ]),
                            ]),

                        // ✅ FINANCIAL SETTINGS
                        Tab::make('Financial Rules')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Section::make('Deposit Settings')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('minimum_deposit')
                                                ->numeric()
                                                ->prefix('₦')
                                                ->required(),

                                            TextInput::make('maximum_deposit')
                                                ->numeric()
                                                ->prefix('₦')
                                                ->required(),

                                            Toggle::make('charge_deposit_fee')
                                                ->label('Charge Deposit Fee')
                                                ->live(),

                                            Select::make('deposit_fee_type')
                                                ->options([
                                                    'fixed' => 'Fixed Amount',
                                                    'percentage' => 'Percentage',
                                                ])
                                                ->visible(fn($get) => $get('charge_deposit_fee')),

                                            TextInput::make('deposit_fee_amount')
                                                ->numeric()
                                                ->prefix(fn($get) => $get('deposit_fee_type') === 'percentage' ? '' : '₦')
                                                ->suffix(fn($get) => $get('deposit_fee_type') === 'percentage' ? '%' : '')
                                                ->visible(fn($get) => $get('charge_deposit_fee')),
                                        ]),
                                    ]),

                                Section::make('Withdrawal Settings')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('minimum_withdrawal')
                                                ->numeric()
                                                ->prefix('₦')
                                                ->required(),

                                            TextInput::make('maximum_withdrawal')
                                                ->numeric()
                                                ->prefix('₦')
                                                ->required(),

                                            TextInput::make('withdrawal_processing_days')
                                                ->numeric()
                                                ->required()
                                                ->suffix('days')
                                                ->default(1),

                                            Select::make('withdrawal_fee_type')
                                                ->options([
                                                    'fixed' => 'Fixed Amount',
                                                    'percentage' => 'Percentage',
                                                ])
                                                ->live(),

                                            TextInput::make('withdrawal_fee_amount')
                                                ->numeric()
                                                ->prefix(fn($get) => $get('withdrawal_fee_type') === 'percentage' ? '' : '₦')
                                                ->suffix(fn($get) => $get('withdrawal_fee_type') === 'percentage' ? '%' : ''),
                                        ]),
                                    ]),

                                Section::make('Transfer Settings')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('minimum_transfer')
                                                ->numeric()
                                                ->prefix('₦')
                                                ->required(),

                                            TextInput::make('maximum_transfer')
                                                ->numeric()
                                                ->prefix('₦')
                                                ->required(),

                                            Toggle::make('allow_international_transfers')
                                                ->label('Allow International Transfers'),

                                            Select::make('transfer_fee_type')
                                                ->options([
                                                    'fixed' => 'Fixed Amount',
                                                    'percentage' => 'Percentage',
                                                ])
                                                ->required()
                                                ->default('fixed')
                                                ->live(),

                                            TextInput::make('transfer_fee_amount')
                                                ->numeric()
                                                ->prefix(fn($get) => $get('transfer_fee_type') === 'percentage' ? '' : '₦')
                                                ->suffix(fn($get) => $get('transfer_fee_type') === 'percentage' ? '%' : ''),
                                        ]),
                                    ]),

                                Section::make('Transaction Limits')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('daily_transaction_limit')
                                                ->numeric()
                                                ->prefix('₦')
                                                ->required()
                                                ->helperText('Max per day per user'),

                                            TextInput::make('monthly_transaction_limit')
                                                ->numeric()
                                                ->prefix('₦')
                                                ->helperText('Max per month per user'),

                                            TextInput::make('max_transactions_per_day')
                                                ->numeric()
                                                ->suffix('transactions')
                                                ->helperText('Count limit per day'),
                                        ]),
                                    ]),
                            ]),
                        Tab::make('Deposit Details')
                            ->icon(Heroicon::Banknotes)
                            ->schema([
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

                            ]),
                        // ✅ SECURITY
                        Tab::make('Security')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Section::make('Authentication')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Toggle::make('require_email_verification')
                                                ->label('Require Email Verification'),

                                            Toggle::make('require_phone_verification')
                                                ->label('Require Phone Verification'),

                                            Toggle::make('require_2fa')
                                                ->label('Require Two-Factor Authentication'),

                                            Toggle::make('force_2fa_for_withdrawals')
                                                ->label('Force 2FA for Withdrawals'),

                                            Toggle::make('require_transaction_pin')
                                                ->label('Require Transaction PIN'),

                                            Toggle::make('auto_logout_on_idle')
                                                ->label('Auto-Logout on Idle'),
                                        ]),
                                    ]),

                                Section::make('Session & Login')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('session_timeout_minutes')
                                                ->numeric()
                                                ->suffix('minutes')
                                                ->default(30),

                                            TextInput::make('max_failed_login_attempts')
                                                ->numeric()
                                                ->suffix('attempts')
                                                ->default(5),

                                            TextInput::make('lockout_duration_minutes')
                                                ->numeric()
                                                ->suffix('minutes')
                                                ->default(30),

                                            TextInput::make('password_expiry_days')
                                                ->numeric()
                                                ->suffix('days')
                                                ->helperText('Users must change password after this period'),
                                        ]),
                                    ]),
                            ]),

                        // ✅ KYC
                        Tab::make('KYC & Verification')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Section::make('KYC Requirements')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Toggle::make('kyc_required')
                                                ->label('Require KYC Verification')
                                                ->live(),

                                            TextInput::make('min_kyc_level_for_withdrawal')
                                                ->numeric()
                                                ->label('Min KYC Level for Withdrawal')
                                                ->default(1)
                                                ->visible(fn($get) => $get('kyc_required')),

                                            TextInput::make('min_kyc_level_for_transfer')
                                                ->numeric()
                                                ->label('Min KYC Level for Transfer')
                                                ->default(1)
                                                ->visible(fn($get) => $get('kyc_required')),

                                            TextInput::make('kyc_expiry_months')
                                                ->numeric()
                                                ->suffix('months')
                                                ->helperText('Re-verify KYC after this period'),
                                        ]),
                                    ]),

                                Section::make('Document Requirements')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Toggle::make('require_selfie')
                                                ->label('Require Selfie Photo'),

                                            Toggle::make('require_id_upload')
                                                ->label('Require ID Document'),

                                            Toggle::make('require_address_proof')
                                                ->label('Require Address Proof'),
                                        ]),

                                        Select::make('allowed_id_types')
                                            ->multiple()
                                            ->options([
                                                'passport' => 'International Passport',
                                                'nin' => 'National ID (NIN)',
                                                'drivers_license' => 'Driver\'s License',
                                                'voters_card' => 'Voter\'s Card',
                                            ])
                                            ->helperText('Select acceptable ID types'),
                                    ]),
                            ]),

                        // ✅ NOTIFICATIONS (simplified for space)
                        Tab::make('Notifications')
                            ->icon('heroicon-o-bell')
                            ->schema([
                                Section::make('Notification Channels')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Toggle::make('email_notifications_enabled'),
                                            Toggle::make('sms_notifications_enabled'),
                                            Toggle::make('push_notifications_enabled'),
                                        ]),
                                    ]),

                                Section::make('Notification Events')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Toggle::make('notify_on_login'),
                                            Toggle::make('notify_on_transaction'),
                                            Toggle::make('notify_on_kyc_status'),
                                            Toggle::make('notify_on_loan_status'),
                                        ]),
                                    ]),
                            ]),

                        // ✅ SYSTEM CONTROL
                        Tab::make('System Control')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Section::make('Feature Toggles')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Toggle::make('allow_registration')
                                                ->label('Allow New Registrations'),

                                            Toggle::make('allow_transfers')
                                                ->label('Allow Transfers'),

                                            Toggle::make('allow_withdrawals')
                                                ->label('Allow Withdrawals'),

                                            Toggle::make('allow_deposits')
                                                ->label('Allow Deposits'),

                                            Toggle::make('demo_mode')
                                                ->label('Demo Mode')
                                                ->helperText('For testing purposes'),
                                        ]),
                                    ]),

                                Section::make('Maintenance Mode')
                                    ->schema([
                                        Toggle::make('maintenance_mode')
                                            ->label('Enable Maintenance Mode')
                                            ->live(),

                                        Textarea::make('maintenance_message')
                                            ->label('Maintenance Message')
                                            ->rows(2)
                                            ->placeholder('We are currently performing system maintenance...')
                                            ->visible(fn($get) => $get('maintenance_mode')),
                                    ]),
                            ]),

                        // ✅ LEGAL
                        Tab::make('Legal')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Legal Documents')
                                    ->schema([
                                        RichEditor::make('terms_and_conditions')
                                            ->label('Terms and Conditions')
                                            ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList'])
                                            ->columnSpanFull(),

                                        RichEditor::make('privacy_policy')
                                            ->label('Privacy Policy')
                                            ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList'])
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Company Information')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('company_registration_number')
                                                ->label('Company Registration Number'),

                                            TextInput::make('tax_identification_number')
                                                ->label('Tax ID (TIN)'),
                                        ]),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ]);
    }

    public function save(): void
    {
        $this->validate();

        try {

            $this->settings->update($this->form->getState());
            ModelsSettings::clearCache();

            Notification::make()
                ->title('Settings Saved')
                ->success()
                ->body('System settings have been updated successfully.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->danger()
                ->body('Failed to save settings: ' . $e->getMessage())
                ->send();
        }
    }
}
