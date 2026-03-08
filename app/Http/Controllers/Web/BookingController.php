<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Devotee;
use App\Models\PaymentDetail;
use App\Models\TempleDeity;
use App\Models\TemplePooja;
use App\Models\TemplePoojaBooking;
use App\Models\TemplePoojaBookingTracking;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $temple = auth()->user();

        $query = TemplePoojaBooking::with(['pooja', 'devotee'])
            ->where('temple_id', $temple->id);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                    ->orWhereHas('devotee', function ($dq) use ($search) {
                        $dq->where('devotee_name', 'like', "%{$search}%")
                            ->orWhere('devotee_phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('booking_status', $request->status);
        }

        if ($request->has('pooja_id') && $request->pooja_id) {
            $query->where('pooja_id', $request->pooja_id);
        }

        // Default to today's date if no date filter provided and not explicitly showing all
        $dateFilter = $request->date;
        if (! $request->has('date') && ! $request->has('show_all')) {
            $dateFilter = Carbon::today()->toDateString();
        }

        if ($dateFilter) {
            $query->whereDate('booking_date', $dateFilter);
        }

        $bookings = $query->orderByDesc('id')->paginate(10)->withQueryString();
        $poojas = TemplePooja::where('temple_id', $temple->id)->get(['id', 'pooja_name']);

        return Inertia::render('Booking/Index', [
            'bookings' => $bookings,
            'poojas' => $poojas,
            'filters' => [
                'search' => $request->search ?? '',
                'status' => $request->status ?? '',
                'pooja_id' => $request->pooja_id ?? '',
                'date' => $dateFilter ?? '',
                'show_all' => $request->has('show_all'),
            ],
        ]);
    }

    public function create(): Response
    {
        $temple = auth()->user();
        $poojas = TemplePooja::where('temple_id', $temple->id)->get(['id', 'pooja_name', 'amount', 'period', 'next_pooja_perform_date', 'deity_id']);
        $devotees = Devotee::where('temple_id', $temple->id)->get(['id', 'devotee_name', 'devotee_phone', 'nakshatra']);
        $deities = TempleDeity::where('temple_id', $temple->id)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Booking/Create', [
            'poojas' => $poojas,
            'devotees' => $devotees,
            'deities' => $deities,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $temple = auth()->user();

        $validated = $request->validate([
            'pooja_id' => 'required|exists:temple_poojas,id',
            'deity_id' => 'nullable|exists:temple_deities,id',
            'devotees' => 'required|array|min:1',
            'devotees.*.devotee_id' => 'nullable|exists:devotees,id',
            'devotees.*.devotee_name' => 'required_without:devotees.*.devotee_id|nullable|string|max:150',
            'devotees.*.devotee_phone' => 'nullable|string|max:15',
            'devotees.*.nakshatra' => 'nullable|string|max:50',
            'booking_date' => 'required|date',
            'booking_end_date' => 'nullable|date|after_or_equal:booking_date',
            'period' => 'required|in:once,daily,monthly,yearly',
            'pooja_amount' => 'required|numeric|min:0',
            'pooja_amount_receipt' => 'nullable|numeric|min:0',
            'payment_mode' => 'nullable|in:cash,online,upi,card',
            'remarks' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $bookingsCreated = 0;
            $totalAmount = $validated['pooja_amount'];
            $totalReceived = $validated['pooja_amount_receipt'] ?? 0;
            $devoteeCount = count($validated['devotees']);
            $perDevoteeAmount = $devoteeCount > 0 ? $totalAmount / $devoteeCount : $totalAmount;
            $perDevoteeReceived = $devoteeCount > 0 ? $totalReceived / $devoteeCount : $totalReceived;

            // Generate one receipt number for all bookings in this batch
            $receiptNumber = 'RCP-'.date('Ymd').'-'.strtoupper(Str::random(4));

            foreach ($validated['devotees'] as $index => $devoteeData) {
                // Resolve devotee
                $devoteeId = $devoteeData['devotee_id'] ?? null;
                if (! $devoteeId && ! empty($devoteeData['devotee_name'])) {
                    $devotee = Devotee::firstOrCreate(
                        [
                            'temple_id' => $temple->id,
                            'devotee_phone' => $devoteeData['devotee_phone'] ?? null,
                        ],
                        [
                            'devotee_name' => $devoteeData['devotee_name'],
                            'nakshatra' => $devoteeData['nakshatra'] ?? null,
                        ]
                    );
                    $devoteeId = $devotee->id;
                }

                $booking = TemplePoojaBooking::create([
                    'temple_id' => $temple->id,
                    'pooja_id' => $validated['pooja_id'],
                    'deity_id' => $validated['deity_id'] ?? null,
                    'devotee_id' => $devoteeId,
                    'booking_number' => 'BKG-'.strtoupper(Str::random(8)),
                    'receipt_number' => $receiptNumber,
                    'booking_date' => $validated['booking_date'],
                    'booking_end_date' => $validated['booking_end_date'] ?? $validated['booking_date'],
                    'period' => $validated['period'],
                    'pooja_amount' => $perDevoteeAmount,
                    'pooja_amount_receipt' => $perDevoteeReceived,
                    'pooja_amount_remaining' => $perDevoteeAmount - $perDevoteeReceived,
                    'pooja_amount_total_received' => $perDevoteeReceived,
                    'payment_status' => $perDevoteeReceived >= $perDevoteeAmount ? 'success' : 'pending',
                    'payment_mode' => $validated['payment_mode'] ?? null,
                    'booking_status' => 'pending',
                    'remarks' => $validated['remarks'] ?? null,
                ]);

                // Create tracking record
                TemplePoojaBookingTracking::create([
                    'booking_id' => $booking->id,
                    'devotee_id' => $devoteeId,
                    'pooja_date' => $validated['booking_date'],
                    'paid_amount' => $perDevoteeReceived,
                    'due_amount' => $perDevoteeAmount - $perDevoteeReceived,
                    'payment_status' => $perDevoteeReceived >= $perDevoteeAmount ? 'done' : 'pending',
                    'booking_status' => 'pending',
                ]);

                // Record payment if received
                if ($perDevoteeReceived > 0) {
                    PaymentDetail::create([
                        'temple_id' => $temple->id,
                        'booking_id' => $booking->id,
                        'payment' => $perDevoteeReceived,
                        'payment_mode' => $validated['payment_mode'] ?? 'cash',
                        'type' => 'credit',
                        'source' => 'pooja',
                        'payment_date' => now(),
                    ]);
                }

                $bookingsCreated++;
            }

            DB::commit();

            $message = $bookingsCreated > 1
                ? "Created {$bookingsCreated} bookings. Receipt: {$receiptNumber}"
                : "Booking created. Receipt: {$receiptNumber}";

            return redirect()->route('bookings.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to create booking: '.$e->getMessage());
        }
    }

    public function show($id): Response
    {
        $temple = auth()->user();
        $booking = TemplePoojaBooking::with(['pooja', 'devotee', 'trackings'])
            ->where('temple_id', $temple->id)
            ->findOrFail($id);

        return Inertia::render('Booking/Show', [
            'booking' => $booking,
        ]);
    }

    public function edit($id): Response
    {
        $temple = auth()->user();
        $booking = TemplePoojaBooking::with(['pooja', 'devotee', 'deity'])
            ->where('temple_id', $temple->id)
            ->findOrFail($id);

        $poojas = TemplePooja::where('temple_id', $temple->id)->get(['id', 'pooja_name', 'amount', 'period', 'deity_id']);
        $devotees = Devotee::where('temple_id', $temple->id)->get(['id', 'devotee_name', 'devotee_phone', 'nakshatra']);
        $deities = TempleDeity::where('temple_id', $temple->id)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Booking/Edit', [
            'booking' => $booking,
            'poojas' => $poojas,
            'devotees' => $devotees,
            'deities' => $deities,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $booking = TemplePoojaBooking::where('temple_id', $temple->id)->findOrFail($id);

        $validated = $request->validate([
            'booking_date' => 'required|date',
            'booking_end_date' => 'nullable|date|after_or_equal:booking_date',
            'booking_status' => 'required|in:pending,confirmed,completed,cancelled',
            'payment_status' => 'required|in:pending,success,failed,refunded',
            'payment_mode' => 'nullable|in:cash,online,upi,card',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $oldPaymentStatus = $booking->payment_status;
        $remainingAmount = $booking->pooja_amount_remaining ?? ($booking->pooja_amount - ($booking->pooja_amount_total_received ?? 0));

        DB::beginTransaction();
        try {
            // If marking as paid and there's remaining amount, record the payment
            if ($validated['payment_status'] === 'success' && $oldPaymentStatus !== 'success' && $remainingAmount > 0) {
                PaymentDetail::create([
                    'temple_id' => $temple->id,
                    'booking_id' => $booking->id,
                    'payment' => $remainingAmount,
                    'payment_mode' => $validated['payment_mode'] ?? 'cash',
                    'type' => 'credit',
                    'source' => 'pooja',
                    'payment_date' => now(),
                ]);

                // Update booking payment fields
                $validated['pooja_amount_receipt'] = $booking->pooja_amount;
                $validated['pooja_amount_remaining'] = 0;
                $validated['pooja_amount_total_received'] = $booking->pooja_amount;

                // Update tracking record
                TemplePoojaBookingTracking::where('booking_id', $booking->id)
                    ->update([
                        'paid_amount' => $booking->pooja_amount,
                        'due_amount' => 0,
                        'payment_status' => 'done',
                    ]);
            }

            // If marking as refunded and was paid, record the refund
            if ($validated['payment_status'] === 'refunded' && $oldPaymentStatus === 'success') {
                $paidAmount = $booking->pooja_amount_total_received ?? $booking->pooja_amount;
                if ($paidAmount > 0) {
                    PaymentDetail::create([
                        'temple_id' => $temple->id,
                        'booking_id' => $booking->id,
                        'payment' => $paidAmount,
                        'payment_mode' => $validated['payment_mode'] ?? 'cash',
                        'type' => 'debit',
                        'source' => 'pooja_refund',
                        'payment_date' => now(),
                    ]);
                }
            }

            $booking->update($validated);
            DB::commit();

            return redirect()->route('bookings.index')->with('success', 'Booking updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to update booking: '.$e->getMessage());
        }
    }

    public function destroy($id): RedirectResponse
    {
        $temple = auth()->user();
        $booking = TemplePoojaBooking::where('temple_id', $temple->id)->findOrFail($id);

        $booking->delete();

        TemplePoojaBookingTracking::where('booking_id', $id)
            ->where('booking_status', '!=', 'completed')
            ->update(['booking_status' => 'cancelled']);

        return redirect()->route('bookings.index')->with('success', 'Booking cancelled successfully.');
    }

    public function updateStatus(Request $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $booking = TemplePoojaBooking::where('temple_id', $temple->id)->findOrFail($id);

        $validated = $request->validate([
            'booking_status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $booking->update(['booking_status' => $validated['booking_status']]);

        // Update tracking status if completed
        if ($validated['booking_status'] === 'completed') {
            TemplePoojaBookingTracking::where('booking_id', $id)
                ->update(['booking_status' => 'completed']);
        }

        return redirect()->back()->with('success', 'Booking status updated.');
    }

    public function recordPayment(Request $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $booking = TemplePoojaBooking::where('temple_id', $temple->id)->findOrFail($id);

        $pendingAmount = $booking->pooja_amount - ($booking->pooja_amount_total_received ?? 0);

        $validated = $request->validate([
            'amount' => "required|numeric|min:1|max:{$pendingAmount}",
            'payment_mode' => 'required|in:cash,online,upi,card',
        ]);

        DB::beginTransaction();

        try {
            $newTotalReceived = ($booking->pooja_amount_total_received ?? 0) + $validated['amount'];
            $newRemaining = $booking->pooja_amount - $newTotalReceived;

            // Update booking
            $booking->update([
                'pooja_amount_receipt' => $newTotalReceived,
                'pooja_amount_total_received' => $newTotalReceived,
                'pooja_amount_remaining' => $newRemaining,
                'payment_status' => $newRemaining <= 0 ? 'success' : 'pending',
                'payment_mode' => $validated['payment_mode'],
            ]);

            // Record payment
            PaymentDetail::create([
                'temple_id' => $temple->id,
                'booking_id' => $booking->id,
                'payment' => $validated['amount'],
                'payment_mode' => $validated['payment_mode'],
                'type' => 'credit',
                'source' => 'pooja',
                'payment_date' => now(),
            ]);

            // Update tracking record
            TemplePoojaBookingTracking::where('booking_id', $booking->id)
                ->update([
                    'paid_amount' => $newTotalReceived,
                    'due_amount' => $newRemaining,
                    'payment_status' => $newRemaining <= 0 ? 'done' : 'pending',
                ]);

            DB::commit();

            return redirect()->back()->with('success', "Payment of ₹{$validated['amount']} recorded successfully.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to record payment: '.$e->getMessage());
        }
    }

    public function processRefund(Request $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $booking = TemplePoojaBooking::where('temple_id', $temple->id)->findOrFail($id);

        $validated = $request->validate([
            'payment_mode' => 'required|in:cash,online,upi,card',
        ]);

        $refundAmount = $booking->pooja_amount_total_received ?? 0;

        if ($refundAmount <= 0) {
            return redirect()->back()->with('error', 'No amount to refund.');
        }

        DB::beginTransaction();

        try {
            // Update booking
            $booking->update([
                'pooja_amount_receipt' => 0,
                'pooja_amount_total_received' => 0,
                'pooja_amount_remaining' => $booking->pooja_amount,
                'payment_status' => 'refunded',
            ]);

            // Record refund
            PaymentDetail::create([
                'temple_id' => $temple->id,
                'booking_id' => $booking->id,
                'payment' => $refundAmount,
                'payment_mode' => $validated['payment_mode'],
                'type' => 'debit',
                'source' => 'pooja_refund',
                'payment_date' => now(),
            ]);

            // Update tracking record
            TemplePoojaBookingTracking::where('booking_id', $booking->id)
                ->update([
                    'paid_amount' => 0,
                    'due_amount' => $booking->pooja_amount,
                    'payment_status' => 'pending',
                ]);

            DB::commit();

            return redirect()->back()->with('success', "Refund of ₹{$refundAmount} processed successfully.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to process refund: '.$e->getMessage());
        }
    }
}
