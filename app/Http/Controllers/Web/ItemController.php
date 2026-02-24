<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function index(Request $request): Response
    {
        $temple = auth()->user();

        $query = Item::with('category')->where('temple_id', $temple->id);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('item_name', 'like', "%{$search}%");
        }

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $items = $query->orderByDesc('id')->paginate(10)->withQueryString();
        $categories = Category::where('temple_id', $temple->id)->get();

        return Inertia::render('Item/Index', [
            'items' => $items,
            'categories' => $categories,
            'filters' => [
                'search' => $request->search ?? '',
                'category_id' => $request->category_id ?? '',
                'status' => $request->status ?? '',
            ],
        ]);
    }

    public function create(): Response
    {
        $temple = auth()->user();
        $categories = Category::where('temple_id', $temple->id)->get();

        return Inertia::render('Item/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $temple = auth()->user();

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'item_name' => 'required|string|max:150|unique:items,item_name,NULL,id,temple_id,'.$temple->id,
            'unit' => 'required|string|max:50',
            'min_quantity' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['temple_id'] = $temple->id;

        Item::create($validated);

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    public function edit($id): Response
    {
        $temple = auth()->user();
        $item = Item::where('temple_id', $temple->id)->findOrFail($id);
        $categories = Category::where('temple_id', $temple->id)->get();

        return Inertia::render('Item/Edit', [
            'item' => $item,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $item = Item::where('temple_id', $temple->id)->findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'item_name' => 'required|string|max:150|unique:items,item_name,'.$id.',id,temple_id,'.$temple->id,
            'unit' => 'required|string|max:50',
            'min_quantity' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $item->update($validated);

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $temple = auth()->user();
        $item = Item::where('temple_id', $temple->id)->findOrFail($id);

        $item->delete();

        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }
}
