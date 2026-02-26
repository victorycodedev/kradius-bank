<?php

namespace App\Filament\Resources\AdNotifications;

use App\Filament\Resources\AdNotifications\Pages\ManageAdNotifications;
use App\Models\AdNotifcation;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class AdNotificationResource extends Resource
{
    protected static ?string $model = AdNotifcation::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Account Management';
    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?int $navigationSort = 6;
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('icon')
                    ->belowContent('Enter an Emoji Icon')
                    ->maxLength(2),
                TextInput::make('title')
                    ->required()
                    ->placeholder('eg : USD Account is live')
                    ->maxLength(24),
                TextInput::make('message')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(70),
                Toggle::make('is_active')
                    ->default(true)
                    ->onIcon(Heroicon::CheckCircle)
                    ->offIcon(Heroicon::XCircle),
                Toggle::make('redirect')
                    ->label('Open Link')
                    ->onIcon(Heroicon::CheckCircle)
                    ->offIcon(Heroicon::XCircle)
                    ->live(),
                TextInput::make('redirect_url')
                    ->columnSpanFull()
                    ->visible(fn(Get $get): bool => $get('redirect'))
                    ->required(fn(Get $get): bool => $get('redirect')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('icon'),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('message')
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->onIcon(Heroicon::CheckCircle)
                    ->offIcon(Heroicon::XCircle),
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
            ->reorderable('order')
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAdNotifications::route('/'),
        ];
    }
}
