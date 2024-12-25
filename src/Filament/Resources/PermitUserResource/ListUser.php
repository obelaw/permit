<?php

namespace Obelaw\Permit\Filament\Resources\PermitUserResource;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Obelaw\Permit\Filament\Resources\PermitUserResource;

class ListUser extends ListRecords
{
    protected static string $resource = PermitUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
