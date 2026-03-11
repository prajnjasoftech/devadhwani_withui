<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrates existing booking data to the new receipt-based structure.
     *
     * @return void
     */
    public function up()
    {
        // Step 1: Get all unique receipt_numbers with their booking data
        $receiptGroups = DB::table('temple_pooja_bookings')
            ->whereNotNull('receipt_number')
            ->select(
                'receipt_number',
                'temple_id',
                'devotee_id',
                'member_id',
                DB::raw('MIN(created_at) as created_at'),
                DB::raw('MIN(booking_date) as receipt_date'),
                DB::raw('SUM(pooja_amount + COALESCE(service_charge, 0)) as total_amount'),
                DB::raw('SUM(COALESCE(pooja_amount_total_received, 0)) as amount_paid')
            )
            ->groupBy('receipt_number', 'temple_id', 'devotee_id', 'member_id')
            ->get();

        foreach ($receiptGroups as $group) {
            // Create receipt
            $netAmount = $group->total_amount;
            $balanceDue = $netAmount - $group->amount_paid;
            $paymentStatus = 'pending';
            if ($group->amount_paid >= $netAmount) {
                $paymentStatus = 'paid';
            } elseif ($group->amount_paid > 0) {
                $paymentStatus = 'partial';
            }

            $receiptId = DB::table('receipts')->insertGetId([
                'temple_id' => $group->temple_id,
                'receipt_number' => $group->receipt_number,
                'devotee_id' => $group->devotee_id,
                'member_id' => $group->member_id,
                'receipt_date' => $group->receipt_date,
                'total_amount' => $group->total_amount,
                'discount' => 0,
                'net_amount' => $netAmount,
                'amount_paid' => $group->amount_paid,
                'balance_due' => max(0, $balanceDue),
                'payment_status' => $paymentStatus,
                'created_at' => $group->created_at,
                'updated_at' => now(),
            ]);

            // Update bookings with receipt_id
            DB::table('temple_pooja_bookings')
                ->where('receipt_number', $group->receipt_number)
                ->update(['receipt_id' => $receiptId]);

            // Migrate existing payments from payment_details to receipt_payments
            $existingPayments = DB::table('payment_details')
                ->join('temple_pooja_bookings', 'temple_pooja_bookings.id', '=', 'payment_details.booking_id')
                ->where('temple_pooja_bookings.receipt_number', $group->receipt_number)
                ->where('payment_details.source', 'pooja')
                ->where('payment_details.type', 'credit')
                ->select(
                    'payment_details.payment',
                    'payment_details.payment_date',
                    'payment_details.payment_mode',
                    'payment_details.member_id'
                )
                ->get();

            // Group payments by date and mode to avoid duplicates from split payments
            $paymentsByKey = [];
            foreach ($existingPayments as $payment) {
                $key = $payment->payment_date . '_' . $payment->payment_mode;
                if (!isset($paymentsByKey[$key])) {
                    $paymentsByKey[$key] = [
                        'receipt_id' => $receiptId,
                        'amount' => 0,
                        'payment_date' => $payment->payment_date,
                        'payment_mode' => $payment->payment_mode ?? 'cash',
                        'member_id' => $payment->member_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $paymentsByKey[$key]['amount'] += $payment->payment;
            }

            foreach ($paymentsByKey as $paymentData) {
                DB::table('receipt_payments')->insert($paymentData);
            }
        }

        // Step 2: Handle orphan bookings (no receipt_number)
        $orphanBookings = DB::table('temple_pooja_bookings')
            ->whereNull('receipt_number')
            ->whereNull('receipt_id')
            ->get();

        foreach ($orphanBookings as $booking) {
            // Generate a receipt number
            $receiptNumber = 'R-' . date('Y', strtotime($booking->created_at)) . '-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT);

            $totalAmount = $booking->pooja_amount + ($booking->service_charge ?? 0);
            $amountPaid = $booking->pooja_amount_total_received ?? 0;
            $balanceDue = $totalAmount - $amountPaid;
            $paymentStatus = 'pending';
            if ($amountPaid >= $totalAmount) {
                $paymentStatus = 'paid';
            } elseif ($amountPaid > 0) {
                $paymentStatus = 'partial';
            }

            $receiptId = DB::table('receipts')->insertGetId([
                'temple_id' => $booking->temple_id,
                'receipt_number' => $receiptNumber,
                'devotee_id' => $booking->devotee_id,
                'member_id' => $booking->member_id,
                'receipt_date' => $booking->booking_date,
                'total_amount' => $totalAmount,
                'discount' => 0,
                'net_amount' => $totalAmount,
                'amount_paid' => $amountPaid,
                'balance_due' => max(0, $balanceDue),
                'payment_status' => $paymentStatus,
                'created_at' => $booking->created_at,
                'updated_at' => now(),
            ]);

            // Update booking
            DB::table('temple_pooja_bookings')
                ->where('id', $booking->id)
                ->update([
                    'receipt_id' => $receiptId,
                    'receipt_number' => $receiptNumber,
                ]);

            // Migrate payment if exists
            $payment = DB::table('payment_details')
                ->where('booking_id', $booking->id)
                ->where('source', 'pooja')
                ->where('type', 'credit')
                ->first();

            if ($payment && $payment->payment > 0) {
                DB::table('receipt_payments')->insert([
                    'receipt_id' => $receiptId,
                    'amount' => $payment->payment,
                    'payment_date' => $payment->payment_date,
                    'payment_mode' => $payment->payment_mode ?? 'cash',
                    'member_id' => $payment->member_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Clear receipt_id from bookings
        DB::table('temple_pooja_bookings')->update(['receipt_id' => null]);

        // Delete all receipt payments
        DB::table('receipt_payments')->truncate();

        // Delete all receipts
        DB::table('receipts')->truncate();
    }
};
