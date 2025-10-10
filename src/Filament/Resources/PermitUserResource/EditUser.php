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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['name'] = $this->record->authable->name;
        $data['email'] = $this->record->authable->email;

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();

        $this->record->authable->name = $data['name'];
        $this->record->authable->email = $data['email'];

        if (isset($data['password']))
            $this->record->authable->password = $data['password'];

        $this->record->authable->save();
    }
}
