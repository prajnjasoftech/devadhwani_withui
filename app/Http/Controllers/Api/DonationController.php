<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    /**
     * List all donations (optionally filtered by temple_id)
     */
    public function index(Request $request)
    {
        $query = Donation::latest();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        $perPage = $request->per_page ?? 10;
        $donations = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $donations,
            'meta' => [
                'current_page' => $donations->currentPage(),
                'per_page' => $donations->perPage(),
                'total' => $donations->total(),
                'last_page' => $donations->lastPage(),
            ],
        ]);
    }

    /**
     * Create new donation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'donor_name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:15',
            'amount' => 'required|numeric|min:0.01',
            'mode' => 'nullable|string|in:cash,upi,bank transfer,cheque,other',
            'donation_date' => 'required|date',
            'purpose' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $donation = Donation::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Donation added successfully',
            'data' => $donation,
        ]);
    }

    /**
     * Show donation details
     */
    public function show($id)
    {
        $donation = Donation::find($id);

        if (! $donation) {
            return response()->json(['status' => false, 'error' => 'Donation not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $donation]);
    }

    /**
     * Update donation
     */
    public function update(Request $request, $id)
    {
        $donation = Donation::find($id);

        if (! $donation) {
            return response()->json(['status' => false, 'error' => 'Donation not found'], 404);
        }

        $validated = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'donor_name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:15',
            'amount' => 'required|numeric|min:0.01',
            'mode' => 'nullable|string|in:cash,upi,bank transfer,cheque,other',
            'donation_date' => 'required|date',
            'purpose' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $donation->update($validated);
        $donation->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Donation updated successfully',
            'data' => $donation,
        ]);
    }

    /**
     * Soft delete donation
     */
    public function destroy($id)
    {
        $donation = Donation::find($id);

        if (! $donation) {
            return response()->json(['status' => false, 'error' => 'Donation not found'], 404);
        }

        $donation->delete();

        return response()->json(['status' => true, 'message' => 'Donation deleted successfully']);
    }

    /**
     * List all trashed donations
     */
    public function trashed(Request $request)
    {
        $query = Donation::onlyTrashed();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        $trashed = $query->get();

        return response()->json(['status' => true, 'data' => $trashed]);
    }

    /**
     * Restore a soft deleted donation
     */
    public function restore($id)
    {
        $donation = Donation::onlyTrashed()->find($id);

        if (! $donation) {
            return response()->json(['status' => false, 'error' => 'Donation not found in trash'], 404);
        }

        $donation->restore();

        return response()->json(['status' => true, 'message' => 'Donation restored successfully']);
    }

    /**
     * Force delete donation permanently
     */
    public function forceDelete($id)
    {
        $donation = Donation::onlyTrashed()->find($id);

        if (! $donation) {
            return response()->json(['status' => false, 'error' => 'Donation not found'], 404);
        }

        $donation->forceDelete();

        return response()->json(['status' => true, 'message' => 'Donation permanently deleted']);
    }
}
