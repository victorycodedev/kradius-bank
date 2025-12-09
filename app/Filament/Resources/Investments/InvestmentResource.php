<?php

namespace App\Filament\Resources\Investments;

use App\Filament\Resources\Investments\Pages\CreateInvestment;
use App\Filament\Resources\Investments\Pages\EditInvestment;
use App\Filament\Resources\Investments\Pages\ListInvestments;
use App\Filament\Resources\Investments\Pages\Profits;
use App\Filament\Resources\Investments\Pages\ViewInvestment;
use App\Filament\Resources\Investments\Schemas\InvestmentForm;
use App\Filament\Resources\Investments\Tables\InvestmentsTable;
use App\Models\Investment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvestmentResource extends Resource
{
    protected static ?string $model = Investment::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string | \UnitEnum | null $navigationGroup = 'Stock Trading';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'reference_number';

    public static function form(Schema $schema): Schema
    {
        return InvestmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvestmentsTable::configure($table);
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
            'index' => ListInvestments::route('/'),
            'create' => CreateInvestment::route('/create'),
            'edit' => EditInvestment::route('/{record}/edit'),
            'view' => ViewInvestment::route('/{record}'),
            'profits' => Profits::route('/{record}/profits'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
