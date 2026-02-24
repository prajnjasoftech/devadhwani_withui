<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * List all suppliers/donors (optionally filtered by temple_id)
     */
    public function index(Request $request)
    {
        $query = Supplier::latest();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        // $suppliers = $query->get();
        // Pagination params
        $perPage = $request->per_page ?? 10; // default 10
        $suppliers = $query->paginate($perPage);

        return response()->json(['status' => true, 'data' => $suppliers, 'meta' => [
            'current_page' => $suppliers->currentPage(),
            'per_page' => $suppliers->perPage(),
            'total' => $suppliers->total(),
            'last_page' => $suppliers->lastPage(),
        ]]);
        // return response()->json(['status' => true, 'data' => $suppliers]);
    }

    /**
     * Create new supplier/donor
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'name' => 'required|string|max:150',
            'contact_number' => 'required|string|max:15',
            'address' => 'nullable|string',
            'type' => 'required|in:vendor,donor',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Supplier/Donor added successfully',
            'data' => $supplier,
        ]);
    }

    /**
     * Show supplier details
     */
    public function show($id)
    {
        $supplier = Supplier::find($id);

        if (! $supplier) {
            return response()->json(['status' => false, 'error' => 'Supplier not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $supplier]);
    }

    /**
     * Update supplier/donor
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::find($id);

        if (! $supplier) {
            return response()->json(['status' => false, 'error' => 'Supplier not found'], 404);
        }

        $validated = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'name' => 'required|string|max:150',
            'contact_number' => 'required|string|max:15',
            'address' => 'nullable|string',
            'type' => 'required|in:vendor,donor',
        ]);

        $supplier->update($validated);
        $supplier->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Supplier/Donor updated successfully',
            'data' => $supplier,
        ]);
    }

    /**
     * Soft delete supplier
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        if (! $supplier) {
            return response()->json(['status' => false, 'error' => 'Supplier not found'], 404);
        }

        $supplier->delete();

        return response()->json(['status' => true, 'message' => 'Supplier/Donor deleted successfully']);
    }

    /**
     * List all trashed suppliers
     */
    public function trashed(Request $request)
    {
        $query = Supplier::onlyTrashed();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        $trashed = $query->get();

        return response()->json(['status' => true, 'data' => $trashed]);
    }

    /**
     * Restore a soft deleted supplier
     */
    public function restore($id)
    {
        $supplier = Supplier::onlyTrashed()->find($id);

        if (! $supplier) {
            return response()->json(['status' => false, 'error' => 'Supplier not found in trash'], 404);
        }

        $supplier->restore();

        return response()->json(['status' => true, 'message' => 'Supplier/Donor restored successfully']);
    }

    /**
     * Force delete supplier permanently
     */
    public function forceDelete($id)
    {
        $supplier = Supplier::onlyTrashed()->find($id);

        if (! $supplier) {
            return response()->json(['status' => false, 'error' => 'Supplier not found'], 404);
        }

        $supplier->forceDelete();

        return response()->json(['status' => true, 'message' => 'Supplier/Donor permanently deleted']);
    }
}
