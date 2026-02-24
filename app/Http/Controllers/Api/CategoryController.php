<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Temple;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * List all categories (optional filter by temple_id)
     */
    public function index(Request $request)
    {
        $query = Category::latest();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }
        // Pagination params
        $perPage = $request->per_page ?? 10; // default 10
        $categories = $query->paginate($perPage);
        // $categories = $query->get();

        return response()->json(['status' => true, 'data' => $categories, 'meta' => [
            'current_page' => $categories->currentPage(),
            'per_page' => $categories->perPage(),
            'total' => $categories->total(),
            'last_page' => $categories->lastPage(),
        ]]);
    }

    /**
     * Create new category
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'temple_id' => 'required|exists:temples,id',
            'name' => 'required|string|max:100|unique:categories,name,NULL,id,temple_id,'.$request->temple_id,
            'description' => 'nullable|string',
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
            'name' => 'required|string|max:100|unique:categories,name,NULL,id,temple_id,'.$request->temple_id,
            'description' => 'nullable|string',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Category created successfully',
            'data' => $category,
        ]);
    }

    /**
     * Show single category
     */
    public function show($id)
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json(['status' => false, 'error' => 'Category not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $category]);
    }

    /**
     * Update category (temple_id and name unique per temple)
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (! $category) {
            return response()->json(['status' => false, 'error' => 'Category not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'temple_id' => 'required|exists:temples,id',
            'name' => 'required|string|max:100,name,'.$id.',id,temple_id,'.$request->temple_id,
            'description' => 'nullable|string',

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
            'name' => 'required|string|max:100|unique:categories,name,'.$id.',id,temple_id,'.$request->temple_id,
            'description' => 'nullable|string',
        ]);

        $category->update($validated);
        $category->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Category updated successfully',
            'data' => $category,
        ]);
    }

    /**
     * Soft delete
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        if (! $category) {
            return response()->json(['status' => false, 'error' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['status' => true, 'message' => 'Category deleted successfully']);
    }

    /**
     * List trashed
     */
    public function trashed(Request $request)
    {
        $query = Category::onlyTrashed();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        $trashed = $query->get();

        return response()->json(['status' => true, 'data' => $trashed]);
    }

    /**
     * Restore
     */
    public function restore($id)
    {
        $category = Category::onlyTrashed()->find($id);
        if (! $category) {
            return response()->json(['status' => false, 'error' => 'Category not found in trash'], 404);
        }

        $category->restore();

        return response()->json(['status' => true, 'message' => 'Category restored successfully']);
    }

    /**
     * Force delete
     */
    public function forceDelete($id)
    {
        $category = Category::onlyTrashed()->find($id);
        if (! $category) {
            return response()->json(['status' => false, 'error' => 'Category not found'], 404);
        }

        $category->forceDelete();

        return response()->json(['status' => true, 'message' => 'Category permanently deleted']);
    }
}
