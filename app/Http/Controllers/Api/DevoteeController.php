<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDevoteeRequest;
use App\Http\Requests\UpdateDevoteeRequest;
use App\Models\Devotee;
use Illuminate\Http\Request;

class DevoteeController extends Controller
{
    /**
     * Display a listing of the devotees.
     */
    public function index(Request $request)
    {
        $query = Devotee::query();

        // Filter by temple_id first (uses index, significantly improves performance)
        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        // Show trashed if requested (apply before search for index usage)
        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        // Optimized search - apply after temple_id filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            if (strlen($search) >= 2) {
                // Use prefix search when possible (much faster with index)
                $query->where(function ($q) use ($search) {
                    $q->where('devotee_name', 'like', "$search%")
                        ->orWhere('devotee_name', 'like', "%$search%")
                        ->orWhere('devotee_phone', 'like', "$search%");
                });
            }
        }

        $perPage = min($request->integer('per_page', 10), 50); // Cap at 50
        $devotees = $query->orderBy('id')->paginate($perPage);

        return $this->successWithPagination($devotees, 'Devotee list fetched successfully.');
    }

    /**
     * Store a newly created devotee.
     */
    public function store(StoreDevoteeRequest $request)
    {
        $devotee = Devotee::create($request->validated());

        return $this->success($devotee, 'Devotee created successfully.', 201);
    }

    /**
     * Show a specific devotee.
     */
    public function show($id)
    {
        $devotee = Devotee::withTrashed()->find($id);

        if (! $devotee) {
            return $this->notFound('Devotee not found.');
        }

        return $this->success($devotee);
    }

    /**
     * Update devotee details.
     */
    public function update(UpdateDevoteeRequest $request, $id)
    {
        $devotee = Devotee::withTrashed()->find($id);

        if (! $devotee) {
            return $this->notFound('Devotee not found.');
        }

        $devotee->update($request->validated());

        return $this->success($devotee, 'Devotee updated successfully.');
    }

    /**
     * Soft delete devotee.
     */
    public function destroy($id)
    {
        $devotee = Devotee::find($id);

        if (! $devotee) {
            return $this->notFound('Devotee not found.');
        }

        $devotee->delete();

        return $this->success(null, 'Devotee soft deleted successfully.');
    }

    /**
     * Restore a soft deleted devotee.
     */
    public function restore($id)
    {
        $devotee = Devotee::withTrashed()->find($id);

        if (! $devotee) {
            return $this->notFound('Devotee not found.');
        }

        if ($devotee->deleted_at === null) {
            return $this->error('Devotee is already active.');
        }

        $devotee->restore();

        return $this->success($devotee, 'Devotee restored successfully.');
    }

    /**
     * Permanently delete a devotee.
     */
    public function forceDelete($id)
    {
        $devotee = Devotee::withTrashed()->find($id);

        if (! $devotee) {
            return $this->notFound('Devotee not found.');
        }

        $devotee->forceDelete();

        return $this->success(null, 'Devotee permanently deleted.');
    }
}
