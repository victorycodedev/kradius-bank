<?php

namespace App\Filament\Resources\VerificationTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class VerificationTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->maxLength('191')
                    ->placeholder('eg : Iternational Monetary Fund (IMF)')
                    ->required(),
                TextInput::make('code')
                    ->maxLength('191')
                    ->placeholder('eg : IMF-Code')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->onIcon(Heroicon::CheckCircle)
                    ->offIcon(Heroicon::XCircle)
                    ->default(true)
                    ->required(),

                Toggle::make('is_required')
                    ->onIcon(Heroicon::CheckCircle)
                    ->offIcon(Heroicon::XCircle)
                    ->default(true)
                    ->required(),

                Select::make('applies_to')
                    ->options([
                        'all' => 'All',
                        'international' => 'International',
                        'local' => 'Local',
                        'above_threshold' => 'Above threshold',
                    ])
                    ->live()
                    ->default('all')
                    ->required(),

                TextInput::make('threshold_amount')
                    ->required(fn($get) => $get('applies_to') === 'above_threshold')
                    ->numeric(),
            ]);
    }
}
