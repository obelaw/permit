<?php

namespace Obelaw\Permit\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Obelaw\Permit\Models\PermitUser;

class RecentSuspensionsTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Suspensions')
            ->query(
                PermitUser::query()
                    ->with('authable')
                    ->whereNotNull('is_suspend')
                    ->latest('is_suspend')
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
                TextColumn::make('is_suspend')
                    ->label('Suspended At')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->limit(10))
            ->defaultSort('is_suspend', 'desc')
            ->paginated(false);
    }
}
