<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * List all roles
     */
    public function index(Request $request)
    {
        $perPage = min($request->integer('per_page', 10), 50);

        $query = Role::withCount('members');

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        if ($request->filled('search')) {
            $query->where('role_name', 'like', "%{$request->search}%");
        }

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $roles = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $roles->items(),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
                'last_page' => $roles->lastPage(),
            ],
        ]);
    }

    /**
     * Get available permissions
     */
    public function permissions()
    {
        return response()->json([
            'status' => true,
            'data' => $this->roleService->getAvailablePermissions(),
        ]);
    }

    /**
     * Create a new role
     */
    public function store(StoreRoleRequest $request)
    {
        $data = $request->validated();

        // Validate permissions if provided
        if (! empty($data['role'])) {
            $errors = $this->roleService->validatePermissions($data['role']);
            if (! empty($errors)) {
                return response()->json([
                    'status' => false,
                    'error' => implode(', ', $errors),
                ], 422);
            }
        }

        $role = $this->roleService->create($data);

        return response()->json([
            'status' => true,
            'message' => 'Role created successfully.',
            'data' => $role,
        ], 201);
    }

    /**
     * Show a single role
     */
    public function show($id)
    {
        $role = Role::withCount('members')->find($id);

        if (! $role) {
            return response()->json([
                'status' => false,
                'message' => 'Role not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $role,
        ]);
    }

    /**
     * Update a role
     */
    public function update(UpdateRoleRequest $request, $id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json([
                'status' => false,
                'message' => 'Role not found.',
            ], 404);
        }

        $data = $request->validated();

        // Validate permissions if provided
        if (! empty($data['role'])) {
            $errors = $this->roleService->validatePermissions($data['role']);
            if (! empty($errors)) {
                return response()->json([
                    'status' => false,
                    'error' => implode(', ', $errors),
                ], 422);
            }
        }

        $role = $this->roleService->update($role, $data);

        return response()->json([
            'status' => true,
            'message' => 'Role updated successfully.',
            'data' => $role,
        ]);
    }

    /**
     * Soft delete role
     */
    public function destroy($id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json([
                'status' => false,
                'message' => 'Role not found.',
            ], 404);
        }

        if (! $this->roleService->canDelete($role)) {
            $count = $this->roleService->getMembersCount($role);

            return response()->json([
                'status' => false,
                'message' => "Cannot delete role. It is assigned to {$count} member(s).",
            ], 400);
        }

        $this->roleService->delete($role);

        return response()->json([
            'status' => true,
            'message' => 'Role deleted successfully.',
        ]);
    }

    /**
     * Show trashed roles
     */
    public function trashed(Request $request)
    {
        $perPage = min($request->integer('per_page', 10), 50);

        $query = Role::onlyTrashed();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        $roles = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $roles->items(),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
                'last_page' => $roles->lastPage(),
            ],
        ]);
    }

    /**
     * Restore soft-deleted role
     */
    public function restore($id)
    {
        $role = $this->roleService->findWithTrashed($id);

        if (! $role) {
            return response()->json([
                'status' => false,
                'message' => 'Role not found.',
            ], 404);
        }

        if (! $role->trashed()) {
            return response()->json([
                'status' => false,
                'message' => 'Role is not deleted.',
            ], 400);
        }

        $this->roleService->restore($role);

        return response()->json([
            'status' => true,
            'message' => 'Role restored successfully.',
        ]);
    }

    /**
     * Permanently delete a role
     */
    public function forceDelete($id)
    {
        $role = $this->roleService->findWithTrashed($id);

        if (! $role) {
            return response()->json([
                'status' => false,
                'message' => 'Role not found.',
            ], 404);
        }

        $this->roleService->forceDelete($role);

        return response()->json([
            'status' => true,
            'message' => 'Role permanently deleted.',
        ]);
    }
}
