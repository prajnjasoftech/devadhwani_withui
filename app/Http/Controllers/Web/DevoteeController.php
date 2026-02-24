<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Devotee;
use App\Models\TemplePoojaBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DevoteeController extends Controller
{
    public function index(Request $request): Response
    {
        $temple = auth()->user();

        $query = Devotee::withCount('trackings')
            ->where('temple_id', $temple->id);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('devotee_name', 'like', "%{$search}%")
                    ->orWhere('devotee_phone', 'like', "%{$search}%")
                    ->orWhere('nakshatra', 'like', "%{$search}%");
            });
        }

        if ($request->has('nakshatra') && $request->nakshatra) {
            $query->where('nakshatra', $request->nakshatra);
        }

        $devotees = $query->orderByDesc('id')->paginate(10)->withQueryString();

        // Get distinct nakshatras for filter
        $nakshatras = Devotee::where('temple_id', $temple->id)
            ->whereNotNull('nakshatra')
            ->distinct()
            ->pluck('nakshatra');

        return Inertia::render('Devotee/Index', [
            'devotees' => $devotees,
            'nakshatras' => $nakshatras,
            'filters' => [
                'search' => $request->search ?? '',
                'nakshatra' => $request->nakshatra ?? '',
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Devotee/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $temple = auth()->user();

        $validated = $request->validate([
            'devotee_name' => 'required|string|max:150',
            'devotee_phone' => 'nullable|string|max:15',
            'nakshatra' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        $validated['temple_id'] = $temple->id;

        Devotee::create($validated);

        return redirect()->route('devotees.index')->with('success', 'Devotee added successfully.');
    }

    public function show($id): Response
    {
        $temple = auth()->user();
        $devotee = Devotee::where('temple_id', $temple->id)->findOrFail($id);

        // Get devotee's bookings
        $bookings = TemplePoojaBooking::with('pooja')
            ->where('devotee_id', $id)
            ->orderByDesc('booking_date')
            ->limit(10)
            ->get();

        return Inertia::render('Devotee/Show', [
            'devotee' => $devotee,
            'bookings' => $bookings,
        ]);
    }

    public function edit($id): Response
    {
        $temple = auth()->user();
        $devotee = Devotee::where('temple_id', $temple->id)->findOrFail($id);

        return Inertia::render('Devotee/Edit', [
            'devotee' => $devotee,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $devotee = Devotee::where('temple_id', $temple->id)->findOrFail($id);

        $validated = $request->validate([
            'devotee_name' => 'required|string|max:150',
            'devotee_phone' => 'nullable|string|max:15',
            'nakshatra' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        $devotee->update($validated);

        return redirect()->route('devotees.index')->with('success', 'Devotee updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $temple = auth()->user();
        $devotee = Devotee::where('temple_id', $temple->id)->findOrFail($id);

        $devotee->delete();

        return redirect()->route('devotees.index')->with('success', 'Devotee deleted successfully.');
    }
}
