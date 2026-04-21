<?php

namespace Obelaw\Permit\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Obelaw\Permit\Filament\Clusters\PermitCluster;
use Obelaw\Permit\Filament\Widgets\PermitRulesOverview;
use Obelaw\Permit\Filament\Widgets\PermitUsersOverview;
use Obelaw\Permit\Filament\Widgets\RulesUsersCountTable;
use Obelaw\Permit\Filament\Widgets\RecentUsersActivityTable;
use Obelaw\Permit\Filament\Widgets\RecentSuspensionsTable;

class PermitDashboard extends Page
{
    protected static ?string $cluster = PermitCluster::class;
    protected static ?string $slug = 'dashboard';
    protected static ?string $title = 'Permit Dashboard';
    protected ?string $heading = 'Permit Dashboard';
    protected ?string $subheading = 'Quick overview of users, status, and suspensions.';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?int $navigationSort = -1000;

    /**
     * @return array<class-string<Widget> | \Filament\Widgets\WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            PermitUsersOverview::class,
            PermitRulesOverview::class,
            RulesUsersCountTable::class,
            RecentUsersActivityTable::class,
            RecentSuspensionsTable::class,
        ];
    }

    /**
     * @return int | array<string, ?int>
     */
    public function getColumns(): int | array
    {
        return 1;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getWidgetsContentComponent(),
        ]);
    }

    public function getWidgetsContentComponent(): Component
    {
        return Grid::make($this->getColumns())
            ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getWidgets()));
    }
}
