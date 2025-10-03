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
        $data['rule_id'] = $this->record->authable->rule_id;
        $data['can_create'] = $this->record->authable->can_create;
        $data['is_active'] = $this->record->authable->is_active;

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();

        $this->record->authable->update([
            'rule_id' => $data['rule_id'],
            'can_create' => $data['can_create'],
            'is_active' => $data['is_active'],
        ]);
    }
}
