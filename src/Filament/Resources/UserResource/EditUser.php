<?php

namespace Obelaw\Permit\Filament\Resources\UserResource;

use Filament\Resources\Pages\EditRecord;
use Obelaw\Permit\Filament\Resources\UserResource;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!$data['password']) {
            unset($data['password']);
        }

        return $data;
    }
}
