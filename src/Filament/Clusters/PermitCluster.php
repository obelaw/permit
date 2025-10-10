<?php

namespace Obelaw\Permit\Filament\Clusters;

use Filament\Clusters\Cluster;

class PermitCluster extends Cluster
{
    protected static ?int $navigationSort = 1000;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $navigationLabel = 'Permit';
}
