<?php

namespace Obelaw\Permit\Filament\Resources\PermitUserResource;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
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

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $this->applyColumnSearchesToTableQuery($query);

        if (!auth()->user()->rule->has_all_permissions)
            $query->where('created_by', auth()->user()->id);

        return $query;
    }
}
