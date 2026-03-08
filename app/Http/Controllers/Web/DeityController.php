<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TempleDeity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeityController extends Controller
{
    public function index(Request $request): Response
    {
        $temple = auth()->user();

        $query = TempleDeity::where('temple_id', $temple->id);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        $deities = $query->orderByDesc('id')->paginate(10)->withQueryString();

        return Inertia::render('Deity/Index', [
            'deities' => $deities,
            'filters' => [
                'search' => $request->search ?? '',
                'status' => $request->status ?? '',
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Deity/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $temple = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['temple_id'] = $temple->id;
        $validated['is_active'] = $validated['is_active'] ?? true;

        TempleDeity::create($validated);

        return redirect()->route('deities.index')->with('success', 'Deity created successfully.');
    }

    public function edit($id): Response
    {
        $temple = auth()->user();
        $deity = TempleDeity::where('temple_id', $temple->id)->findOrFail($id);

        return Inertia::render('Deity/Edit', [
            'deity' => $deity,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $deity = TempleDeity::where('temple_id', $temple->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $deity->update($validated);

        return redirect()->route('deities.index')->with('success', 'Deity updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $temple = auth()->user();
        $deity = TempleDeity::where('temple_id', $temple->id)->findOrFail($id);

        $deity->delete();

        return redirect()->route('deities.index')->with('success', 'Deity deleted successfully.');
    }
}
