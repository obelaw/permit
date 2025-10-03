<?php

namespace Obelaw\Permit\Filament\Resources\PermitUserResource;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
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
        $data['created_by'] = auth()->user()->id;

        if (!Filament::auth()->user()->authable->rule->has_all_permissions)
            $data['can_create'] = false;

        return $data;
    }

    protected function afterCreate()
    {
        $data = $this->form->getState();

        $this->record->authable()->create([
            'created_by' => auth()->user()->id,
            'rule_id' => $data['rule_id'],
            'can_create' => $data['can_create'],
            'is_active' => $data['is_active'],
        ]);
    }
}
