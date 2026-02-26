<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->autofocus()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->columnSpanFull()
                    ->disabled(fn(?Role $record) => $record?->name === 'Super Admin')
                    ->label('Name'),

                CheckboxList::make('permissions')
                    ->relationship(name: 'permissions', titleAttribute: 'name')
                    ->disabled(fn(?Role $record) => $record?->name === 'Super Admin')
                    ->columnSpanFull()
                    ->label('Permissions')
                    ->columns(3)
                    ->searchable()
                    ->bulkToggleable()
                    ->descriptions(
                        Permission::all()->mapWithKeys(function ($permission) {
                            return [
                                $permission->id => match ($permission->name) {
                                    'create event' => 'The ability to create and add new event',
                                    default => $permission->name,
                                },
                            ];
                        })
                    )
                    ->saveRelationshipsUsing(function (CheckboxList $component, $state) {
                        $record = $component->getRecord();
                        if ($record instanceof Role) {
                            $record->permissions()->sync($state ?? []);
                        }
                    })
                    ->dehydrated(fn(string $operation) => $operation === 'edit'),
            ]);
    }
}
