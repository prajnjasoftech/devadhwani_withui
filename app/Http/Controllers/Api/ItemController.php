<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    /**
     * Display a listing of items (filter by temple_id optional).
     */
    public function index(Request $request)
    {
        $query = Item::with(['category'])->latest();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }
        // Pagination params
        $perPage = $request->per_page ?? 10; // default 10
        $items = $query->paginate($perPage);

        return response()->json(['status' => true, 'data' => $items, 'meta' => [
            'current_page' => $items->currentPage(),
            'per_page' => $items->perPage(),
            'total' => $items->total(),
            'last_page' => $items->lastPage(),
        ]]);
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'temple_id' => 'required|exists:temples,id',
            'category_id' => 'required|exists:categories,id',
            'item_name' => 'required|string|max:150|unique:items,item_name,NULL,id,temple_id,'.$request->temple_id,
            'unit' => 'required|string|max:50',
            'min_quantity' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'error' => collect($validator->errors()->all())->implode(', '),
            ], 422);
        }
        $validated = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'category_id' => 'required|exists:categories,id',
            'item_name' => 'required|string|max:150|unique:items,item_name,NULL,id,temple_id,'.$request->temple_id,
            'unit' => 'required|string|max:50',
            'min_quantity' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);
        $item = Item::create($validated);
        $item->load('category');

        return response()->json([
            'status' => true,
            'message' => 'Item created successfully',
            'data' => $item,
        ]);
    }

    /**
     * Display the specified item.
     */
    public function show($id)
    {
        $item = Item::with(['category'])->find($id);

        if (! $item) {
            return response()->json(['status' => false, 'error' => 'Item not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $item]);
    }

    /**
     * Update the specified item.
     */
    public function update(Request $request, $id)
    {
        $item = Item::find($id);

        if (! $item) {
            return response()->json(['status' => false, 'error' => 'Item not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'temple_id' => 'required|exists:temples,id',
            'category_id' => 'required|exists:categories,id',
            'item_name' => 'required|string|max:150|unique:items,item_name,'.$id.',id,temple_id,'.$request->temple_id,
            'unit' => 'required|string|max:50',
            'min_quantity' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'error' => collect($validator->errors()->all())->implode(', '),
            ], 422);
        }
        $validated = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'category_id' => 'required|exists:categories,id',
            'item_name' => 'required|string|max:150|unique:items,item_name,'.$id.',id,temple_id,'.$request->temple_id,
            'unit' => 'required|string|max:50',
            'min_quantity' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);
        $item->update($validated);
        $item->load('category');

        return response()->json([
            'status' => true,
            'message' => 'Item updated successfully',
            'data' => $item,
        ]);
    }

    /**
     * Soft delete item.
     */
    public function destroy($id)
    {
        $item = Item::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'error' => 'Item not found'], 404);
        }

        $item->delete();

        return response()->json(['status' => true, 'message' => 'Item deleted successfully']);
    }

    /**
     * List all trashed items.
     */
    public function trashed(Request $request)
    {
        $query = Item::onlyTrashed();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        $trashed = $query->get();

        return response()->json(['status' => true, 'data' => $trashed]);
    }

    /**
     * Restore a deleted item.
     */
    public function restore($id)
    {
        $item = Item::onlyTrashed()->find($id);

        if (! $item) {
            return response()->json(['status' => false, 'error' => 'Item not found in trash'], 404);
        }

        $item->restore();

        return response()->json(['status' => true, 'message' => 'Item restored successfully']);
    }

    /**
     * Permanently delete item.
     */
    public function forceDelete($id)
    {
        $item = Item::onlyTrashed()->find($id);

        if (! $item) {
            return response()->json(['status' => false, 'error' => 'Item not found'], 404);
        }

        $item->forceDelete();

        return response()->json(['status' => true, 'message' => 'Item permanently deleted']);
    }
}
