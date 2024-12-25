<?php

namespace Obelaw\Permit\Filament\Resources\PermitUserResource;

use Filament\Resources\Pages\EditRecord;
use Obelaw\Permit\Filament\Resources\PermitUserResource;

class EditUser extends EditRecord
{
    protected static string $resource = PermitUserResource::class;

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
