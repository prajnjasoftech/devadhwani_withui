<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Role;

class MemberService
{
    /**
     * Create a new member with role name sync
     */
    public function create(array $data): Member
    {
        $data = $this->syncRoleName($data);

        return Member::create($data);
    }

    /**
     * Update a member with role name sync
     */
    public function update(Member $member, array $data): Member
    {
        $data = $this->syncRoleName($data);
        $member->update($data);

        return $member->fresh();
    }

    /**
     * Sync role_name from role_id
     */
    protected function syncRoleName(array $data): array
    {
        if (! empty($data['role_id'])) {
            $role = Role::find($data['role_id']);
            $data['role'] = $role?->role_name;
        } else {
            $data['role'] = null;
        }

        return $data;
    }

    /**
     * Get member by ID scoped to temple
     */
    public function findForTemple(int $id, int $templeId): ?Member
    {
        return Member::where('temple_id', $templeId)->find($id);
    }

    /**
     * Get member by ID (with trashed)
     */
    public function findWithTrashed(int $id): ?Member
    {
        return Member::withTrashed()->find($id);
    }

    /**
     * Soft delete a member
     */
    public function delete(Member $member): bool
    {
        return $member->delete();
    }

    /**
     * Restore a soft-deleted member
     */
    public function restore(Member $member): bool
    {
        return $member->restore();
    }

    /**
     * Permanently delete a member
     */
    public function forceDelete(Member $member): bool
    {
        return $member->forceDelete();
    }

    /**
     * Check if phone exists for temple (excluding current member)
     */
    public function phoneExistsForTemple(string $phone, int $templeId, ?int $excludeId = null): bool
    {
        $query = Member::where('phone', $phone)->where('temple_id', $templeId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
