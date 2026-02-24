<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Devotee;
use App\Models\PaymentDetail;
use App\Models\TemplePooja;
use App\Models\TemplePoojaBooking;
use App\Models\TemplePoojaBookingTracking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TemplePoojaBookingController extends Controller
{
    /**
     * Display a listing of all bookings.
     * Optional filter: temple_id
     */
    public function index(Request $request)
    {
        $query = TemplePoojaBooking::query();

        /* -------------------- Filters -------------------- */

        if ($request->filled('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        if ($request->filled('pooja_id')) {
            $query->where('pooja_id', $request->pooja_id);
        }

        if ($request->filled('status')) {
            $query->where('booking_status', $request->status);
        }

        /* -------------------- Date Filter -------------------- */

        if ($request->start_date && $request->end_date) {
            $dateFilter = [$request->start_date, $request->end_date];
        } elseif ($request->start_date) {
            $dateFilter = [$request->start_date, $request->start_date];
        } else {
            $today = now()->toDateString();
            $dateFilter = [$today, $today];
        }

        $perPage = $request->per_page ?? 10;

        /* ----------------------------------------------------
       1️⃣ FILTER BOOKINGS:
       Include booking ONLY if at least one pooja date matches
    ---------------------------------------------------- */

        $bookings = $query
            ->whereHas('trackings', function ($q) use ($dateFilter) {
                $q->whereDate('pooja_date', '>=', $dateFilter[0])
                    ->whereDate('pooja_date', '<=', $dateFilter[1]);
            })
            ->with([
                'pooja',
                'member',
                // IMPORTANT: load ALL trackings (no date filter here)
                'trackings',
                'trackings.devotee',
            ])
            ->latest()
            ->paginate($perPage);

        /* ----------------------------------------------------
       2️⃣ DEVOTEE-WISE SUMMARY (ALL POOJAS)
    ---------------------------------------------------- */

        $bookings->through(function ($booking) {

            // Group trackings by devotee (NULL = Guest)
            $devoteeGroups = $booking->trackings->groupBy(function ($tracking) {
                return $tracking->devotee_id ?? 'guest';
            });

            $booking->devotees = $devoteeGroups->map(function ($trackings, $devoteeKey) {

                // Handle guest devotee
                if ($devoteeKey === 'guest') {
                    $devotee = null;
                } else {
                    $devotee = $trackings->first()->devotee;
                }

                $paidTrackings = $trackings->whereIn('payment_status', ['done', 'partial']);
                $pendingTrackings = $trackings->where('payment_status', '!=', 'done');
                $performedTrackings = $trackings->where('booking_status', 'completed');

                return [
                    'id' => $devotee?->id,
                    'name' => $devotee?->devotee_name ?? 'Guest Devotee',
                    'phone' => $devotee?->devotee_phone ?? null,
                    'nakshatra' => $devotee?->nakshatra ?? null,
                    'address' => $devotee?->address ?? null,

                    'tracking_summary' => [
                        // Counts (FULL booking)
                        'total_pooja' => $trackings->count(),
                        'paid_pooja' => $paidTrackings->count(),
                        'remaining_pooja' => $pendingTrackings->count(),
                        'pooja_performed' => $performedTrackings->count(),
                        'remaining_pooja_perform' => $trackings->count() - $performedTrackings->count(),

                        // Amounts (FULL booking)
                        'paid_amount' => $paidTrackings->sum('paid_amount'),
                        'remaining_amount' => $pendingTrackings->sum('due_amount'),

                        // Dates (FULL booking)
                        'done_pooja_dates' => $paidTrackings->pluck('pooja_date')->values(),
                        'pending_pooja_dates' => $pendingTrackings->pluck('pooja_date')->values(),
                        'performed_dates' => $performedTrackings->pluck('pooja_date')->values(),
                        'pending_performance_dates' => $trackings
                            ->where('booking_status', '!=', 'completed')
                            ->pluck('pooja_date')
                            ->values(),
                    ],
                ];
            })->values();

            // Remove raw trackings from API output
            unset($booking->trackings);

            return $booking;
        });

        /* -------------------- Response -------------------- */

        return response()->json([
            'status' => true,
            'data' => $bookings,
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
                'last_page' => $bookings->lastPage(),
            ],
        ]);
    }

    /* =========================================================
     |  DEVOTEE RESOLUTION
     ========================================================= */
    private function resolveDevotees(Request $request, int $templeId): array
    {
        if (! $request->filled('devotees') || ! is_array($request->devotees)) {
            return [['id' => null, 'nakshatra' => null]];
        }

        $result = [];

        foreach ($request->devotees as $dev) {
            if (! empty($dev['devotee_id'])) {
                $d = Devotee::findOrFail($dev['devotee_id']);
            } else {
                $d = Devotee::firstOrCreate(
                    [
                        'temple_id' => $templeId,
                        'devotee_phone' => $dev['devotee_phone'] ?? null,
                        'devotee_name' => $dev['devotee_name'],
                        'nakshatra' => $dev['nakshatra'] ?? null,
                        'address' => $dev['address'] ?? null,
                    ],
                    [
                        'devotee_name' => $dev['devotee_name'],
                        'nakshatra' => $dev['nakshatra'] ?? null,
                        'address' => $dev['address'] ?? null,
                    ]
                );
            }

            $result[] = [
                'id' => $d->id,
                'nakshatra' => $d->nakshatra,
            ];
        }

        return $result;
    }

    /* =========================================================
     |  NAKSHATRA DATE (MONTHLY)
     ========================================================= */
    private function getNakshatraDateForMonth(string $startDate, string $nakshatra, Carbon $month)
    {
        return \App\Models\Panchang::whereYear('datetime', '=', $month->year)
            ->whereMonth('datetime', '=', $month->month)
            ->whereRaw(
                "FIND_IN_SET(?, REPLACE(nakshatra, ' ', ''))",
                [$nakshatra]
            )
            ->orderBy('datetime')
            ->skip(1) // 2nd occurrence preferred
            ->first()
            ?? \App\Models\Panchang::whereYear('datetime', '=', $month->year)
                ->whereMonth('datetime', '=', $month->month)
                ->whereRaw(
                    "FIND_IN_SET(?, REPLACE(nakshatra, ' ', ''))",
                    [$nakshatra]
                )
                ->orderBy('datetime')
                ->first();
    }

    /* =========================================================
     |  CALCULATE VALID DATES (ALL PERIODS)
     ========================================================= */
    private function getValidDates($booking, array $devotee): array
    {
        $dates = [];

        $start = Carbon::parse($booking->booking_date);
        $end = Carbon::parse($booking->booking_end_date ?? $booking->booking_date);

        // 🔹 Load next pooja date ONCE
        $nextPoojaDate = TemplePooja::where('id', $booking->pooja_id)
            ->whereNotNull('next_pooja_perform_date')
            ->value('next_pooja_perform_date');

        $nextPoojaDate = $nextPoojaDate ? Carbon::parse($nextPoojaDate) : null;

        /* =========================
            ONCE
        ========================== */
        if ($booking->period === 'once') {
            if (
                $nextPoojaDate &&
                $nextPoojaDate->year === $start->year &&
                $nextPoojaDate->month === $start->month &&
                $nextPoojaDate >= $start
            ) {
                $dates[] = $nextPoojaDate->toDateString();
            } else {
                $dates[] = $start->toDateString();
            }

            return $dates;
        }

        /* =========================
            DAILY
        ========================== */
        if ($booking->period === 'daily') {
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $dates[] = $cursor->toDateString();
                $cursor->addDay();
            }

            return $dates;
        }

        /* =========================
            YEARLY (🔥 NEXT DATE AWARE)
        ========================== */
        if ($booking->period === 'yearly') {
            $cursor = $start->copy();

            while ($cursor->lte($end)) {

                if (
                    $nextPoojaDate &&
                    $nextPoojaDate->year === $cursor->year &&
                    $nextPoojaDate >= $cursor
                ) {
                    $dates[] = $nextPoojaDate->toDateString();
                } else {
                    $dates[] = $cursor->toDateString();
                }

                $cursor->addYear();
            }

            return $dates;
        }

        /* =========================
            MONTHLY (🔥 NEXT DATE + NAKSHATRA)
        ========================== */
        //   $cursor = $sta
        //   $cursor = $start->copy()->startOfMonth();
        $cursor = $start->copy();
        $endM = $end->copy()->endOfMonth();

        while ($cursor->lte($endM)) {

            // 🔹 Highest priority: next_pooja_perform_date (same month)
            if (
                $nextPoojaDate &&
                $nextPoojaDate->year === $cursor->year &&
                $nextPoojaDate->month === $cursor->month &&
                $nextPoojaDate >= $cursor
            ) {
                $dates[] = $nextPoojaDate->toDateString();
            } else {

                // 🔹 Nakshatra-based lookup
                $panchang = $devotee['nakshatra']
                    ? $this->getNakshatraDateForMonth(
                        $booking->booking_date,
                        $devotee['nakshatra'],
                        $cursor
                    )
                    : null;

                $dates[] = $panchang
                    ? Carbon::parse($panchang->datetime)->toDateString()
                    : $cursor->toDateString();
            }

            $cursor->addMonthNoOverflow();
        }

        return $dates;
    }

    /* =========================================================
     |  REMOVE INVALID TRACKINGS (PAYMENT SAFE)
     ========================================================= */
    private function removeInvalidTrackings($booking, array $devotees)
    {
        foreach ($devotees as $devotee) {
            $validDates = $this->getValidDates($booking, $devotee);

            $trackings = TemplePoojaBookingTracking::where('booking_id', $booking->id)
                ->where('devotee_id', $devotee['id'])
                ->get();

            foreach ($trackings as $track) {
                $date = Carbon::parse($track->pooja_date)->toDateString();

                if (! in_array($date, $validDates)) {
                    if (in_array($track->payment_status, ['done', 'partial'])) {
                        throw new \Exception("Cannot remove paid pooja date: {$date}");
                    }
                    $track->delete();
                }
            }
        }
    }

    /* =========================================================
     |  ADD MISSING TRACKINGS
     ========================================================= */
    private function addMissingTrackings($booking, array $devotees)
    {
        foreach ($devotees as $devotee) {
            foreach ($this->getValidDates($booking, $devotee) as $date) {
                $exists = TemplePoojaBookingTracking::where('booking_id', $booking->id)
                    ->where('devotee_id', $devotee['id'])
                    ->whereDate('pooja_date', $date)
                    ->exists();

                if (! $exists) {
                    TemplePoojaBookingTracking::create([
                        'booking_id' => $booking->id,
                        'devotee_id' => $devotee['id'],
                        'pooja_date' => $date,
                        'paid_amount' => 0,
                        'due_amount' => $booking->pooja_amount,
                        'payment_status' => 'pending',
                        'booking_status' => 'pending',
                    ]);
                }
            }
        }
    }

    /* =========================================================
     |  APPLY PARTIAL PAYMENT (ALL PERIODS)
     ========================================================= */
    private function applyPayment($booking, float $amount)
    {
        $unit = $booking->pooja_amount;

        $trackings = TemplePoojaBookingTracking::where('booking_id', $booking->id)
            ->orderByRaw('devotee_id IS NULL, devotee_id')
            ->orderBy('pooja_date')
            ->get();

        foreach ($trackings as $track) {
            if ($amount <= 0) {
                break;
            }
            if ($track->payment_status === 'done') {
                continue;
            }

            $remaining = $unit - $track->paid_amount;

            if ($amount >= $remaining) {
                $track->update([
                    'paid_amount' => $unit,
                    'due_amount' => 0,
                    'payment_status' => 'done',
                ]);
                $amount -= $remaining;
            } else {
                $track->update([
                    'paid_amount' => $track->paid_amount + $amount,
                    'due_amount' => $unit - ($track->paid_amount + $amount),
                    'payment_status' => 'partial',
                ]);
                break;
            }
        }
    }

    /* =========================================================
     |  STORE BOOKING
     ========================================================= */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = Validator::make($request->all(), [
                'temple_id' => 'required|exists:temples,id',
                'pooja_id' => 'required|exists:temple_poojas,id',
                'member_id' => 'nullable|exists:members,id',
                'booking_date' => 'required|date',
                'booking_end_date' => 'nullable|date|after_or_equal:booking_date',
                'period' => 'required|in:once,daily,monthly,yearly',
                'pooja_amount' => 'required|numeric|min:0',
                'pooja_amount_receipt' => 'nullable|numeric|min:0',
                'pooja_amount_remaining' => 'nullable|numeric|min:0',
                'pooja_amount_total_received' => 'nullable|numeric|min:0',
                'booking_time_slot' => 'nullable|string|max:50',
                'payment_mode' => 'nullable|in:cash,online,upi,card',

            ])->validate();

            $bookingData = $validated;
            $bookingData['booking_number'] = 'BKG-'.strtoupper(Str::random(8));
            $bookingData['payment_status'] = $validated['payment_status'] ?? 'pending';
            $bookingData['booking_status'] = $validated['booking_status'] ?? 'pending';
            $booking = TemplePoojaBooking::create($bookingData);

            $devotees = $this->resolveDevotees($request, $validated['temple_id']);

            $this->addMissingTrackings($booking, $devotees);

            if (! empty($validated['pooja_amount_receipt'])) {
                PaymentDetail::create([
                    'temple_id' => $validated['temple_id'],
                    'booking_id' => $booking->id,
                    'payment' => $validated['pooja_amount_receipt'],
                    'payment_mode' => $validated['payment_mode'] ?? null,
                    'type' => 'credit',
                    'source' => 'pooja',
                    'payment_date' => now(),
                ]);
                $this->applyPayment($booking, $validated['pooja_amount_receipt']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Booking created successfully',
                'data' => $booking->load('trackings'),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     |  UPDATE BOOKING
     ========================================================= */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $booking = TemplePoojaBooking::lockForUpdate()->findOrFail($id);

            $validated = Validator::make($request->all(), [
                'temple_id' => 'required|exists:temples,id',
                'booking_date' => 'sometimes|date',
                'booking_end_date' => 'sometimes|date|after_or_equal:booking_date',
                'period' => 'nullable|in:once,daily,monthly,yearly',
                'pooja_amount_receipt' => 'nullable|numeric|min:0',
                'remarks' => 'nullable|string',
            ])->validate();

            $booking->update($validated);

            $devotees = TemplePoojaBookingTracking::where('booking_id', $booking->id)
                ->select('devotee_id')
                ->distinct()
                ->get()
                ->map(
                    fn ($r) => $r->devotee_id
                        ? ['id' => $r->devotee_id, 'nakshatra' => optional(Devotee::find($r->devotee_id))->nakshatra]
                        : ['id' => null, 'nakshatra' => null]
                )->toArray();

            if (isset($validated['booking_date']) || isset($validated['booking_end_date']) || isset($validated['period'])) {
                $this->removeInvalidTrackings($booking, $devotees);
                $this->addMissingTrackings($booking, $devotees);
            }

            if (! empty($validated['pooja_amount_receipt'])) {
                PaymentDetail::create([
                    'temple_id' => $validated['temple_id'],
                    'booking_id' => $booking->id,
                    'payment' => $validated['pooja_amount_receipt'],
                    'type' => 'credit',
                    'source' => 'pooja',
                    'payment_date' => now(),
                ]);
                $this->applyPayment($booking, $validated['pooja_amount_receipt']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Booking updated successfully',
                'data' => $booking->load('trackings'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Soft delete (move to trash)
     */
    public function destroy($id)
    {
        $booking = TemplePoojaBooking::find($id);

        if (! $booking) {
            return response()->json(['status' => false, 'error' => 'Booking not found'], 404);
        }

        $booking->delete();

        TemplePoojaBookingTracking::where('booking_id', $id)
            ->where('booking_status', '!=', 'completed')
            ->update([
                'booking_status' => 'cancelled',
                'updated_at' => now(),
            ]);

        return response()->json(['status' => true, 'message' => 'Booking deleted successfully']);
    }

    /**
     * Get trashed (soft deleted) records.
     */
    public function trashed(Request $request)
    {
        $query = TemplePoojaBooking::onlyTrashed();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        $bookings = $query->get();

        return response()->json(['status' => true, 'data' => $bookings]);
    }

    /**
     * Restore soft deleted record.
     */
    public function restore($id)
    {
        $booking = TemplePoojaBooking::onlyTrashed()->find($id);

        if (! $booking) {
            return response()->json(['status' => false, 'error' => 'Booking not found or not deleted'], 404);
        }

        $booking->restore();

        return response()->json(['status' => true, 'message' => 'Booking restored successfully']);
    }

    /**
     * Permanently delete record.
     */
    public function forceDelete($id)
    {
        $booking = TemplePoojaBooking::onlyTrashed()->find($id);

        if (! $booking) {
            return response()->json(['status' => false, 'error' => 'Booking not found'], 404);
        }

        $booking->forceDelete();

        return response()->json(['status' => true, 'message' => 'Booking permanently deleted']);
    }
}
