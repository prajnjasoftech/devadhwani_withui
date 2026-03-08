<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TempleDeity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TempleDeityController extends Controller
{
    /**
     * List all temple deities.
     */
    public function index(Request $request)
    {
        $query = TempleDeity::query();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        if ($request->has('with_trashed') && $request->with_trashed == true) {
            $query->withTrashed();
        }

        // Pagination params
        $perPage = $request->per_page ?? 10;
        $deities = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $deities,
            'meta' => [
                'current_page' => $deities->currentPage(),
                'per_page' => $deities->perPage(),
                'total' => $deities->total(),
                'last_page' => $deities->lastPage(),
            ],
        ]);
    }

    /**
     * Create a new temple deity.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'temple_id' => 'required|integer|exists:temples,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'error' => collect($validator->errors()->all())->implode(', '),
            ], 422);
        }

        $data = $request->all();
        $data['is_active'] = $data['is_active'] ?? true;

        $deity = TempleDeity::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Temple Deity created successfully.',
            'data' => $deity,
        ], 201);
    }

    /**
     * Show a specific deity.
     */
    public function show($id)
    {
        $deity = TempleDeity::find($id);

        if (! $deity) {
            return response()->json([
                'status' => false,
                'message' => 'Temple Deity not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $deity,
        ]);
    }

    /**
     * Update a deity.
     */
    public function update(Request $request, $id)
    {
        $deity = TempleDeity::find($id);

        if (! $deity) {
            return response()->json([
                'status' => false,
                'message' => 'Temple Deity not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'temple_id' => 'sometimes|integer|exists:temples,id',
            'name' => 'sometimes|string|max:150',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'error' => collect($validator->errors()->all())->implode(', '),
            ], 422);
        }

        $deity->update($request->all());
        $deity->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Temple Deity updated successfully.',
            'data' => $deity,
        ]);
    }

    /**
     * Soft delete.
     */
    public function destroy($id)
    {
        $deity = TempleDeity::find($id);

        if (! $deity) {
            return response()->json([
                'status' => false,
                'message' => 'Temple Deity not found.',
            ], 404);
        }

        $deity->delete();

        return response()->json([
            'status' => true,
            'message' => 'Temple Deity soft deleted successfully.',
        ]);
    }

    /**
     * Restore.
     */
    public function restore($id)
    {
        $deity = TempleDeity::withTrashed()->find($id);

        if (! $deity) {
            return response()->json([
                'status' => false,
                'message' => 'Temple Deity not found.',
            ], 404);
        }

        $deity->restore();

        return response()->json([
            'status' => true,
            'message' => 'Temple Deity restored successfully.',
            'data' => $deity,
        ]);
    }

    /**
     * Force delete.
     */
    public function forceDelete($id)
    {
        $deity = TempleDeity::withTrashed()->find($id);

        if (! $deity) {
            return response()->json([
                'status' => false,
                'message' => 'Temple Deity not found.',
            ], 404);
        }

        $deity->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Temple Deity permanently deleted.',
        ]);
    }

    /**
     * Get trashed (soft deleted) records.
     */
    public function trashed(Request $request)
    {
        $query = TempleDeity::onlyTrashed();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        $deities = $query->get();

        return response()->json(['status' => true, 'data' => $deities]);
    }
}
