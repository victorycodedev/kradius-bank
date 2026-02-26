<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\Notification as ModelsNotification;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('profile_photo_path')
                    ->label('Photo')
                    ->circular()
                    ->collection('avatars')
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),

                TextColumn::make('first_name')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('last_name')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                TextColumn::make('phone_number')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->toggleable(),

                TextColumn::make('kyc_status')
                    ->badge()
                    ->label('KYC')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'verified',
                        'danger' => 'rejected',
                    ])
                    ->icons([
                        'heroicon-m-clock' => 'pending',
                        'heroicon-m-check-circle' => 'verified',
                        'heroicon-m-x-circle' => 'rejected',
                    ]),

                TextColumn::make('account_status')
                    ->badge()
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'suspended',
                        'danger' => 'closed',
                    ]),

                // IconColumn::make('biometric_enabled')
                //     ->label('Biometric')
                //     ->boolean()
                //     ->toggleable(),

                IconColumn::make('two_factor_enabled')
                    ->label('2FA')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kyc_status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('account_status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'closed' => 'Closed',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    Action::make('notifications')
                        ->icon('heroicon-o-bell-alert')
                        ->url(fn(User $record): string => UserResource::getUrl('notifications', ['record' => $record]))
                        ->openUrlInNewTab(false),
                    Action::make('view_accounts')
                        ->icon(Heroicon::Banknotes)
                        ->color('info')
                        ->modalHeading(fn(User $record) => $record->name . "'s Accounts")
                        ->modalContent(fn(User $record) => view('filament.modals.user-accounts-livewire', [
                            'userId' => $record->id,
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalWidth(Width::Large)
                        ->slideOver()
                ])
                    ->button()

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('send_notification')
                        ->icon(Heroicon::BellAlert)
                        ->label('Send Notification')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('send_type')
                                        ->options([
                                            'in-app' => 'In-App Notification',
                                            'email' => 'Email Notification',
                                            'both' => 'Both in-app and email',
                                        ])
                                        ->default('in-app')
                                        ->live()
                                        ->selectablePlaceholder(false)
                                        ->required(),
                                    Select::make('type')
                                        ->options([
                                            'transaction' => 'Transaction',
                                            'security' => 'Security',
                                            'promotional' => 'Promotional',
                                            'system' => 'System',
                                            'verification' => 'Verification'
                                        ])
                                        ->required(),
                                    TextInput::make('title')
                                        ->columnSpanFull()
                                        ->maxLength(100)
                                        ->belowContent(function (Get $get) {
                                            if ($get('send_type') === 'in-app') {
                                                return '';
                                            }
                                            return 'This will be used as the subject of the email';
                                        })
                                        ->required(),
                                    Textarea::make('message')
                                        ->columnSpanFull()
                                        ->maxLength(function (Get $get) {
                                            if ($get('send_type') === 'in-app' || $get('send_type') === 'both') {
                                                return 100;
                                            }
                                            return 1000;
                                        })
                                        ->required(),
                                ])
                        ])
                        ->action(function (Collection $records, array $data) {

                            foreach ($records as $record) {

                                if ($data['send_type'] == 'both' || $data['send_type'] == 'in-app') {
                                    ModelsNotification::create([
                                        'user_id' => $record->id,
                                        'title' => $data['title'],
                                        'type' => $data['type'],
                                        'message' => $data['message'],
                                    ]);
                                }

                                // TODO: Send email notification
                            }


                            Notification::make()
                                ->title('Notification Sent')
                                ->body('Successfully sent notification to ' . $records->count() . ' users')
                                ->success()
                                ->send();
                        })
                        ->chunkSelectedRecords(250)
                        ->deselectRecordsAfterCompletion()
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
