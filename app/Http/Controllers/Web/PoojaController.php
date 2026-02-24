<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TemplePooja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PoojaController extends Controller
{
    public function index(Request $request): Response
    {
        $temple = auth()->user();

        $query = TemplePooja::where('temple_id', $temple->id);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('pooja_name', 'like', "%{$search}%");
        }

        if ($request->has('period') && $request->period) {
            $query->where('period', $request->period);
        }

        $poojas = $query->orderByDesc('id')->paginate(10)->withQueryString();

        return Inertia::render('Pooja/Index', [
            'poojas' => $poojas,
            'filters' => [
                'search' => $request->search ?? '',
                'period' => $request->period ?? '',
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Pooja/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $temple = auth()->user();

        $validated = $request->validate([
            'pooja_name' => 'required|string|max:150',
            'period' => 'required|in:once,daily,monthly,yearly',
            'amount' => 'required|numeric|min:0',
            'details' => 'nullable|string',
            'devotees_required' => 'nullable|boolean',
            'next_pooja_perform_date' => 'nullable|date',
        ]);

        $validated['temple_id'] = $temple->id;

        TemplePooja::create($validated);

        return redirect()->route('poojas.index')->with('success', 'Pooja created successfully.');
    }

    public function edit($id): Response
    {
        $temple = auth()->user();
        $pooja = TemplePooja::where('temple_id', $temple->id)->findOrFail($id);

        return Inertia::render('Pooja/Edit', [
            'pooja' => $pooja,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $pooja = TemplePooja::where('temple_id', $temple->id)->findOrFail($id);

        $validated = $request->validate([
            'pooja_name' => 'required|string|max:150',
            'period' => 'required|in:once,daily,monthly,yearly',
            'amount' => 'required|numeric|min:0',
            'details' => 'nullable|string',
            'devotees_required' => 'nullable|boolean',
            'next_pooja_perform_date' => 'nullable|date',
        ]);

        $pooja->update($validated);

        return redirect()->route('poojas.index')->with('success', 'Pooja updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $temple = auth()->user();
        $pooja = TemplePooja::where('temple_id', $temple->id)->findOrFail($id);

        $pooja->delete();

        return redirect()->route('poojas.index')->with('success', 'Pooja deleted successfully.');
    }
}
