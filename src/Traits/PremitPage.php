<?php

namespace Obelaw\Permit\Traits;

use Obelaw\Permit\Attributes\PagePermission;
use Obelaw\Permit\Facades\Permit;
use ReflectionClass;

trait PremitPage
{
    public static function canAccess(): bool
    {
        $reflection = new ReflectionClass(static::class);
        $reflectionPermissions = $reflection->getAttributes(PagePermission::class);

        return Permit::can($reflectionPermissions[0]->getArguments()['id']);
    }
}
