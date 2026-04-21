<?php

namespace Obelaw\Permit\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Obelaw\Permit\Models\PermitUser;

class RecentUsersActivityTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent User Activity')
            ->query(
                PermitUser::query()
                    ->with(['authable', 'rule'])
                    ->whereNotNull('last_active_at')
                    ->latest('last_active_at')
            )
            ->columns([
                TextColumn::make('authable.name')
                    ->label('Name')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('authable.email')
                    ->label('Email')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('rule.name')
                    ->label('Rule')
                    ->placeholder('-'),
                TextColumn::make('last_active_at')
                    ->label('Last Active')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->limit(10))
            ->defaultSort('last_active_at', 'desc')
            ->paginated(false);
    }
}
