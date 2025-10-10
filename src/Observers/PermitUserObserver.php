<?php

namespace Obelaw\Permit\Observers;

use Obelaw\Permit\Events\UserDeletedEvent;
use Obelaw\Permit\Models\PermitUser;

class PermitUserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(PermitUser $user): void
    {
        // ...
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(PermitUser $user): void
    {
        // ...
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(PermitUser $user): void
    {
        event(new UserDeletedEvent(
            $user->authable,
        ));
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(PermitUser $user): void
    {
        // ...
    }

    /**
     * Handle the User "forceDeleted" event.
     */
    public function forceDeleted(PermitUser $user): void
    {
        // ...
    }
}
