<?php

namespace Obelaw\Permit\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Obelaw\Permit\Models\PermitUser;

class PermitUsersOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'User Overview';

    protected function getStats(): array
    {
        $baseQuery = PermitUser::query();

        return [
            Stat::make('Total users', (string) ((clone $baseQuery)->count()))
                ->color('gray')
                ->icon('heroicon-o-users'),
            Stat::make('Active users', (string) ((clone $baseQuery)->where('is_active', true)->count()))
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            Stat::make('Inactive users', (string) ((clone $baseQuery)->where('is_active', false)->count()))
                ->color('warning')
                ->icon('heroicon-o-pause-circle'),
            Stat::make('Suspended users', (string) ((clone $baseQuery)->whereNotNull('is_suspend')->count()))
                ->color('danger')
                ->icon('heroicon-o-no-symbol'),
        ];
    }
}
