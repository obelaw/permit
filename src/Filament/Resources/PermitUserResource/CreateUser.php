<?php

namespace Obelaw\Permit\Filament\Resources\PermitUserResource;

use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Obelaw\Permit\Filament\Resources\PermitUserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = PermitUserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    /**
     * Handle the record creation with proper transaction management
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Create the user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            // Create the permit relationship
            $permit = $user->permit()->create([
                'created_by' => auth()->id(),
                'rule_id' => $data['rule_id'],
                'can_create' => $data['can_create'] ?? false,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Log the creation
            Log::info('User created with permit', [
                'user_id' => $user->id,
                'permit_id' => $permit->id,
                'created_by' => auth()->id(),
            ]);

            return $permit; // Return the PermitUser model as that's what the resource expects
        });
    }

    /**
     * Handle creation failure
     */
    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->title('Validation Error')
            ->body('Please check the form for errors.')
            ->danger()
            ->send();

        parent::onValidationError($exception);
    }

    /**
     * Handle general exceptions during creation
     */
    protected function handleRecordCreationUsing(?callable $using = null): callable
    {
        return $using ?: function (array $data): Model {
            try {
                return $this->handleRecordCreation($data);
            } catch (\Throwable $e) {
                Log::error('Failed to create user with permit', [
                    'error' => $e->getMessage(),
                    'data' => $data,
                    'user_id' => auth()->id(),
                ]);

                Notification::make()
                    ->title('Creation Failed')
                    ->body('An error occurred while creating the user. Please try again.')
                    ->danger()
                    ->send();

                throw $e;
            }
        };
    }

    /**
     * Actions after successful creation
     */
    protected function afterCreate(): void
    {
        Notification::make()
            ->title('User Created Successfully')
            ->body('The user has been created with the appropriate permissions.')
            ->success()
            ->send();
    }

    /**
     * Get redirect URL after creation
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
