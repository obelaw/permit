<?php

namespace Obelaw\Permit\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Obelaw\Permit\Models\PermitRule;
use Obelaw\Permit\Models\PermitUser;

class PermitRulesOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Rules Overview';

    protected function getStats(): array
    {
        $rulesQuery = PermitRule::query();

        return [
            Stat::make('Total rules', (string) ((clone $rulesQuery)->count()))
                ->color('gray')
                ->icon('heroicon-o-shield-check'),
            Stat::make('Full-access rules', (string) ((clone $rulesQuery)->where('has_all_permissions', true)->count()))
                ->color('success')
                ->icon('heroicon-o-lock-open'),
            Stat::make('Restricted rules', (string) ((clone $rulesQuery)->where('has_all_permissions', false)->count()))
                ->color('warning')
                ->icon('heroicon-o-lock-closed'),
            Stat::make('Creators enabled', (string) PermitUser::query()->where('can_create', true)->count())
                ->color('info')
                ->icon('heroicon-o-user-plus'),
        ];
    }
}
