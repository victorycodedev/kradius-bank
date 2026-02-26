<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Traits\CreatesUserAccount;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    use CreatesUserAccount;

    protected static string $resource = UserResource::class;
    protected static bool $canCreateAnother = false;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'User created successfully';
    }

    /**
     * Handle the record creation and assign 'User' role
     */
    protected function handleRecordCreation(array $data): Model
    {
        $user = static::getModel()::create($data);

        // Assign the 'User' role to the newly created user
        $user->assignRole('User');

        // Use the trait method - customize as needed
        $this->createDefaultAccount($user, [
            'currency' => 'USD',
            'account_tier' => 'basic',
        ]);

        return $user;
    }


    /**
     * Mutate form data before filling the form
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values if needed
        $data['login_attempts'] = 0;
        $data['kyc_status'] = $data['kyc_status'] ?? 'pending';
        $data['account_status'] = $data['account_status'] ?? 'active';
        $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
        return $data;
    }
}
