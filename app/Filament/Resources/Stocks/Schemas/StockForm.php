<?php

namespace App\Filament\Resources\Stocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('symbol')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('category')
                    ->options([
                        'Technology' => 'Technology',
                        'Finance' => 'Finance',
                        'Healthcare' => 'Healthcare',
                        'Energy' => 'Energy',
                        'E-commerce' => 'E-commerce',
                    ])
                    ->required(),
                TextInput::make('logo_url')
                    ->url(),
                TextInput::make('current_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('opening_price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('previous_close')
                    ->numeric(),
                TextInput::make('day_high')
                    ->numeric(),
                TextInput::make('day_low')
                    ->numeric(),
                TextInput::make('price_change')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('price_change_percentage')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('minimum_investment')
                    ->required()
                    ->numeric()
                    ->default(1000.0),
                TextInput::make('maximum_investment')
                    ->numeric(),
            ]);
    }
}
