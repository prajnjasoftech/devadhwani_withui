<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $temple = auth()->user();
        $templeId = $temple->id;

        // Get dates or fallback to today
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::today()->startOfDay();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::today()->endOfDay();

        // Base query with date filter
        $baseQuery = DB::table('payment_details')
            ->where('temple_id', $templeId)
            ->whereBetween('payment_date', [$startDate, $endDate]);

        // Total booking amount (Pooja income)
        $totalBookingAmount = (clone $baseQuery)
            ->where('source', 'pooja')
            ->where('type', 'credit')
            ->sum('payment');

        // Total expense amount (Purchase)
        $totalExpenseAmount = (clone $baseQuery)
            ->where('source', 'purchase')
            ->where('type', 'debit')
            ->sum('payment');

        // Total pooja count
        $totalPoojaCount = (clone $baseQuery)
            ->where('source', 'pooja')
            ->whereNotNull('booking_id')
            ->distinct('booking_id')
            ->count('booking_id');

        // Pooja-wise summary
        $poojaDetails = DB::table('payment_details as t')
            ->join('temple_pooja_bookings as b', 'b.id', '=', 't.booking_id')
            ->join('temple_poojas as p', 'p.id', '=', 'b.pooja_id')
            ->select(
                'b.pooja_id',
                'p.pooja_name',
                DB::raw('COUNT(DISTINCT t.booking_id) as total_bookings'),
                DB::raw('SUM(t.payment) as total_amount')
            )
            ->where('t.source', 'pooja')
            ->where('t.temple_id', $templeId)
            ->where('t.type', 'credit')
            ->whereBetween('t.payment_date', [$startDate, $endDate])
            ->groupBy('b.pooja_id', 'p.pooja_name')
            ->orderByDesc('total_amount')
            ->get();

        // Expense-wise summary (Item wise)
        $expenseDetails = DB::table('payment_details as t')
            ->join('purchases as pu', 'pu.id', '=', 't.source_id')
            ->join('items as i', 'i.id', '=', 'pu.item_id')
            ->select(
                'pu.item_id',
                'i.item_name',
                DB::raw('SUM(pu.quantity) as total_quantity'),
                DB::raw('SUM(t.payment) as total_expense')
            )
            ->where('t.source', 'purchase')
            ->where('t.type', 'debit')
            ->where('t.temple_id', $templeId)
            ->whereBetween('t.payment_date', [$startDate, $endDate])
            ->groupBy('pu.item_id', 'i.item_name')
            ->orderByDesc('total_expense')
            ->get();

        return Inertia::render('Report/Index', [
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => [
                'total_income' => $totalBookingAmount,
                'total_expense' => $totalExpenseAmount,
                'net_amount' => $totalBookingAmount - $totalExpenseAmount,
                'total_bookings' => $totalPoojaCount,
            ],
            'poojaDetails' => $poojaDetails,
            'expenseDetails' => $expenseDetails,
        ]);
    }
}
