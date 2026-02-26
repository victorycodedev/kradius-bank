<?php

namespace App\Filament\Resources\InvestmentProfits;

use App\Filament\Resources\InvestmentProfits\Pages\CreateInvestmentProfit;
use App\Filament\Resources\InvestmentProfits\Pages\EditInvestmentProfit;
use App\Filament\Resources\InvestmentProfits\Pages\ListInvestmentProfits;
use App\Filament\Resources\InvestmentProfits\Schemas\InvestmentProfitForm;
use App\Filament\Resources\InvestmentProfits\Tables\InvestmentProfitsTable;
use App\Models\InvestmentProfit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InvestmentProfitResource extends Resource
{
    protected static ?string $model = InvestmentProfit::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string | \UnitEnum | null $navigationGroup = 'Stock Trading';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return InvestmentProfitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvestmentProfitsTable::configure($table);
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
            'index' => ListInvestmentProfits::route('/'),
            'create' => CreateInvestmentProfit::route('/create'),
            'edit' => EditInvestmentProfit::route('/{record}/edit'),
        ];
    }

    // public static function canEdit(Model $record): bool
    // {
    //     return true;
    // }
}
