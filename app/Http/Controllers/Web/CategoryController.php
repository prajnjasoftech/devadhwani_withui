<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $temple = auth()->user();

        $query = Category::withCount('items')->where('temple_id', $temple->id);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->orderByDesc('id')->paginate(10)->withQueryString();

        return Inertia::render('Category/Index', [
            'categories' => $categories,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Category/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $temple = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,NULL,id,temple_id,'.$temple->id,
            'description' => 'nullable|string',
        ]);

        $validated['temple_id'] = $temple->id;

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit($id): Response
    {
        $temple = auth()->user();
        $category = Category::where('temple_id', $temple->id)->findOrFail($id);

        return Inertia::render('Category/Edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $category = Category::where('temple_id', $temple->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,'.$id.',id,temple_id,'.$temple->id,
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $temple = auth()->user();
        $category = Category::where('temple_id', $temple->id)->findOrFail($id);

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
