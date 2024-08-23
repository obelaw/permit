<?php

namespace Obelaw\Permit;

use Filament\Panel;
use Obelaw\Permit\Filament\Pages\Auth\Login;
use Obelaw\Permit\Filament\Resources\AdminResource;
use Obelaw\Permit\Filament\Resources\RuleResource;
use Obelaw\Twist\Base\BaseAddon;

class PermitPlugin extends BaseAddon
{
    public function register(Panel $panel): void
    {
        $panel
            ->login(Login::class)
            ->authGuard('permit')
            ->resources([
                AdminResource::class,
                RuleResource::class,
            ]);
    }
}
