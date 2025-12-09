<?php

namespace App\Filament\Resources\LoanTypes;

use App\Filament\Resources\LoanTypes\Pages\ManageLoanTypes;
use App\Models\LoanType;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class LoanTypeResource extends Resource
{
    protected static ?string $model = LoanType::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Loan Management';
    protected static ?int $navigationSort = 1;
    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->columnSpanFull()
                    ->unique(ignoreRecord: true)
                    ->placeholder('eg : Personal Loan')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('min_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('max_amount')
                    ->required()
                    ->numeric(),

                TextInput::make('min_duration_months')
                    ->required()
                    ->numeric()
                    ->suffix('months')
                    ->minValue(1)
                    ->default(1),
                TextInput::make('max_duration_months')
                    ->required()
                    ->numeric()
                    ->maxValue(12)
                    ->suffix('months'),
                TextInput::make('interest_rate')
                    ->required()
                    ->numeric(),
                TextInput::make('requirements'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('min_amount')
                    ->numeric(),
                TextColumn::make('max_amount')
                    ->numeric(),
                TextColumn::make('interest_rate')
                    ->numeric()
                    ->formatStateUsing(fn($state) => number_format($state, 2) . '%'),
                TextColumn::make('min_duration_months')
                    ->label('Min Duration (Months)')
                    ->formatStateUsing(fn($state) => $state . ' months')
                    ->numeric(),
                TextColumn::make('max_duration_months')
                    ->label('Max Duration (Months)')
                    ->numeric(),
                ToggleColumn::make('is_active')
                    ->onIcon(Heroicon::CheckCircle)
                    ->offIcon(Heroicon::XCircle),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->button()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLoanTypes::route('/'),
        ];
    }
}
