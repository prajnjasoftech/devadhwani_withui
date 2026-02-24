<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Panchang;
use App\Services\ProkeralaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PanchangController extends Controller
{
    public function show(Request $request, ProkeralaService $service, $date = null)
    {
        $latitude = $request->latitude ?? 10.5270099;
        $longitude = $request->longitude ?? 76.214621;
        $timezone = $request->timezone ?? 'Asia/Kolkata';
        // $datetime = now()->toIso8601String();
        $ayanamsa = $request->ayanamsa ?? '1';

        if ($date == null) {
            // 1️⃣ Read dynamic date or fallback to today's date
            $date = $request->date ?? now()->format('Y-m-d');
            $time = now()->format('H:i:s');
            $datetime = "{$date} {$time}";
        } else {
            $date = Carbon::parse($date)->toDateString();
            $time = now()->format('H:i:s');
            $datetime = "{$date} {$time}";
        }
        // 2️⃣ Check if Panchang already exists in DB for this date
        $existing = Panchang::whereDate('datetime', $date)->first();

        if ($existing) {
            return response()->json([
                'status' => true,
                'message' => 'Panchang fetched from database cache',
                'data' => $existing,
            ]);
        }

        try {
            $calendarData = $service->getCalendar($date, $timezone);

            $calendar = $calendarData['data']['calendar_date'];

            $formattedDate = $calendar['month_name']
                .' '.$calendar['id']
                .', '.$calendar['year'];
        } catch (\Exception $e) {

            Log::info($e->getMessage());
        }
        // Convert to ISO format for Prokerala API
        $isoDatetime = \Carbon\Carbon::parse($datetime)->toIso8601String();

        $panchang = $service->getPanchang($isoDatetime, $latitude, $longitude, $timezone, $ayanamsa = '1');
        $data = $panchang['data'] ?? $panchang['data'] ?? null;

        $tithiArray = $data['tithi'] ?? [];
        $tithiNames = collect($tithiArray)
            ->pluck('name')
            ->implode(', ');
        $karanaArray = $data['karana'] ?? [];
        $karanaNames = collect($karanaArray)
            ->pluck('name')
            ->implode(', ');
        $nakshatraArray = $data['nakshatra'] ?? [];
        $nakshatraNames = collect($nakshatraArray)
            ->pluck('name')
            ->implode(', ');
        $yogaArray = $data['yoga'] ?? [];
        $yogaNames = collect($yogaArray)
            ->pluck('name')
            ->implode(', ');
        // Extract key details (adjust according to actual response)
        $data = [
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
            'day_name' => $formattedDate ?? null,
            'day_raw' => json_encode($calendarData ?? null),
        ];

        // Save to database
        $saved = Panchang::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Panchang data fetched and saved successfully',
            'data' => $saved,
            // 'panchang_raw' => $panchang,
        ]);
    }

    // get panchagn by date
    public function listDate($date)
    {
        $panchang = Panchang::whereDate('datetime', $date)->first();

        if (! $panchang) {
            return response()->json([
                'status' => false,
                'message' => 'Panchang not found for the specified date.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $panchang,
        ]);
    }

    // Show single Panchang record
    public function list($id = null)
    {
        if ($id) {
            $panchang = Panchang::find($id);
        } else {
            $panchang = Panchang::all();
        }

        if (! $panchang) {
            return response()->json([
                'status' => false,
                'message' => 'Panchang not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $panchang,
        ]);
    }

    public function yearlyRecord(Request $request, ProkeralaService $service)
    {

        $year = $request->year ?? now()->year;

        $startDate = \Carbon\Carbon::create($year, 5, 1);
        $endDate = \Carbon\Carbon::create($year, 6, 30);

        $results = [];

        while ($startDate->lte($endDate)) {

            $date = $startDate->format('Y-m-d');

            // Check DB cache first
            $existing = Panchang::whereDate('datetime', $date)->first();
            if ($existing) {
                $results[] = [
                    'date' => $date,
                    'source' => 'database',
                    'data' => $existing,
                ];
                $startDate->addDay();

                continue;
            }

            // Generate single day's panchang using internal call
            $response = $this->show($request, $service, $date);

            $results[] = [
                'date' => $date,
                'source' => 'api',
                'data' => $response,
            ];

            $startDate->addDay();
        }

        return response()->json([
            'status' => true,
            'message' => "Panchang generated for year {$year}",
            'results' => $results,
        ]);
    }
}
