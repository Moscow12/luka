<?php

namespace App\Services\Acl;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * No-escalation rules for administering users, roles and permissions.
 *
 * A non-super-admin may only hand out what they already hold: a role is grantable (and
 * editable) only if every one of its permissions is one the actor has, and a user account
 * is manageable only if everything that user holds is grantable by the actor. Super-admin
 * accounts can only be managed by super-admins.
 */
class AccessControl
{
    public const MANAGE_USERS = 'manage-users';

    public const MANAGE_ROLES = 'manage-roles';

    public const MANAGE_PERMISSIONS = 'manage-permissions';

    /** @return Collection<int, string> */
    public static function permissionNames(User $user): Collection
    {
        return $user->getAllPermissions()->pluck('name')->unique()->values();
    }

    /** True when the actor holds every named permission. */
    public static function holdsAll(User $actor, iterable $permissionNames): bool
    {
        if ($actor->isSuperAdmin()) {
            return true;
        }

        return collect($permissionNames)->diff(self::permissionNames($actor))->isEmpty();
    }

    /** May grant this role to someone (and edit it): its permissions are a subset of the actor's. */
    public static function canGrantRole(User $actor, Role $role): bool
    {
        return self::holdsAll($actor, $role->permissions()->pluck('name'));
    }

    /** @return Collection<int, Role> */
    public static function grantableRoles(User $actor): Collection
    {
        $roles = Role::with('permissions:id,name')->orderBy('name')->get();

        if ($actor->isSuperAdmin()) {
            return $roles;
        }

        $held = self::permissionNames($actor);

        return $roles->filter(fn (Role $role) => $role->permissions->pluck('name')->diff($held)->isEmpty())->values();
    }

    /** May put these permissions (by id) into a role. */
    public static function canAssignPermissions(User $actor, array $permissionIds): bool
    {
        return self::holdsAll($actor, Permission::whereIn('id', $permissionIds)->pluck('name'));
    }

    /** May edit, reset, disable or re-enable this account. */
    public static function canManageUser(User $actor, User $target): bool
    {
        if ($actor->isSuperAdmin()) {
            return true;
        }

        if ($target->isSuperAdmin()) {
            return false;
        }

        // Everything the target can do (roles and direct permissions) must be within the actor's reach
        return self::holdsAll($actor, $target->getAllPermissions()->pluck('name'));
    }

    public static function holdsRole(User $actor, Role $role): bool
    {
        return $actor->roles()->whereKey($role->getKey())->exists();
    }
}
