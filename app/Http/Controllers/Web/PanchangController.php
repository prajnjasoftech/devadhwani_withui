<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Panchang;
use App\Services\ProkeralaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PanchangController extends Controller
{
    public function index(): Response
    {
        $month = request('month', now()->month);
        $year = request('year', now()->year);

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

    public function fetch(Request $request, ProkeralaService $service): JsonResponse
    {
        $date = $request->date;

        if (! $date) {
            return response()->json([
                'status' => false,
                'message' => 'Date is required',
            ], 400);
        }

        // Check if already exists in DB
        $existing = Panchang::whereDate('datetime', $date)->first();

        if ($existing) {
            return response()->json([
                'status' => true,
                'message' => 'Panchang fetched from database',
                'data' => [
                    'id' => $existing->id,
                    'tithi' => $existing->tithi,
                    'nakshatra' => $existing->nakshatra,
                    'yoga' => $existing->yoga,
                    'karana' => $existing->karana,
                    'sunrise' => $existing->sunrise,
                    'sunset' => $existing->sunset,
                    'day_name' => $existing->day_name,
                ],
            ]);
        }

        // Fetch from API
        try {
            $latitude = 10.5270099;
            $longitude = 76.214621;
            $timezone = 'Asia/Kolkata';
            $time = now()->format('H:i:s');
            $datetime = "{$date} {$time}";

            // Get calendar data
            $formattedDate = null;
            $calendarData = null;
            try {
                $calendarData = $service->getCalendar($date, $timezone);
                $calendar = $calendarData['data']['calendar_date'];
                $formattedDate = $calendar['month_name'].' '.$calendar['day'].', '.$calendar['year'];
            } catch (\Exception $e) {
                Log::info('Calendar API error: '.$e->getMessage());
            }

            // Get panchang data
            $isoDatetime = Carbon::parse($datetime)->toIso8601String();
            $panchang = $service->getPanchang($isoDatetime, $latitude, $longitude, $timezone, '1');
            $data = $panchang['data'] ?? null;

            if (! $data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to fetch panchang data',
                ], 500);
            }

            $tithiNames = collect($data['tithi'] ?? [])->pluck('name')->implode(', ');
            $karanaNames = collect($data['karana'] ?? [])->pluck('name')->implode(', ');
            $nakshatraNames = collect($data['nakshatra'] ?? [])->pluck('name')->implode(', ');
            $yogaNames = collect($data['yoga'] ?? [])->pluck('name')->implode(', ');

            $panchangData = [
                'datetime' => $datetime,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timezone' => $timezone,
                'day' => $data['vaara'] ?? null,
                'tithi' => $tithiNames,
                'nakshatra' => $nakshatraNames,
                'yoga' => $yogaNames,
                'karana' => $karanaNames,
                'sunrise' => $panchang['data']['sunrise'] ?? null,
                'sunset' => $panchang['data']['sunset'] ?? null,
                'raw_data' => $panchang,
                'day_name' => $formattedDate,
                'day_raw' => json_encode($calendarData),
            ];

            $saved = Panchang::create($panchangData);

            return response()->json([
                'status' => true,
                'message' => 'Panchang fetched from API',
                'data' => [
                    'id' => $saved->id,
                    'tithi' => $saved->tithi,
                    'nakshatra' => $saved->nakshatra,
                    'yoga' => $saved->yoga,
                    'karana' => $saved->karana,
                    'sunrise' => $saved->sunrise,
                    'sunset' => $saved->sunset,
                    'day_name' => $saved->day_name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Panchang fetch error: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch panchang: '.$e->getMessage(),
            ], 500);
        }
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
