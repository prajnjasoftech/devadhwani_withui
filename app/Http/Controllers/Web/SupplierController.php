<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    public function index(Request $request): Response
    {
        $temple = auth()->user();

        $query = Supplier::where('temple_id', $temple->id);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $suppliers = $query->orderByDesc('id')->paginate(10)->withQueryString();

        return Inertia::render('Supplier/Index', [
            'suppliers' => $suppliers,
            'filters' => [
                'search' => $request->search ?? '',
                'type' => $request->type ?? '',
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Supplier/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $temple = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'contact_number' => 'required|string|max:15',
            'address' => 'nullable|string',
            'type' => 'required|in:vendor,donor',
        ]);

        $validated['temple_id'] = $temple->id;

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function edit($id): Response
    {
        $temple = auth()->user();
        $supplier = Supplier::where('temple_id', $temple->id)->findOrFail($id);

        return Inertia::render('Supplier/Edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $supplier = Supplier::where('temple_id', $temple->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'contact_number' => 'required|string|max:15',
            'address' => 'nullable|string',
            'type' => 'required|in:vendor,donor',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $temple = auth()->user();
        $supplier = Supplier::where('temple_id', $temple->id)->findOrFail($id);

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
