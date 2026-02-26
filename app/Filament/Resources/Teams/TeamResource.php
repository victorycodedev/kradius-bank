<?php

namespace App\Filament\Resources\Teams;

use App\Filament\Resources\Teams\Pages\CreateTeam;
use App\Filament\Resources\Teams\Pages\EditTeam;
use App\Filament\Resources\Teams\Pages\ListTeams;
use App\Filament\Resources\Teams\Schemas\TeamForm;
use App\Filament\Resources\Teams\Tables\TeamsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TeamResource extends Resource
{
    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $model = User::class;
    // protected static bool $shouldRegisterNavigation = true;
    // Customize navigation and slug so Filament treats it separately
    protected static ?string $navigationLabel = 'Teams';
    protected static ?string $slug = 'teams'; // importa
    protected static ?int $navigationSort = 2;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return TeamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeamsTable::configure($table);
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
            'index' => ListTeams::route('/'),
            'create' => CreateTeam::route('/create'),
            'edit' => EditTeam::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutRole('User');
    }

    public static function canView($record): bool
    {
        return Auth::user()->can('view team members');
    }
    public static function canViewAny(): bool
    {
        return Auth::user()->can('view team members');
    }
    // can add staff
    public static function  canCreate(): bool
    {
        return Auth::user()->can('add new team member');
    }

    public static function  canEdit(Model $record): bool
    {
        return Auth::user()->can('update team member details');
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->can('delete team member');
    }
}
