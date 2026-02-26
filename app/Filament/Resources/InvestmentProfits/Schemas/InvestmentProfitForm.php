<?php

namespace App\Filament\Resources\InvestmentProfits\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InvestmentProfitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('investment_id')
                    ->relationship(name: 'investment', titleAttribute: 'id')
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->user->name} ({$record->reference_number})")
                    ->required(),
                TextInput::make('reference_number')
                    ->disabled(fn(string $operation): bool => $operation === 'edit')
                    ->suffixAction(
                        Action::make('generate_reference_number')
                            ->icon(Heroicon::ArrowPathRoundedSquare)
                            ->action(function (Set $set) {
                                $ref = 'INV' . strtoupper(Str::random(10));
                                $set('reference_number', 'INV-' . $ref);
                            }),
                    )
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Select::make('type')
                    ->options([
                        'roi' => 'Roi',
                        'dividend' => 'Dividend',
                        'capital_gain' => 'Capital gain',
                        'manual' => 'Manual',
                    ])
                    ->default('roi')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'paid' => 'Paid', 'cancelled' => 'Cancelled'])
                    ->default('pending')
                    ->required(),
            ]);
    }
}
