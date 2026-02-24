<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Temple;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * List all roles (optional: filter by temple)
     */
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 10; // default 10
        if ($request->has('temple_id')) {
            $roles = Role::where('temple_id', $request->temple_id)->paginate($perPage);
        } else {
            $roles = Role::paginate($perPage);
        }
        // Pagination params

        // $role = $query;
        return response()->json(['status' => true, 'data' => $roles, 'meta' => [
            'current_page' => $roles->currentPage(),
            'per_page' => $roles->perPage(),
            'total' => $roles->total(),
            'last_page' => $roles->lastPage(),
        ]]);
    }

    /**
     * Create a new role
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'role_name' => 'nullable|string|max:255',
            'role' => 'required|array',
        ]);

        $role = Role::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Role created successfully',
            'data' => $role,
        ], 201);
    }

    /**
     * Show a single role
     */
    public function show($id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        return response()->json($role);
    }

    /**
     * Update a role
     */
    public function update(Request $request, $id)
    {

        // ✅ Validate basic input
        $data = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'role_name' => 'nullable|string|max:255',
            'role' => 'required|array',
        ]);
        $role = Role::find($id);
        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        // ✅ Verify temple ownership
        if ($role->temple_id != $data['temple_id']) {
            return response()->json([
                'message' => 'Unauthorized temple access.',
                'expected_temple_id' => $role->temple_id,
                'provided_temple_id' => $data['temple_id'],
            ], 403);
        }

        // ✅ Define allowed structure
        $requiredKeys = ['User', 'Role', 'Pooja', 'Settings', 'Events', 'Donations', 'Members'];

        // ✅ Check all required keys exist
        $missingKeys = array_diff($requiredKeys, array_keys($data['role']));
        if (! empty($missingKeys)) {
            return response()->json([
                'message' => 'Invalid role structure.',
                'missing_fields' => array_values($missingKeys),
            ], 422);
        }

        // ✅ Validate value types
        foreach ($data['role'] as $key => $value) {
            if (! is_numeric($value)) {
                return response()->json([
                    'message' => "Invalid value for '$key'. Expected numeric value.",
                    'invalid_field' => $key,
                    'invalid_value' => $value,
                ], 422);
            }
        }
        // ✅ Passed validation → Update role
        $role->update([
            'role_name' => $data['role_name'] ?? $role->role_name,
            'role' => $data['role'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Role updated successfully',
            'data' => $role,
        ]);
    }

    /**
     * Soft delete
     */
    public function destroy($id)
    {
        $role = Role::find($id);
        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully']);
    }

    /**
     * Trashed roles
     */
    public function trashed(Request $request)
    {
        $query = Role::onlyTrashed();
        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }
        // Pagination params
        $perPage = $request->per_page ?? 10; // default 10
        $role = $query->paginate($perPage);

        return response()->json(['status' => true, 'data' => $role, 'meta' => [
            'current_page' => $role->currentPage(),
            'per_page' => $role->perPage(),
            'total' => $role->total(),
            'last_page' => $role->lastPage(),
        ]]);
        // return response()->json($query->get());
    }

    /**
     * Restore soft-deleted role
     */
    public function restore($id)
    {
        $role = Role::withTrashed()->find($id);
        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $role->restore();

        return response()->json(['message' => 'Role restored successfully']);
    }

    /**
     * Permanently delete a role
     */
    public function forceDelete($id)
    {
        $role = Role::withTrashed()->find($id);
        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $role->forceDelete();

        return response()->json(['message' => 'Role permanently deleted']);
    }
}
