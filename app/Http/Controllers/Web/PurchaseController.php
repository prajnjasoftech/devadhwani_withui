<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\PaymentDetail;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseController extends Controller
{
    public function index(Request $request): Response
    {
        $temple = auth()->user();

        $query = Purchase::with(['item', 'supplier'])
            ->where('temple_id', $temple->id);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%");
            })->orWhereHas('supplier', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->has('mode') && $request->mode) {
            $query->where('mode', $request->mode);
        }

        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchases = $query->orderByDesc('id')->paginate(10)->withQueryString();
        $suppliers = Supplier::where('temple_id', $temple->id)->get(['id', 'name']);

        return Inertia::render('Purchase/Index', [
            'purchases' => $purchases,
            'suppliers' => $suppliers,
            'filters' => [
                'search' => $request->search ?? '',
                'mode' => $request->mode ?? '',
                'supplier_id' => $request->supplier_id ?? '',
            ],
        ]);
    }

    public function create(): Response
    {
        $temple = auth()->user();
        $items = Item::where('temple_id', $temple->id)->where('status', 'active')->get(['id', 'item_name', 'unit']);
        $suppliers = Supplier::where('temple_id', $temple->id)->get(['id', 'name', 'type']);

        return Inertia::render('Purchase/Create', [
            'items' => $items,
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $temple = auth()->user();

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
            'received_date' => 'required|date',
            'mode' => 'required|in:purchase,donation',
            'remarks' => 'nullable|string|max:1000',
            'payment_mode' => 'nullable|in:cash,card,online,upi',
        ]);

        DB::beginTransaction();

        try {
            $total_price = ($validated['unit_price'] ?? 0) * $validated['quantity'];

            $purchase = Purchase::create([
                'temple_id' => $temple->id,
                'item_id' => $validated['item_id'],
                'supplier_id' => $validated['supplier_id'],
                'quantity' => $validated['quantity'],
                'unit_price' => $validated['unit_price'] ?? 0,
                'total_price' => $total_price,
                'received_date' => $validated['received_date'],
                'mode' => $validated['mode'],
                'remarks' => $validated['remarks'] ?? null,
            ]);

            // Update item stock
            $item = Item::findOrFail($validated['item_id']);
            $item->current_quantity += $validated['quantity'];
            $item->save();

            // Record payment if it's a purchase
            if ($validated['mode'] === 'purchase' && $total_price > 0) {
                PaymentDetail::create([
                    'temple_id' => $temple->id,
                    'source_id' => $purchase->id,
                    'payment_date' => now(),
                    'payment' => $total_price,
                    'source' => 'purchase',
                    'type' => 'debit',
                    'payment_mode' => $validated['payment_mode'] ?? 'cash',
                ]);
            }

            DB::commit();

            return redirect()->route('purchases.index')->with('success', 'Purchase recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to record purchase: '.$e->getMessage());
        }
    }

    public function edit($id): Response
    {
        $temple = auth()->user();
        $purchase = Purchase::with(['item', 'supplier'])
            ->where('temple_id', $temple->id)
            ->findOrFail($id);

        $items = Item::where('temple_id', $temple->id)->where('status', 'active')->get(['id', 'item_name', 'unit']);
        $suppliers = Supplier::where('temple_id', $temple->id)->get(['id', 'name', 'type']);

        return Inertia::render('Purchase/Edit', [
            'purchase' => $purchase,
            'items' => $items,
            'suppliers' => $suppliers,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $purchase = Purchase::where('temple_id', $temple->id)->findOrFail($id);

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
            'received_date' => 'required|date',
            'mode' => 'required|in:purchase,donation',
            'remarks' => 'nullable|string|max:1000',
            'payment_mode' => 'nullable|in:cash,card,online,upi',
        ]);

        $total_price = ($validated['unit_price'] ?? 0) * $validated['quantity'];

        $purchase->update([
            'item_id' => $validated['item_id'],
            'supplier_id' => $validated['supplier_id'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'] ?? 0,
            'total_price' => $total_price,
            'received_date' => $validated['received_date'],
            'mode' => $validated['mode'],
            'remarks' => $validated['remarks'] ?? null,
        ]);

        // Update payment record
        PaymentDetail::where('source_id', $purchase->id)
            ->where('source', 'purchase')
            ->update([
                'payment' => $total_price,
                'payment_mode' => $validated['payment_mode'] ?? 'cash',
            ]);

        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $temple = auth()->user();
        $purchase = Purchase::where('temple_id', $temple->id)->findOrFail($id);

        $purchase->delete();

        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully.');
    }
}
