<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Notification;
use Dom\Text;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as NotificationsNotification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notifications extends Page implements HasTable, HasSchemas
{
    use InteractsWithRecord;
    use InteractsWithTable;
    use InteractsWithSchemas;

    protected static string $resource = UserResource::class;

    protected string $view = 'filament.resources.users.pages.notifications';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_notification')
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
                ->action(function (array $data) {

                    if ($data['send_type'] == 'both' || $data['send_type'] == 'in-app') {
                        Notification::create([
                            'user_id' => $this->record->id,
                            'title' => $data['title'],
                            'type' => $data['type'],
                            'message' => $data['message'],
                        ]);
                    }

                    // TODO: Send email notification


                    NotificationsNotification::make()
                        ->title('Notification Sent')
                        ->body('Successfully sent notification to ' . $this->record->name)
                        ->success()
                        ->send();
                }),
            Action::make('back')
                ->icon(Heroicon::ChevronLeft)
                ->color('gray')
                ->url(UserResource::getUrl('index')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->relationship(fn(): HasMany => $this->record->notifications())
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('type')
                    ->badge(),
                IconColumn::make('is_read')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'transaction' => 'Transaction',
                        'security' => 'Security',
                        'promotional' => 'Promotional',
                        'system' => 'System',
                        'verification' => 'Verification'
                    ]),
                // is read
                TernaryFilter::make('is_read')
                    ->trueLabel('Read')
                    ->falseLabel('Unread'),

            ])
            ->recordActions([
                Action::make('delete')
                    ->icon(Heroicon::Trash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Notification')
                    // ->modalContent('Are you sure you want to delete this notification?')
                    ->action(function (Model $record): void {
                        $record->delete();

                        NotificationsNotification::make()
                            ->title('Notification Deleted')
                            ->body('Successfully deleted notification')
                            ->success()
                            ->send();
                    })
            ])
            ->toolbarActions([
                // ...
            ])
            ->defaultSort('created_at', 'desc');
    }
}
