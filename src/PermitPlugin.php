<?php

namespace Obelaw\Permit;

use Filament\Panel;
use Obelaw\Permit\Filament\Pages\Auth\Login;
use Obelaw\Twist\Base\BaseAddon;

class PermitPlugin extends BaseAddon
{
    public function register(Panel $panel): void
    {
        $panel
            ->login(Login::class)
            ->authGuard('permit')
            ->discoverResources(
                in: __DIR__ . DIRECTORY_SEPARATOR . 'Filament' . DIRECTORY_SEPARATOR . 'Resources',
                for: 'Obelaw\\Permit\\Filament\\Resources'
            )
            ->discoverClusters(
                in: __DIR__ . DIRECTORY_SEPARATOR . 'Filament' . DIRECTORY_SEPARATOR . 'Clusters',
                for: 'Obelaw\\Permit\\Filament\\Clusters'
            );
    }
}
