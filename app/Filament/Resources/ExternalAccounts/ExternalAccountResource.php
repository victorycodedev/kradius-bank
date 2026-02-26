<?php

namespace App\Filament\Resources\ExternalAccounts;

use App\Filament\Resources\ExternalAccounts\Pages\ManageExternalAccounts;
use App\Models\ExternalAccount;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExternalAccountResource extends Resource
{
    protected static ?string $model = ExternalAccount::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string | \UnitEnum | null $navigationGroup = 'Account Management';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'account_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bank_id')
                    ->relationship('bank', 'name')
                    ->required(),
                TextInput::make('account_number')
                    ->maxLength(191)
                    ->required(),
                TextInput::make('account_name')
                    ->maxLength(191)
                    ->required(),
                TextInput::make('account_type')
                    ->maxLength(191),
                KeyValue::make('metadata')
                    ->keyPlaceholder('eg : IBAN')
                    ->valuePlaceholder('eg : 123456789')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('account_name')
            ->columns([
                TextColumn::make('bank.name')
                    ->searchable(),
                TextColumn::make('account_number')
                    ->searchable(),
                TextColumn::make('account_name')
                    ->searchable(),
                TextColumn::make('account_type')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => ManageExternalAccounts::route('/'),
        ];
    }
}
