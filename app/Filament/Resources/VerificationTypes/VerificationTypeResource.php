<?php

namespace App\Filament\Resources\VerificationTypes;

use App\Filament\Resources\VerificationTypes\Pages\CreateVerificationType;
use App\Filament\Resources\VerificationTypes\Pages\EditVerificationType;
use App\Filament\Resources\VerificationTypes\Pages\ListVerificationTypes;
use App\Filament\Resources\VerificationTypes\Schemas\VerificationTypeForm;
use App\Filament\Resources\VerificationTypes\Tables\VerificationTypesTable;
use App\Models\VerificationType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VerificationTypeResource extends Resource
{
    protected static ?string $model = VerificationType::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string | \UnitEnum | null $navigationGroup = 'Account Management';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return VerificationTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VerificationTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVerificationTypes::route('/'),
            'create' => CreateVerificationType::route('/create'),
            'edit' => EditVerificationType::route('/{record}/edit'),
        ];
    }
}
