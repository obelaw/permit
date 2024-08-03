<?php

namespace Obelaw\Permit\Filament\Resources\RuleResource;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Obelaw\Permit\Filament\Resources\RuleResource;

class ListRule extends ListRecords
{
    protected static string $resource = RuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
