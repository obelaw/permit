<?php

namespace Obelaw\Permit\Filament\Resources\PermitRuleResource;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Obelaw\Permit\Filament\Resources\PermitRuleResource;

class ListRule extends ListRecords
{
    protected static string $resource = PermitRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
