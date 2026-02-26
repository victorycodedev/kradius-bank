<?php

namespace App\Filament\Resources\Investments\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class InvestmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship(name: 'user', titleAttribute: 'name', modifyQueryUsing: fn($query) => $query->role('User'))
                    ->required(),
                Select::make('stock_id')
                    ->relationship(name: 'stock', titleAttribute: 'name')
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->name} ({$record->symbol})")
                    ->required(),
                Select::make('user_account_id')
                    ->relationship(name: 'userAccount', titleAttribute: 'id')
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->account_number}({$record->account_type})")
                    ->required(),
                TextInput::make('reference_number')
                    ->suffixAction(
                        Action::make('generate_reference_number')
                            ->icon(Heroicon::ArrowPathRoundedSquare)
                            ->action(function (Set $set, Get $get) {
                                $ref = rand(100000, 999999);
                                $set('reference_number', 'INV-' . $ref);
                            }),
                    )
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->placeholder('eg : 1000')
                    ->numeric()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $purchasePrice = $get('purchase_price');

                        if ($purchasePrice > 0) {
                            $shares = $state / $purchasePrice;
                            $set('shares', number_format($shares, 8, '.', ''));
                        }
                    }),

                TextInput::make('purchase_price')
                    ->required()
                    ->placeholder('eg : 100')
                    ->numeric()
                    ->prefix('$')
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $amount = $get('amount');

                        if ($amount > 0) {
                            $shares = $amount / $state;
                            $set('shares', number_format($shares, 8, '.', ''));
                        }
                    }),

                TextInput::make('shares')
                    ->required()
                    ->numeric()
                    ->disabled() // user cannot edit
                    ->dehydrated() // save value
                    ->placeholder('Auto calculated'),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'liquidated' => 'Liquidated',
                    ])
                    ->default('pending')
                    ->required(),

                Select::make('investment_type')
                    ->options(['long_term' => 'Long term', 'short_term' => 'Short term', 'day_trade' => 'Day trade'])
                    ->default('long_term')
                    ->required(),

                TextInput::make('duration_days')
                    ->label('Duration (Days)')
                    ->placeholder('eg : 30')
                    ->suffix('Days')
                    ->maxValue(365)
                    ->numeric(),

                TextInput::make('roi_percentage')
                    ->label('ROI %')
                    ->placeholder('eg : 10')
                    ->suffix('%')
                    ->numeric(),

                DatePicker::make('maturity_date'),

                Textarea::make('admin_notes')
                    ->columnSpanFull(),
            ]);
    }
}
