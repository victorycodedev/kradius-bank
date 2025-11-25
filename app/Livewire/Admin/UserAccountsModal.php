<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\UserAccount;
use App\Traits\CreatesUserAccount;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;

class UserAccountsModal extends Component implements HasSchemas, HasActions
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use CreatesUserAccount;

    public $accounts;
    public $userId;
    public $user;

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->user = User::findOrFail($userId);
        $this->loadAccounts();
    }

    public function loadAccounts()
    {
        $this->accounts = UserAccount::with('limits')
            ->where('user_id', $this->userId)
            ->get();
    }

    public function changeStatusAction(): Action
    {
        return Action::make('changeStatus')
            ->label('Change Status')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->size('sm')
            ->schema([
                Select::make('status')
                    ->label('New Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'closed' => 'Closed',
                    ])
                    ->required(),
                Textarea::make('reason')
                    ->label('Reason')
                    ->rows(2),
            ])
            ->action(function (array $data, array $arguments): void {
                $account = UserAccount::find($arguments['accountId']);

                $account->update(['status' => $data['status']]);

                if (in_array($data['status'], ['closed', 'inactive'])) {
                    $account->update([
                        'frozen' => true,
                        'frozen_at' => now(),
                        'frozen_reason' => $data['reason'] ?? 'Status changed',
                    ]);
                } else {
                    $account->update([
                        'frozen' => false,
                        'frozen_at' => null,
                        'frozen_reason' => null,
                    ]);
                }

                $this->loadAccounts();

                Notification::make()
                    ->success()
                    ->title('Account status updated')
                    ->send();
            })
            ->requiresConfirmation();
    }

    public function editLimitsAction(): Action
    {
        return Action::make('editLimits')
            ->label('Edit Limits')
            ->icon('heroicon-o-pencil')
            ->color('info')
            ->size('sm')
            ->fillForm(function (array $arguments): array {
                $account = UserAccount::with('limits')->find($arguments['accountId']);
                return [
                    'daily_transfer_limit' => $account->limits?->daily_transfer_limit ?? 500000,
                    'daily_withdrawal_limit' => $account->limits?->daily_withdrawal_limit ?? 200000,
                    'single_transaction_limit' => $account->limits?->single_transaction_limit ?? 100000,
                ];
            })
            ->schema([
                Grid::make(1)
                    ->schema([
                        TextInput::make('daily_transfer_limit')
                            ->label('Daily Transfer Limit')
                            ->numeric()
                            ->required()
                            ->prefix('₦')
                            ->minValue(0),

                        TextInput::make('daily_withdrawal_limit')
                            ->label('Daily Withdrawal Limit')
                            ->numeric()
                            ->required()
                            ->prefix('₦')
                            ->minValue(0),

                        TextInput::make('single_transaction_limit')
                            ->label('Single Transaction Limit')
                            ->numeric()
                            ->required()
                            ->prefix('₦')
                            ->minValue(0),
                    ]),
            ])
            ->action(function (array $data, array $arguments): void {
                $account = UserAccount::with('limits')->find($arguments['accountId']);

                if ($account->limits) {
                    $account->limits->update($data);
                } else {
                    $account->limits()->create($data);
                }

                $this->loadAccounts();

                Notification::make()
                    ->success()
                    ->title('Account limits updated')
                    ->send();
            })
            ->modalHeading('Edit Account Limits');
    }

    public function setPrimaryAction(): Action
    {
        return Action::make('setPrimary')
            ->label('Set as Primary')
            ->icon('heroicon-o-star')
            ->color('success')
            ->size('sm')
            ->action(function (array $arguments): void {
                // Remove primary from all accounts
                UserAccount::where('user_id', $this->userId)
                    ->update(['is_primary' => false]);

                // Set this account as primary
                $account = UserAccount::find($arguments['accountId']);
                $account->update(['is_primary' => true]);

                $this->loadAccounts();

                Notification::make()
                    ->success()
                    ->title('Primary account updated')
                    ->send();
            })
            ->requiresConfirmation()
            ->visible(fn(array $arguments) => !UserAccount::find($arguments['accountId'])->is_primary)
            ->modalHeading('Set as Primary Account')
            ->modalDescription('This will make this account the primary account for all transactions.');
    }

    public function createAccountAction(): Action
    {
        return Action::make('createAccount')
            ->label('Create New Account')
            ->icon(Heroicon::PlusCircle)
            ->color('success')
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('account_type')
                            ->label('Account Type')
                            ->options([
                                'savings' => 'Savings',
                                'current' => 'Current',
                                'fixed_deposit' => 'Fixed Deposit',
                            ])
                            ->required()
                            ->default('savings'),

                        Select::make('account_tier')
                            ->label('Account Tier')
                            ->options([
                                'basic' => 'Basic',
                                'premium' => 'Premium',
                                'gold' => 'Gold',
                            ])
                            ->required()
                            ->default('basic')
                            ->live(),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('currency')
                            ->label('Currency')
                            ->default('NGN')
                            ->required()
                            ->maxLength(3),

                        TextInput::make('interest_rate')
                            ->label('Interest Rate (%)')
                            ->numeric()
                            ->default(2.5)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                    ]),

                Toggle::make('is_primary')
                    ->label('Set as Primary Account')
                    ->helperText('If checked, this will become the primary account'),
            ])
            ->action(function (array $data): void {
                // If setting as primary, remove primary from other accounts
                if ($data['is_primary'] ?? false) {
                    UserAccount::where('user_id', $this->userId)
                        ->update(['is_primary' => false]);
                }

                $accountAlreadyExists = UserAccount::where('user_id', $this->userId)
                    ->where('account_type', $data['account_type'])
                    ->where('account_tier', $data['account_tier'])
                    ->where('currency', $data['currency'])
                    ->exists();

                if ($accountAlreadyExists) {
                    Notification::make()
                        ->danger()
                        ->title('Account already exists')
                        ->send();

                    return;
                }

                // Create the account
                $account = $this->createDefaultAccount($this->user, [
                    'account_type' => $data['account_type'],
                    'account_tier' => $data['account_tier'],
                    'currency' => $data['currency'],
                    'interest_rate' => $data['interest_rate'] ?? 2.5,
                    'is_primary' => $data['is_primary'] ?? false,
                ]);

                $this->loadAccounts();

                Notification::make()
                    ->success()
                    ->title('Account created successfully')
                    ->body("Account Number: {$account->account_number}")
                    ->send();
            })
            ->modalHeading('Create New Account');
    }

    public function deleteAccountAction(): Action
    {
        return Action::make('deleteAccount')
            ->label('Delete')
            ->icon(Heroicon::Trash)
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (array $arguments): void {
                $account = UserAccount::find($arguments['accountId']);

                // Don't allow deleting primary account if it's the only one
                if ($account->is_primary && $this->accounts->count() === 1) {
                    Notification::make()
                        ->danger()
                        ->title('Cannot delete')
                        ->body('Cannot delete the only account for this user')
                        ->send();
                    return;
                }

                // Don't allow deleting if balance > 0
                if ($account->balance > 0) {
                    Notification::make()
                        ->danger()
                        ->title('Cannot delete')
                        ->body('Cannot delete account with balance. Please transfer funds first.')
                        ->send();
                    return;
                }

                // If deleting primary account, set another as primary
                if ($account->is_primary) {
                    $newPrimary = UserAccount::where('user_id', $this->userId)
                        ->where('id', '!=', $account->id)
                        ->first();

                    if ($newPrimary) {
                        $newPrimary->update(['is_primary' => true]);
                    }
                }

                $account->delete();
                $this->loadAccounts();

                Notification::make()
                    ->success()
                    ->title('Account deleted')
                    ->send();
            })
            ->modalHeading('Delete Account')
            ->modalDescription('Are you sure you want to delete this account? This action cannot be undone.');
    }

    public function render()
    {
        return view('livewire.admin.user-accounts-modal');
    }
}
