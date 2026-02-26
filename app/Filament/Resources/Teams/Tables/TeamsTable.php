<?php

namespace App\Filament\Resources\Teams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->listWithLineBreaks()
                    ->bulleted(),
                SelectColumn::make('account_status')
                    ->disabled(fn($record) => $record?->id == FacadesAuth::user()->id)
                    ->selectablePlaceholder(false)
                    ->options([
                        'active' => 'Active',
                        'blocked' => 'Blocked',
                    ]),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(FacadesAuth::user()->can('update team member details')),
                DeleteAction::make()
                    ->disabled(fn($record) => $record->id == FacadesAuth::user()->id)
                    ->visible(FacadesAuth::user()->can('delete team member')),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make()
                //         ->visible(FacadesAuth::user()->can('delete team member')),
                // ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
