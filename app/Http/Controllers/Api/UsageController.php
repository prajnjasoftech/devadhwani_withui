<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Usage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsageController extends Controller
{
    /**
     * List all usages.
     */
    public function index(Request $request)
    {
        $query = Usage::with(['item', 'user'])->latest();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }
        // Pagination params
        $perPage = $request->per_page ?? 10; // default 10
        $usages = $query->paginate($perPage);

        return response()->json(['status' => true, 'data' => $usages, 'meta' => [
            'current_page' => $usages->currentPage(),
            'per_page' => $usages->perPage(),
            'total' => $usages->total(),
            'last_page' => $usages->lastPage(),
        ]]);
        // $usages = $query->get();
        // return response()->json(['status' => true, 'data' => $usages]);
    }

    /**
     * Create a new usage record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'item_id' => 'required|exists:items,id',
            'used_by' => 'required|exists:members,id',
            'quantity' => 'required|numeric|min:0.01',
            'used_for' => 'required|string|max:150',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $usage = Usage::create($validated);

            // Reduce item stock
            $item = Item::findOrFail($validated['item_id']);
            $item->current_quantity -= $validated['quantity'];
            if ($item->current_quantity < 0) {
                $item->current_quantity = 0;
            }
            $item->save();
            /*
            // Create stock transaction
            \App\Models\StockTransaction::create([
                'temple_id' => $validated['temple_id'],
                'item_id' => $validated['item_id'],
                'type' => 'out',
                'quantity' => $validated['quantity'],
                'reference_no' => 'USG-' . str_pad($usage->id, 6, '0', STR_PAD_LEFT),
                'remarks' => 'Auto-created from usage ID: ' . $usage->id,
            ]);*/

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Usage recorded successfully and stock updated',
                'data' => $usage,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a specific usage.
     */
    public function show($id)
    {
        $usage = Usage::with(['item', 'user'])->find($id);

        if (! $usage) {
            return response()->json(['status' => false, 'error' => 'Usage not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $usage]);
    }

    /**
     * Update usage record.
     */
    public function update(Request $request, $id)
    {
        $usage = Usage::find($id);

        if (! $usage) {
            return response()->json(['status' => false, 'error' => 'Usage not found'], 404);
        }

        $validated = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'item_id' => 'required|exists:items,id',
            'used_by' => 'required|exists:members,id',
            'quantity' => 'required|numeric|min:0.01',
            'used_for' => 'required|string|max:150',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $usage->update($validated);

        return response()->json(['status' => true, 'message' => 'Usage updated successfully', 'data' => $usage]);
    }

    /**
     * Soft delete usage record.
     */
    public function destroy($id)
    {
        $usage = Usage::find($id);

        if (! $usage) {
            return response()->json(['status' => false, 'error' => 'Usage not found'], 404);
        }

        $usage->delete();

        return response()->json(['status' => true, 'message' => 'Usage deleted successfully']);
    }

    /**
     * List trashed records.
     */
    public function trashed(Request $request)
    {
        $query = Usage::onlyTrashed();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        $trashed = $query->get();

        return response()->json(['status' => true, 'data' => $trashed]);
    }

    /**
     * Restore a soft-deleted usage.
     */
    public function restore($id)
    {
        $usage = Usage::onlyTrashed()->find($id);

        if (! $usage) {
            return response()->json(['status' => false, 'error' => 'Usage not found in trash'], 404);
        }

        $usage->restore();

        return response()->json(['status' => true, 'message' => 'Usage restored successfully']);
    }

    /**
     * Force delete a usage permanently.
     */
    public function forceDelete($id)
    {
        $usage = Usage::onlyTrashed()->find($id);

        if (! $usage) {
            return response()->json(['status' => false, 'error' => 'Usage not found'], 404);
        }

        $usage->forceDelete();

        return response()->json(['status' => true, 'message' => 'Usage permanently deleted']);
    }
}
