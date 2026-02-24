<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TempleReportController extends Controller
{
    /**
     * List all temple poojas.
     */
    public function index(Request $request)
    {
        // 📌 Get dates or fallback to TODAY
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::today()->startOfDay();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::today()->endOfDay();

        // 🔹 Base query with mandatory date filter
        $baseQuery = DB::table('payment_details')
            ->whereBetween('payment_date', [$startDate, $endDate]);
        // ✅ Temple filter (NEW)
        if ($request->filled('temple_id')) {
            $baseQuery->where('temple_id', $request->temple_id);
        }

        // ✅ 1. Total booking amount (Pooja)
        $totalBookingAmount = (clone $baseQuery)
            ->where('source', 'pooja')
            ->where('type', 'credit')
            ->sum('payment');

        // ✅ 2. Total expense amount (Purchase)
        $totalExpenseAmount = (clone $baseQuery)
            ->where('source', 'purchase')
            ->where('type', 'debit')
            ->sum('payment');

        // ✅ 3. Total pooja count
        $totalPoojaCount = (clone $baseQuery)
            ->where('source', 'pooja')
            ->whereNotNull('booking_id')
            ->distinct('booking_id')
            ->count('booking_id');

        // ✅ 4. Pooja-wise summary
        $temple_id = 1;
        if ($request->filled('temple_id')) {
            $temple_id = $request->temple_id;
        }
        $poojaDetails = DB::table('payment_details as t')
            ->join('temple_pooja_bookings as b', 'b.id', '=', 't.booking_id')
            ->join('temple_poojas as p', 'p.id', '=', 'b.pooja_id')
            ->select(
                'b.pooja_id',
                'p.pooja_name as pooja_name',
                DB::raw('COUNT(DISTINCT t.booking_id) as total_bookings'),
                DB::raw('COUNT(t.id) as total_transactions'),
                DB::raw('SUM(t.payment) as total_amount')
            )
            ->where('t.source', 'pooja')
            ->where('t.temple_id', $temple_id)
            ->where('t.type', 'credit')
            ->whereBetween('t.payment_date', [$startDate, $endDate])
            ->groupBy('b.pooja_id', 'p.pooja_name')
            ->orderByDesc('total_amount')
            ->get();
        // ✅ 5. Expense-wise summary (Purchase)
        // ✅ 5. Expense-wise summary (Item wise)
        $expenseDetails = DB::table('payment_details as t')
            ->join('purchases as pu', 'pu.id', '=', 't.source_id')
            ->join('items as i', 'i.id', '=', 'pu.item_id')
            ->select(
                'pu.item_id',
                'i.item_name',
                DB::raw('SUM(pu.quantity) as total_quantity'),
                DB::raw('COUNT(t.id) as total_transactions'),
                DB::raw('SUM(t.payment) as total_expense')
            )
            ->where('t.source', 'purchase')
            ->where('t.type', 'debit')
            ->where('t.temple_id', $temple_id)
            ->whereBetween('t.payment_date', [$startDate, $endDate])
            ->groupBy('pu.item_id', 'i.item_name')
            ->orderByDesc('total_expense')
            ->get();

        // 📤 API Response
        return response()->json([
            'status' => true,
            'message' => 'Transaction summary fetched successfully',
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'data' => [
                'total_booking_amount' => number_format($totalBookingAmount, 2, '.', ''),
                'total_expense_amount' => number_format($totalExpenseAmount, 2, '.', ''),
                'total_pooja_count' => $totalPoojaCount,
                'pooja_wise_summary' => $poojaDetails,
                'expense_wise_summary' => $expenseDetails,
            ],
        ]);
    }
}
