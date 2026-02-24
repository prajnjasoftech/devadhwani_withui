<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PendingPoojaController extends Controller
{
    public function index(Request $request)
    {
        // 🔹 Pagination
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        // 🔹 Base query
        $query = DB::table('temple_pooja_bookings_tracking as t')
            ->join('temple_pooja_bookings as b', 'b.id', '=', 't.booking_id')
            ->join('devotees as d', 'd.id', '=', 'b.devotee_id')
            ->join('temple_poojas as p', 'p.id', '=', 'b.pooja_id')
            ->where('t.payment_status', 'pending')
            ->select(
                'd.id as devotee_id',
                'd.devotee_name',
                'd.devotee_phone',
                'b.id as booking_id',
                'b.booking_date as pooja_date',
                'b.temple_id',
                'p.pooja_name',
                DB::raw('IFNULL(p.amount, 0) as amount')
            );

        // ✅ Temple filter (NEW)
        if ($request->filled('temple_id')) {
            $query->where('b.temple_id', $request->temple_id);
        }

        // ✅ Pooja filter
        if ($request->filled('pooja_id')) {
            $query->where('b.pooja_id', $request->pooja_id);
        }

        // ✅ Date filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('b.booking_date', [
                $request->start_date,
                $request->end_date,
            ]);
        }

        // 🔹 Fetch rows
        $rows = $query->orderBy('d.id')->get();

        // 🔹 Group by devotee
        $grouped = $rows->groupBy('devotee_id')->map(function ($items) {

            return [
                'devotee_name' => $items->first()->devotee_name,
                'devotee_phone' => $items->first()->devotee_phone,
                'total_pending_poojas' => $items->count(),
                'total_pending_amount' => $items->sum('amount'),
                'pooja_wise_summary' => $items->map(function ($row) {
                    return [
                        'booking_id' => $row->booking_id,
                        'pooja_name' => $row->pooja_name,
                        'pooja_date' => $row->pooja_date,
                        'amount' => $row->amount,
                    ];
                })->values(),
            ];

        })->values();

        // 🔹 Manual pagination (for grouped results)
        $total = $grouped->count();
        $results = $grouped
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        $paginator = new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Pending pooja summary fetched successfully',
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],

        ]);
    }
}
