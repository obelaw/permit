<?php

namespace Obelaw\Permit\Filament\Resources\AdminResource;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Obelaw\Permit\Filament\Resources\AdminResource;

class ListAdmin extends ListRecords
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
