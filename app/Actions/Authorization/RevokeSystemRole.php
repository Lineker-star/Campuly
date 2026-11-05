<?php

namespace App\Actions\Authorization;

use App\Enums\Role;
use App\Models\User;
use App\Services\Authorization\SystemPermissionScope;
use InvalidArgumentException;

class RevokeSystemRole
{
    public function __construct(private SystemPermissionScope $systemPermissionScope) {}

    /**
     * Take away a globally-scoped Spatie role without changing the active school.
     *
     * School roles are held in their own team, so this never touches the
     * access a person has inside a school.
     */
    public function revoke(User $user, Role $role): void
    {
        if (!$role->isSystemScoped()) {
            throw new InvalidArgumentException(__(':role is not a system-scoped role.', ['role' => $role->value]));
        }

        try {
            $this->systemPermissionScope->withinUserScope($user, fn () => $user->removeRole($role));
        } finally {
            $this->systemPermissionScope->forget($user);
        }
    }
}
