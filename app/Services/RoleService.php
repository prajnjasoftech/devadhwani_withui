<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Role;

class RoleService
{
    /**
     * Available permissions - single source of truth
     */
    public const PERMISSIONS = [
        'dashboard' => 'Dashboard',
        'devotees' => 'Devotees Management',
        'members' => 'Members Management',
        'roles' => 'Roles Management',
        'poojas' => 'Poojas Management',
        'bookings' => 'Bookings Management',
        'categories' => 'Categories Management',
        'items' => 'Items Management',
        'suppliers' => 'Suppliers Management',
        'purchases' => 'Purchases Management',
        'donations' => 'Donations Management',
        'events' => 'Events Management',
        'reports' => 'Reports',
        'settings' => 'Settings',
    ];

    /**
     * Get available permissions
     */
    public function getAvailablePermissions(): array
    {
        return self::PERMISSIONS;
    }

    /**
     * Get permission keys
     */
    public function getPermissionKeys(): array
    {
        return array_keys(self::PERMISSIONS);
    }

    /**
     * Create a new role
     */
    public function create(array $data): Role
    {
        return Role::create($data);
    }

    /**
     * Update a role
     */
    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role->fresh();
    }

    /**
     * Get role by ID scoped to temple
     */
    public function findForTemple(int $id, int $templeId): ?Role
    {
        return Role::where('temple_id', $templeId)->find($id);
    }

    /**
     * Get role by ID (with trashed)
     */
    public function findWithTrashed(int $id): ?Role
    {
        return Role::withTrashed()->find($id);
    }

    /**
     * Check if role can be deleted (no members assigned)
     */
    public function canDelete(Role $role): bool
    {
        return Member::where('role_id', $role->id)->count() === 0;
    }

    /**
     * Get count of members assigned to role
     */
    public function getMembersCount(Role $role): int
    {
        return Member::where('role_id', $role->id)->count();
    }

    /**
     * Soft delete a role
     */
    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    /**
     * Restore a soft-deleted role
     */
    public function restore(Role $role): bool
    {
        return $role->restore();
    }

    /**
     * Permanently delete a role
     */
    public function forceDelete(Role $role): bool
    {
        return $role->forceDelete();
    }

    /**
     * Validate role permissions structure
     */
    public function validatePermissions(array $permissions): array
    {
        $errors = [];
        $validKeys = $this->getPermissionKeys();

        foreach ($permissions as $key => $value) {
            if (! in_array($key, $validKeys)) {
                $errors[] = "Invalid permission key: {$key}";
            }
        }

        return $errors;
    }
}
