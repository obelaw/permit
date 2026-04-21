<?php

namespace Obelaw\Permit\Filament\Widgets;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Obelaw\Permit\Models\PermitRule;

class RulesUsersCountTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Rules and Users Count')
            ->query(PermitRule::query()->withCount('users'))
            ->columns([
                TextColumn::make('name')
                    ->label('Rule')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('Users')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('has_all_permissions')
                    ->label('Full Access')
                    ->boolean(),
            ])
            ->defaultSort('users_count', 'desc');
    }
}
