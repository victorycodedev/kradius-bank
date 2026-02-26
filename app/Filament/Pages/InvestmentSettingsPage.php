<?php

namespace App\Filament\Pages;

use App\Models\InvestmentSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;

class InvestmentSettingsPage extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.investment-settings-page';
    protected static string | \UnitEnum | null $navigationGroup = 'Stock Trading';
    protected static ?string $title = 'Stock trading settings';
    protected static ?string $navigationLabel = 'Settings';
    protected ?string $heading = 'Stock Settings';
    protected ?string $subheading = 'Manage your stock trading settings';
    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    protected InvestmentSetting $settings;

    public function mount(): void
    {
        $this->settings = InvestmentSetting::get();
        $this->form->fill($this->settings->toArray());
        // dd($this->form->getState(), $this->data);
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->model(InvestmentSetting::class)
            ->columns(1)
            ->components([
                Section::make('General Investment Settings')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Toggle::make('investments_enabled')
                            ->label('Enable Investments')
                            ->default(true),

                        Toggle::make('auto_profit_enabled')
                            ->label('Enable Auto Profit')
                            ->default(false),

                        Select::make('auto_profit_frequency')
                            ->label('Auto Profit Frequency')
                            ->options([
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                                'monthly' => 'Monthly',
                            ])
                            ->required(),
                        TextInput::make('max_active_investments_per_user')
                            ->label('Max Active Investments Per User')
                            ->numeric()
                            ->required(),
                    ]),

                // Section::make('Profit & ROI Settings')
                //     ->columns(2)
                //     ->schema([
                //         TextInput::make('default_roi_percentage')
                //             ->label('Default ROI (%)')
                //             ->numeric()
                //             ->suffix('%')
                //             ->required(),

                //         TextInput::make('default_investment_duration_days')
                //             ->label('Default Investment Duration (Days)')
                //             ->numeric()
                //             ->required(),
                //     ]),

                // Section::make('Withdrawal & Penalty Settings')
                //     ->columns(2)
                //     ->schema([
                //         Toggle::make('allow_partial_withdrawal')
                //             ->label('Allow Partial Withdrawal'),

                //         TextInput::make('early_withdrawal_penalty')
                //             ->label('Early Withdrawal Penalty (%)')
                //             ->numeric()
                //             ->suffix('%')
                //             ->required(),
                //     ]),

                Section::make('User Eligibility Requirements')
                    ->columns(2)
                    ->schema([
                        Toggle::make('require_kyc_for_investment')
                            ->label('Require KYC for Investment'),

                        TextInput::make('min_account_age_days')
                            ->label('Minimum Account Age (Days)')
                            ->numeric()
                            ->required()
                            ->helperText('Enter 0 if not required'),
                    ]),

                Section::make('Investment Terms')
                    ->schema([
                        Textarea::make('investment_terms')
                            ->label('Investment Terms & Conditions')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(): void
    {
        $this->validate();

        $loadSettings = InvestmentSetting::find(1);
        $loadSettings->update($this->form->getState());

        Notification::make()
            ->success()
            ->title('Investment settings updated successfully')
            ->send();
    }
}
