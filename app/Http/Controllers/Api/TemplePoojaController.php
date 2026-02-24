<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TemplePooja;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TemplePoojaController extends Controller
{
    /**
     * List all temple poojas.
     */
    public function index(Request $request)
    {
        $query = TemplePooja::query();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        if ($request->has('member_id')) {
            $query->where('member_id', $request->member_id);
        }
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pooja_name', 'like', "%$search%");
            });
        }
        if ($request->has('with_trashed') && $request->with_trashed == true) {
            $query->withTrashed();
        }
        // Pagination params
        $perPage = $request->per_page ?? 10; // default 10
        $poojas = $query->orderByDesc('id')->paginate($perPage);

        return response()->json(['status' => true, 'data' => $poojas, 'meta' => [
            'current_page' => $poojas->currentPage(),
            'per_page' => $poojas->perPage(),
            'total' => $poojas->total(),
            'last_page' => $poojas->lastPage(),
        ]]);
        // $poojas = $query->orderByDesc('id')->get();

        // return response()->json(['status' => true,'message' => 'Temple Pooja list fetched successfully.','data' => $poojas]);
    }

    /**
     * Create a new temple pooja.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'temple_id' => 'required|integer|exists:temples,id',
            'member_id' => 'nullable|integer|exists:members,id',
            'pooja_name' => 'required|string|max:150',
            'period' => 'required|in:once,daily,monthly,yearly',
            'amount' => 'required|numeric|min:0',
            'devotees_required' => 'nullable|boolean',
            'details' => 'nullable|string',
            'next_pooja_perform_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'error' => collect($validator->errors()->all())->implode(', '),
            ], 422);
        }
        $data = $request->all();

        // 🔥 Convert date to MySQL format
        if (! empty($data['next_pooja_perform_date'])) {
            $data['next_pooja_perform_date'] = Carbon::parse(
                $data['next_pooja_perform_date']
            )->format('Y-m-d');
        }
        $pooja = TemplePooja::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Temple Pooja created successfully.',
            'data' => $pooja,
        ], 201);
    }

    /**
     * Show a specific pooja.
     */
    public function show($id)
    {
        $pooja = TemplePooja::find($id);

        if (! $pooja) {
            return response()->json([
                'status' => false,
                'message' => 'Temple Pooja not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $pooja,
        ]);
    }

    /**
     * Update a pooja.
     */
    public function update(Request $request, $id)
    {
        $pooja = TemplePooja::find($id);

        if (! $pooja) {
            return response()->json([
                'status' => false,
                'message' => 'Temple Pooja not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'temple_id' => 'sometimes|integer|exists:temples,id',
            'member_id' => 'sometimes|integer|exists:members,id',
            'pooja_name' => 'sometimes|string|max:150',
            'period' => 'sometimes|in:once,daily,monthly,yearly',
            'amount' => 'sometimes|numeric|min:0',
            'devotees_required' => 'nullable|boolean',
            'details' => 'nullable|string',
            'next_pooja_perform_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'error' => collect($validator->errors()->all())->implode(', '),
            ], 422);
        }

        // Capture old date before update
        $oldNextPoojaDate = $pooja->next_pooja_perform_date;

        $data = $request->all();

        // 🔥 Convert date to MySQL format
        if (! empty($data['next_pooja_perform_date'])) {
            $data['next_pooja_perform_date'] = Carbon::parse(
                $data['next_pooja_perform_date']
            )->format('Y-m-d');
        }

        $pooja->update($data);
        $pooja->refresh();

        // Update tracking records if next_pooja_perform_date changed
        $oldDateStr = $oldNextPoojaDate ? Carbon::parse($oldNextPoojaDate)->format('Y-m-d') : null;
        $newDateStr = $pooja->next_pooja_perform_date ? Carbon::parse($pooja->next_pooja_perform_date)->format('Y-m-d') : null;

        if ($oldDateStr !== $newDateStr && $newDateStr) {
            $this->updateTrackingByPoojaId($pooja->id, $newDateStr);
        }

        return response()->json([
            'status' => true,
            'message' => 'Temple Pooja updated successfully.',
            'data' => $pooja,
        ]);
    }

    /**
     * Soft delete.
     */
    public function destroy($id)
    {
        $pooja = TemplePooja::find($id);

        if (! $pooja) {
            return response()->json([
                'status' => false,
                'message' => 'Temple Pooja not found.',
            ], 404);
        }

        $pooja->delete();

        return response()->json([
            'status' => true,
            'message' => 'Temple Pooja soft deleted successfully.',
        ]);
    }

    /**
     * Restore.
     */
    public function restore($id)
    {
        $pooja = TemplePooja::withTrashed()->find($id);

        if (! $pooja) {
            return response()->json([
                'status' => false,
                'message' => 'Temple Pooja not found.',
            ], 404);
        }

        $pooja->restore();

        return response()->json([
            'status' => true,
            'message' => 'Temple Pooja restored successfully.',
            'data' => $pooja,
        ]);
    }

    /**
     * Force delete.
     */
    public function forceDelete($id)
    {
        $pooja = TemplePooja::withTrashed()->find($id);

        if (! $pooja) {
            return response()->json([
                'status' => false,
                'message' => 'Temple Pooja not found.',
            ], 404);
        }

        $pooja->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Temple Pooja permanently deleted.',
        ]);
    }

    /**
     * Get trashed (soft deleted) records.
     */
    public function trashed(Request $request)
    {
        $query = TemplePooja::onlyTrashed();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        $TemplePooja = $query->get();

        return response()->json(['status' => true, 'data' => $TemplePooja]);
    }

    /**
     * Update tracking records when next_pooja_perform_date changes.
     *
     * Logic:
     * - Find the NEXT PENDING tracking record for each booking of this pooja
     * - Update it to the new date
     * - If new date is beyond booking_end_date, extend the booking
     *
     * Example: Pooja done on March 5th, admin sets next date to March 28th
     * → The next pending tracking (April's) gets updated to March 28th
     */
    public function updateTrackingByPoojaId($poojaId, $newDate)
    {
        if (! $newDate) {
            return;
        }

        $newDateParsed = Carbon::parse($newDate);

        // Find all active bookings for this pooja (monthly or yearly period)
        $bookings = DB::table('temple_pooja_bookings')
            ->where('pooja_id', $poojaId)
            ->whereIn('period', ['monthly', 'yearly'])
            ->whereNull('deleted_at')
            ->where('booking_status', '!=', 'cancelled')
            ->get();

        foreach ($bookings as $booking) {
            // Find the NEXT pending tracking record (earliest one not completed)
            $tracking = DB::table('temple_pooja_bookings_tracking')
                ->where('booking_id', $booking->id)
                ->where('booking_status', '!=', 'completed')
                ->orderBy('pooja_date', 'asc')
                ->first();

            if ($tracking) {
                // Update the tracking record to the new date
                DB::table('temple_pooja_bookings_tracking')
                    ->where('id', $tracking->id)
                    ->update([
                        'pooja_date' => $newDateParsed->format('Y-m-d'),
                        'updated_at' => now(),
                    ]);

                // If new date is beyond booking_end_date, extend the booking
                $bookingEndDate = $booking->booking_end_date ? Carbon::parse($booking->booking_end_date) : null;
                if ($bookingEndDate && $newDateParsed->gt($bookingEndDate)) {
                    DB::table('temple_pooja_bookings')
                        ->where('id', $booking->id)
                        ->update([
                            'booking_end_date' => $newDateParsed->format('Y-m-d'),
                            'updated_at' => now(),
                        ]);
                }
            }
        }
    }
}
