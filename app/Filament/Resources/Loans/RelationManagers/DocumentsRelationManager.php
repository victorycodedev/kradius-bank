<?php

namespace App\Filament\Resources\Loans\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('document_type')
                    ->options([
                        'Valid ID' => 'Valid ID',
                        'Proof of Income' => 'Proof of Income',
                        'Bank Statement' => 'Bank Statement',
                        'Employment Letter' => 'Employment Letter',
                        'Business Registration' => 'Business Registration',
                        'Tax Returns' => 'Tax Returns',
                        'Utility Bill' => 'Utility Bill',
                        'Other' => 'Other',
                    ])
                    ->columnSpanFull()
                    ->required()
                    ->searchable(),
                FileUpload::make('document_path')
                    ->label('Document')
                    ->required()
                    ->disk('public')
                    ->directory('loan-documents')
                    ->maxSize(5120) // 5MB
                    ->acceptedFileTypes([
                        'application/pdf',
                        'image/jpeg',
                        'image/png',
                        'image/jpg',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ])
                    ->downloadable()
                    ->openable()
                    ->previewable()
                    ->helperText('Max size: 5MB. Formats: PDF, JPG, PNG, DOC, DOCX')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_name')
            ->columns([
                TextColumn::make('document_type')
                    ->label('Type')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('original_name')
                    ->label('File Name')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->original_name),

                TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn($state) => $state ? number_format($state / 1024, 2) . ' KB' : '-'),

                IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean(),

                TextColumn::make('verifiedBy.name')
                    ->label('Verified By')
                    ->placeholder('-'),

                TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime('d M, Y H:i')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('d M, Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_verified')
                    ->label('Verification Status')
                    ->boolean()
                    ->trueLabel('Verified Only')
                    ->falseLabel('Unverified Only')
                    ->native(false),

                SelectFilter::make('document_type')
                    ->options([
                        'Valid ID' => 'Valid ID',
                        'Proof of Income' => 'Proof of Income',
                        'Bank Statement' => 'Bank Statement',
                        'Employment Letter' => 'Employment Letter',
                        'Business Registration' => 'Business Registration',
                        'Tax Returns' => 'Tax Returns',
                        'Utility Bill' => 'Utility Bill',
                        'Other' => 'Other',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon(Heroicon::PlayCircle)
                    ->label('Upload Document')
                    ->mutateDataUsing(function (array $data): array {

                        if (! empty($data['document_path'])) {

                            $filePath = $data['document_path']; // THIS IS A STRING

                            // ✅ Get original file name safely
                            $data['original_name'] = basename($filePath);

                            // ✅ Get file size safely
                            if (Storage::disk('public')->exists($filePath)) {
                                $data['file_size'] = Storage::disk('public')->size($filePath);
                                $data['mime_type'] = Storage::disk('public')->mimeType($filePath);
                            } else {
                                $data['file_size'] = null;
                                $data['mime_type'] = null;
                            }
                        }

                        return $data;
                    }),
                // AssociateAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->url(fn($record) => Storage::disk('public')->url($record->document_path))
                        ->openUrlInNewTab()
                        ->visible(fn($record) => in_array($record->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])),
                    EditAction::make(),

                    Action::make('verify')
                        // ->visible(fn(string $operation) => $operation === 'edit')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn($record) => !$record->is_verified)
                        ->requiresConfirmation()
                        ->modalHeading('Verify Document')
                        ->modalDescription('Are you sure you want to verify this document?')
                        ->action(function ($record) {
                            $record->update([
                                'is_verified' => true,
                                'verified_by' =>  Auth::user()->id,
                                'verified_at' => now(),
                            ]);

                            // Log activity
                            $record->loan->logActivity(
                                'document_verified',
                                Auth::user()->name . ' verified ' . $record->document_type,
                                Auth::user()->id,
                            );

                            Notification::make()
                                ->success()
                                ->title('Document Verified')
                                ->send();
                        }),

                    Action::make('unverify')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        // ->visible(fn(string $operation, $record) => $operation === 'edit' && $record->is_verified)
                        ->visible(fn($record) => $record->is_verified)
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update([
                                'is_verified' => false,
                                'verified_by' => null,
                                'verified_at' => null,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Verification Removed')
                                ->send();
                        }),
                    // DissociateAction::make(),

                    DeleteAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
