<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use function Livewire\Volt\title;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_account_id')
                    ->relationship(name: 'userAccount', titleAttribute: 'id')
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->user->name} - {$record->account_number}({$record->account_type}) - {$record->balance}")
                    ->required(),
                Select::make('transaction_type')
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                        'transfer' => 'Transfer',
                        'withdrawal' => 'Withdrawal',
                        'deposit' => 'Deposit',
                    ])
                    ->default('debit')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('USD'),
                TextInput::make('balance_before')
                    ->required()
                    ->numeric(),
                TextInput::make('balance_after')
                    ->required()
                    ->numeric(),
                Textarea::make('description')
                    ->columnSpanFull(),

                // Select::make('recipient_account_id')
                //     ->relationship(name: 'recipientAccount', titleAttribute: 'id', modifyQueryUsing: fn(Builder $query) => $query->role('User')),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'pending_verification' => 'Pending verification',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'reversed' => 'Reversed',
                    ])
                    ->default('pending')
                    ->required(),

                Select::make('channel')
                    ->options([
                        'mobile_app' => 'Mobile app',
                        'atm' => 'Atm',
                        'pos' => 'Pos',
                        'web' => 'Web',
                        'bank_transfer' => 'Bank transfer',
                    ])
                    ->default('mobile_app')
                    ->required(),

                DateTimePicker::make('completed_at'),
            ]);
    }
}
