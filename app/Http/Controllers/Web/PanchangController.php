<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Panchang;
use App\Services\PanchangService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PanchangController extends Controller
{
    public function index(): Response
    {
        $month = (int) request('month', now()->month);
        $year = (int) request('year', now()->year);

        // Get panchang data for the selected month
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $panchangData = Panchang::whereBetween('datetime', [$startDate, $endDate])
            ->orderBy('datetime')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->datetime)->format('Y-m-d');
            });

        // Build calendar days
        $calendarDays = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $panchang = $panchangData->get($dateKey);

            $calendarDays[] = [
                'date' => $dateKey,
                'day' => $currentDate->day,
                'dayOfWeek' => $currentDate->dayOfWeek,
                'isToday' => $currentDate->isToday(),
                'panchang' => $panchang ? [
                    'id' => $panchang->id,
                    'tithi' => $panchang->tithi,
                    'nakshatra' => $panchang->nakshatra,
                    'yoga' => $panchang->yoga,
                    'karana' => $panchang->karana,
                    'sunrise' => $panchang->sunrise,
                    'sunset' => $panchang->sunset,
                    'day_name' => $panchang->day_name,
                ] : null,
            ];

            $currentDate->addDay();
        }

        // Today's panchang for quick view
        $todayPanchang = Panchang::whereDate('datetime', today())->first();

        return Inertia::render('Panchang/Index', [
            'calendarDays' => $calendarDays,
            'currentMonth' => $month,
            'currentYear' => $year,
            'monthName' => Carbon::create($year, $month, 1)->format('F Y'),
            'todayPanchang' => $todayPanchang,
            'firstDayOfWeek' => $startDate->dayOfWeek,
        ]);
    }

    public function fetch(Request $request, PanchangService $service): JsonResponse
    {
        $date = $request->date;

        if (! $date) {
            return response()->json([
                'status' => false,
                'message' => 'Date is required',
            ], 400);
        }

        // Use service to get or fetch panchang
        $panchang = $service->getForDate($date);

        if (! $panchang) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch panchang data',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => $panchang->wasRecentlyCreated ? 'Panchang fetched from API' : 'Panchang fetched from database',
            'data' => [
                'id' => $panchang->id,
                'tithi' => $panchang->tithi,
                'nakshatra' => $panchang->nakshatra,
                'yoga' => $panchang->yoga,
                'karana' => $panchang->karana,
                'sunrise' => $panchang->sunrise,
                'sunset' => $panchang->sunset,
                'day_name' => $panchang->day_name,
            ],
        ]);
    }

    public function show($date): Response
    {
        $panchang = Panchang::whereDate('datetime', $date)->first();

        if (! $panchang) {
            abort(404, 'Panchang not found for this date');
        }

        return Inertia::render('Panchang/Show', [
            'panchang' => $panchang,
            'date' => $date,
        ]);
    }
}
