<?php

namespace Obelaw\Permit\Filament\Resources\PermitUserResource;

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

        if (!auth()->user()->rule->has_all_permissions)
            $data['can_create'] = false;

        return $data;
    }
}
