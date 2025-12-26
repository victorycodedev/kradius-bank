<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['name'] = $data['first_name'] . ' ' . $data['last_name'];

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->icon(Heroicon::Trash),
            Action::make('reset_login_attempts')
                ->icon(Heroicon::ArrowPath)
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (Model $record) {
                    $record->update([
                        'login_attempts' => 0,
                        'locked_until' => null,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Login attempts reset successfully')
                        ->send();
                }),

            Action::make('reset_pin')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->schema([
                    TextInput::make('new_pin')
                        ->label('New Transaction PIN')
                        ->password()
                        ->numeric()
                        ->length(4)
                        ->required()
                        ->placeholder('4-digit PIN'),
                ])
                ->action(function (Model $record, array $data) {
                    $record->update([
                        'pin' => Hash::make($data['new_pin']),
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Transaction PIN reset successfully')
                        ->send();
                }),

            Action::make('verify_kyc')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn() => $this->record->kyc_status !== 'verified')
                ->action(function (Model $record) {
                    $record->update([
                        'kyc_status' => 'verified',
                    ]);

                    Notification::make()
                        ->success()
                        ->title('KYC verified successfully')
                        ->send();
                }),

            Action::make('send_email')
                ->icon(Heroicon::Envelope)
                ->color('secondary')
                ->schema([
                    TextInput::make('subject')
                        ->label('Subject')
                        ->required(),

                    RichEditor::make('message')
                        ->label('Message')
                        ->required(),
                ])
                ->action(function (Model $record, array $data) {
                    try {

                        Mail::to($record->email)->send(new \App\Mail\SendEmailNotification($data['subject'], $data['message']));

                        Notification::make()
                            ->success()
                            ->title('Email sent successfully')
                            ->send();
                    } catch (\Throwable $th) {
                        Notification::make()
                            ->danger()
                            ->title($th->getMessage() ?? 'Failed to send email')
                            ->send();
                    }
                }),
        ];
    }
}
