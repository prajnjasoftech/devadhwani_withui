<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Temple;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TempleController extends Controller
{
    /**
     * Temple List API
     */
    public function index(Request $request)
    {
        $query = Temple::query();

        // Lightweight response for dropdowns (only id and temple_name)
        if ($request->get('fields') === 'dropdown') {
            $temples = $query->select('id', 'temple_name')->orderBy('temple_name')->get();

            return response()->json([
                'status' => true,
                'message' => 'Temple list fetched successfully',
                'data' => $temples,
            ], 200);
        }

        if ($request->filled('temple_id')) {
            $temple_id = $request->temple_id;
            $query->where('id', $temple_id);
        }

        // 🔍 Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('temple_name', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('database_name', 'like', "%$search%");
            });
        }

        // 📄 Pagination (default 10)
        $perPage = $request->get('per_page', 10);

        $temples = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Temple list fetched successfully',
            'data' => $temples,
        ], 200);
    }

    /**
     * Update Temple
     */
    public function update(Request $request, $id)
    {
        $temple = Temple::find($id);

        if (! $temple) {
            return response()->json([
                'status' => false,
                'message' => 'Temple not found',
            ], 404);
        }

        $validated = $request->validate([
            'temple_name' => 'nullable|string|max:255',
            'temple_address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'database_name' => 'nullable|string|max:255',
            'temple_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // 🖼️ Upload logo
        if ($request->hasFile('temple_logo')) {

            // delete old logo
            if ($temple->temple_logo) {
                Storage::delete('public/temples/'.$temple->temple_logo);
            }

            $file = $request->file('temple_logo');
            $fileName = 'temple_'.time().'.'.$file->getClientOriginalExtension();

            $file->storeAs('public/temples', $fileName);

            $validated['temple_logo'] = $fileName;
        }

        $temple->fill($validated);
        $temple->save();
        $temple->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Temple updated successfully',
            'data' => $temple,
        ], 200);
    }
}
