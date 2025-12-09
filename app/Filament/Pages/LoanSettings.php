<?php

namespace App\Filament\Pages;

use App\Models\LoanSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class LoanSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $navigationLabel = 'Loan Settings';
    protected static string | \UnitEnum | null $navigationGroup = 'Loan Management';
    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.loan-settings';
    // protected static ?string $slug = 'loan-settings';

    public ?array $data = [];

    protected LoanSetting $loadSettings;

    public function mount(): void
    {
        // ✅ Always edit the first settings row
        $this->loadSettings = LoanSetting::find(1);

        // ✅ Fill form with DB values
        $this->form->fill($this->loadSettings->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(2)
            ->components([
                Checkbox::make('loan_applications_enabled')
                    ->label('Enable Loan Applications')
                    ->columnSpanFull(),

                TextInput::make('max_loan_amount')
                    ->numeric()
                    ->label('Maximum Loan Amount')
                    ->required(),

                TextInput::make('max_active_loans_per_user')
                    ->numeric()
                    ->label('Max Active Loans Per User')
                    ->required(),

                TextInput::make('min_account_age_days')
                    ->numeric()
                    ->label('Minimum Account Age (Days)')
                    ->required(),

                TextInput::make('min_account_balance')
                    ->numeric()
                    ->label('Minimum Account Balance')
                    ->required(),

                Checkbox::make('require_guarantor')
                    ->live()
                    ->label('Require Guarantor'),

                TextInput::make('min_guarantors')
                    ->numeric()
                    ->label('Minimum Guarantors')
                    ->required(fn($get) => $get('require_guarantor')),

                Repeater::make('required_documents')
                    ->label('Required Documents')
                    ->simple(TextInput::make('name')
                        ->label('Document Name')
                        ->placeholder('eg : ID Card')
                        ->required())
                    ->addActionLabel('Add Document')
                    ->columnSpanFull(),

                Textarea::make('terms_and_conditions')
                    ->label('Terms & Conditions')
                    ->rows(6)
                    ->columnSpanFull(),
            ]);
    }

    public function save(): void
    {
        $loadSettings = LoanSetting::find(1);
        $loadSettings->update($this->form->getState());

        Notification::make()
            ->success()
            ->title('Loan settings updated successfully')
            ->send();
    }
}
