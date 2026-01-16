<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;

trait HasRoles
{
    /**
     * Users can have many roles (in their tenant).
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Users can have direct permissions (overrides).
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Check if user has a specific permission.
     * Owner role bypasses this check.
     */
    public function hasPermissionTo(string $permission): bool
    {
        // 1. Check if user is an Owner (via Role name)
        // Note: For MVP we assume role name 'Owner' is immutable.
        if ($this->hasRole('Owner')) {
            return true;
        }

        // 2. Check direct permissions
        if ($this->permissions()->where('slug', $permission)->exists()) {
            return true;
        }

        // 3. Check via Roles
        foreach ($this->roles as $role) {
            if ($role->permissions()->where('slug', $permission)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    public function assignRole(Role $role): void
    {
        $this->roles()->syncWithoutDetaching($role);
    }

    /**
     * Get all unique permissions slug for the user.
     */
    public function getAllPermissionsAttribute(): array
    {
        // 1. If Owner, return all permissions (wildcard)
        if ($this->hasRole('Owner')) {
             return ['*'];
        }
        
        // 2. Direct permissions
        $permissions = $this->permissions->pluck('slug')->toArray();
        
        // 3. Role permissions
        foreach ($this->roles as $role) {
             $rolePerms = $role->permissions->pluck('slug')->toArray();
             $permissions = array_merge($permissions, $rolePerms);
        }
        
        return array_unique($permissions);
    }
}
