<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\PaymentDetail;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * List all purchases/donations.
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['item', 'supplier'])->latest();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }
        // Pagination params
        $perPage = $request->per_page ?? 10; // default 10
        $purchases = $query->paginate($perPage);
        // $purchases = $query->get();

        return response()->json(['status' => true, 'data' => $purchases, 'meta' => [
            'current_page' => $purchases->currentPage(),
            'per_page' => $purchases->perPage(),
            'total' => $purchases->total(),
            'last_page' => $purchases->lastPage(),
        ]]);
    }

    /**
     * Create a new purchase/donation entry.
     */
    public function store(Request $request)
    {
        // dd('here');
        $validated = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'item_id' => 'required|exists:items,id',
            'member_id' => 'nullable|exists:members,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|decimal:0,2|min:0.01',
            'unit_price' => 'nullable|decimal:0,2|min:0',
            'received_date' => 'required|date',
            'mode' => 'required|in:purchase,donation',
            'remarks' => 'nullable|string|max:1000',
            'payment_mode' => 'nullable|in:cash,card,online,upi',
        ]);

        DB::beginTransaction();

        try {
            // Calculate total price
            $total_price = ($validated['unit_price'] ?? 0) * $validated['quantity'];
            // Merge manually (instead of unpacking)
            $purchaseData = array_merge($validated, [
                'total_price' => $total_price,
            ]);
            $purchase = Purchase::create($purchaseData);
            $purchase->load(['item', 'supplier']);

            // Update item stock (add quantity)
            $item = Item::findOrFail($validated['item_id']);
            $item->current_quantity += $validated['quantity'];
            $item->save();

            PaymentDetail::create([
                'temple_id' => $validated['temple_id'],
                'source_id' => $purchase->id,
                'member_id' => $validated['member_id'] ?? null,
                'payment_date' => now(),
                'payment' => $total_price,
                'source' => 'purchase',
                'type' => 'debit',
                'payment_mode' => $validated['payment_mode'] ?? 'cash',
            ]);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Purchase/Donation recorded successfully',
                'data' => $purchase,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show purchase details.
     */
    public function show($id)
    {
        $purchase = Purchase::with(['item', 'supplier'])->find($id);

        if (! $purchase) {
            return response()->json(['status' => false, 'error' => 'Purchase not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $purchase]);
    }

    /**
     * Update purchase record.
     */
    public function update(Request $request, $id)
    {
        $purchase = Purchase::find($id);

        if (! $purchase) {
            return response()->json(['status' => false, 'error' => 'Purchase not found'], 404);
        }

        $validated = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'item_id' => 'required|exists:items,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
            'received_date' => 'required|date',
            'mode' => 'required|in:purchase,donation',
            'remarks' => 'nullable|string',
            'payment_mode' => 'nullable|in:cash,card,online,upi',
        ]);

        $validated['total_price'] = ($validated['unit_price'] ?? 0) * $validated['quantity'];

        $purchase->update($validated);
        $purchase->load(['item', 'supplier']);

        PaymentDetail::where('source_id', $purchase->id)
            ->where('source', 'purchase')
            ->update([
                'payment' => ($validated['unit_price'] ?? 0) * $validated['quantity'],
                'payment_mode' => $validated['payment_mode'] ?? 'cash',
                'updated_at' => now(),
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Purchase/Donation updated successfully',
            'data' => $purchase,
        ]);
    }

    /**
     * Soft delete purchase.
     */
    public function destroy($id)
    {
        $purchase = Purchase::find($id);
        if (! $purchase) {
            return response()->json(['status' => false, 'error' => 'Purchase not found'], 404);
        }

        $purchase->delete();

        return response()->json(['status' => true, 'message' => 'Purchase/Donation deleted successfully']);
    }

    /**
     * List trashed purchases.
     */
    public function trashed(Request $request)
    {
        $query = Purchase::onlyTrashed();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        $trashed = $query->get();

        return response()->json(['status' => true, 'data' => $trashed]);
    }

    /**
     * Restore soft-deleted purchase.
     */
    public function restore($id)
    {
        $purchase = Purchase::onlyTrashed()->find($id);

        if (! $purchase) {
            return response()->json(['status' => false, 'error' => 'Purchase not found in trash'], 404);
        }

        $purchase->restore();

        return response()->json(['status' => true, 'message' => 'Purchase/Donation restored successfully']);
    }

    /**
     * Permanently delete purchase.
     */
    public function forceDelete($id)
    {
        $purchase = Purchase::onlyTrashed()->find($id);

        if (! $purchase) {
            return response()->json(['status' => false, 'error' => 'Purchase not found'], 404);
        }

        $purchase->forceDelete();

        return response()->json(['status' => true, 'message' => 'Purchase/Donation permanently deleted']);
    }
}
