<?php

namespace App\Filament\Resources\Teams\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('phone_number')
                    ->tel()
                    ->maxLength(255),
                Select::make('roles')
                    ->label('Roles')
                    ->multiple()
                    ->preload()
                    ->disabled(fn($record) => $record?->id == Auth::user()->id)
                    ->relationship(name: 'roles', titleAttribute: 'name', modifyQueryUsing: fn($query) => $query->where('name', '!=', 'User'))
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                    ->dehydrated(fn(?string $state): bool => filled($state)),
                TextInput::make('password_confirmation')
                    ->password()
                    ->dehydrated(false)
                    ->revealable()
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->same('password'),
            ]);
    }
}
