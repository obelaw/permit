<?php

namespace Obelaw\Permit\Traits;

use Illuminate\Database\Eloquent\Model;
use Obelaw\Permit\Facades\Permit;

trait PremitCan
{
    public static function canViewAny(): bool
    {
        return Permit::can(static::$canAccess['can_viewAny']);
    }

    public static function canCreate(): bool
    {
        return Permit::can(static::$canAccess['can_create']);
    }

    public static function canEdit(Model $record): bool
    {
        return Permit::can(static::$canAccess['can_edit']);
    }

    public static function canDelete(Model $record): bool
    {
        return Permit::can(static::$canAccess['can_delete']);
    }
}
