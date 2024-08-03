<?php

namespace Obelaw\Permit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getPaths()
 * @method static void addPath(string $path)
 */
class Permit extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'obelaw.permit';
    }
}
