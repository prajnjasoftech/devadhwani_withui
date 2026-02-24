<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member as MainMember;
use App\Models\Temple;
use App\Models\Tenant\Member as TenantMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MemberController extends Controller
{
    /**
     * List all members.
     * If `temple_id` present → use main DB.
     * If `temple` route param present → use tenant DB.
     */
    public function index(Request $request, $temple = null)
    {
        if ($temple) {
            // ✅ Tenant DB
            $members = TenantMember::all();

            return response()->json(['source' => 'tenant', 'data' => $members]);
        }
        // Pagination params
        $perPage = $request->per_page ?? 10; // default 10
        // ✅ Main DB
        //    $members = MainMember::where('temple_id', $request->temple_id)->get();
        $members = MainMember::where('temple_id', $request->temple_id)->paginate($perPage);

        return response()->json(['source' => 'main', 'data' => $members, 'meta' => [
            'current_page' => $members->currentPage(),
            'per_page' => $members->perPage(),
            'total' => $members->total(),
            'last_page' => $members->lastPage(),
        ]]);
    }

    /**
     * Create a member (works for both DBs).
     */
    public function store(Request $request, $temple = null)
    {

        if ($temple) {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'role' => 'nullable|string|max:255',
                'role_id' => 'nullable|integer',
            ]);

            // Tenant DB
            $member = TenantMember::create($data);

            return response()->json(['source' => 'tenant', 'message' => 'Member added (tenant)', 'member' => $member]);
        }

        $validator = Validator::make($request->all(), [
            'temple_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'role' => 'nullable|string|max:255',
            'role_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'error' => collect($validator->errors()->all())->implode(', '),
            ], 422);
        }
        $data = $request->validate([
            'temple_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'role' => 'nullable|string|max:255',
            'role_id' => 'nullable|integer',
        ]);
        // Ensure this phone exists in either temples or members, but not both
        $templeExists = Temple::where('phone', $request->phone)->exists();
        $memberExists = MainMember::where('phone', $request->phone)->exists();

        if ($templeExists || $memberExists) {
            return response()->json([
                'error' => 'Conflict: Phone number exists for both Temple and Member.',
            ], 409);
        }
        // Main DB
        $member = MainMember::create($data);

        return response()->json(['status' => true, 'message' => 'Supplier/Donor added successfully', 'data' => $member]);
    }

    /**
     * Show a single member.
     */
    public function show($id, Request $request, $temple = null)
    {
        if ($temple) {
            $member = TenantMember::find($id);
            if (! $member) {
                return response()->json(['message' => 'Tenant member not found'], 404);
            }

            return response()->json(['source' => 'tenant', 'data' => $member]);
        }

        $member = MainMember::find($id);
        if (! $member) {
            return response()->json(['message' => 'Main member not found'], 404);
        }

        return response()->json(['status' => true, 'message' => 'Supplier/Donor Listed successfully', 'data' => $member]);
    }

    /**
     * Update member.
     */
    public function update(Request $request, $id, $temple = null)
    {

        $validator = Validator::make($request->all(), [
            'temple_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'role' => 'nullable|string|max:255',
            'role_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'error' => collect($validator->errors()->all())->implode(', '),
            ], 422);
        }
        $data = $request->validate([
            'temple_id' => 'nullable|integer',
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'role' => 'nullable|string|max:255',
            'role_id' => 'nullable|integer',
        ]);
        // Ensure this phone exists in either temples or members, but not both
        if (isset($data['phone'])) {
            $memberExists = MainMember::where('phone', $data['phone'])->where('id', '!=', $id)->exists();

            if ($memberExists) {
                return response()->json([
                    'error' => 'Conflict: Phone number exists for both Temple and Member.',
                ], 409);
            }
        }
        $model = $temple ? TenantMember::find($id) : MainMember::find($id);

        if (! $model) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $model->update($data);

        return response()->json(['status' => true, 'message' => 'Member updated successfully', 'data' => $model]);
    }

    /**
     * Soft delete member.
     */
    public function destroy($id, $temple = null)
    {
        $model = $temple ? TenantMember::find($id) : MainMember::find($id);

        if (! $model) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $model->delete();

        return response()->json(['message' => 'Member deleted successfully']);
    }

    /**
     * Show trashed (deleted) members.
     */
    public function trashed($temple = null)
    {
        $members = $temple
             ? TenantMember::onlyTrashed()->get()
             : MainMember::onlyTrashed()->get();

        return response()->json(['source' => $temple ? 'tenant' : 'main', 'data' => $members]);
    }

    /**
     * Restore member.
     */
    public function restore($id, $temple = null)
    {
        $model = $temple
             ? TenantMember::withTrashed()->find($id)
             : MainMember::withTrashed()->find($id);

        if (! $model) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $model->restore();

        return response()->json(['message' => 'Member restored successfully']);
    }

    /**
     * Force delete.
     */
    public function forceDelete($id, $temple = null)
    {
        $model = $temple
             ? TenantMember::withTrashed()->find($id)
             : MainMember::withTrashed()->find($id);

        if (! $model) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $model->forceDelete();

        return response()->json(['message' => 'Member permanently deleted']);
    }
}
