<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function templeProfile(): Response
    {
        $temple = auth()->user();

        // Explicitly append logo only for profile page
        $temple->append('temple_logo_base64');

        return Inertia::render('Temple/Profile', [
            'temple' => $temple,
        ]);
    }

    public function updateTempleProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'temple_name' => 'required|string|max:255',
            'temple_address' => 'nullable|string|max:500',
            'temple_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $temple = auth()->user();

        $data = [
            'temple_name' => $request->temple_name,
            'temple_address' => $request->temple_address,
        ];

        if ($request->hasFile('temple_logo')) {
            $logoPath = $request->file('temple_logo')->store('temples', 'public');
            $data['temple_logo'] = basename($logoPath);
        }

        $temple->update($data);

        return redirect()->route('temple.profile')->with('success', 'Temple profile updated successfully.');
    }

    public function index(): Response
    {
        $temple = auth()->user();
        $templeId = $temple->id ?? 1;

        // Transaction summary for today
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        // Total booking amount (Pooja income)
        $totalBookingAmount = DB::table('payment_details')
            ->where('temple_id', $templeId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('source', 'pooja')
            ->where('type', 'credit')
            ->sum('payment');

        // Total expense amount (Purchase)
        $totalExpenseAmount = DB::table('payment_details')
            ->where('temple_id', $templeId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('source', 'purchase')
            ->where('type', 'debit')
            ->sum('payment');

        // Total pooja count today
        $totalPoojaCount = DB::table('payment_details')
            ->where('temple_id', $templeId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('source', 'pooja')
            ->whereNotNull('booking_id')
            ->distinct('booking_id')
            ->count('booking_id');

        // Pooja-wise summary
        $poojaWiseSummary = DB::table('payment_details as t')
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
        $expenseWiseSummary = DB::table('payment_details as t')
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

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalDevotees' => \App\Models\Devotee::count(),
                'todayBookings' => \App\Models\TemplePoojaBooking::whereDate('created_at', today())->count(),
                'monthlyRevenue' => \App\Models\PaymentDetail::whereMonth('payment_date', '=', now()->month)
                    ->whereYear('payment_date', '=', now()->year)
                    ->sum('payment'),
                'pendingPoojas' => \App\Models\TemplePoojaBooking::where('booking_status', 'pending')->count(),
            ],
            'transactionSummary' => [
                'totalBookingAmount' => number_format($totalBookingAmount, 2, '.', ''),
                'totalExpenseAmount' => number_format($totalExpenseAmount, 2, '.', ''),
                'totalPoojaCount' => $totalPoojaCount,
                'poojaWiseSummary' => $poojaWiseSummary,
                'expenseWiseSummary' => $expenseWiseSummary,
            ],
            'recentBookings' => \App\Models\TemplePoojaBooking::with(['pooja', 'devotees'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'booking_number' => $booking->booking_number,
                        'devotee_name' => $booking->devotees->first()?->devotee_name ?? 'N/A',
                        'pooja_name' => $booking->pooja->pooja_name ?? 'N/A',
                        'booking_date' => $booking->booking_date,
                        'status' => $booking->booking_status,
                    ];
                }),
            'panchang' => \App\Models\Panchang::whereDate('datetime', today())->first(),
        ]);
    }
}
