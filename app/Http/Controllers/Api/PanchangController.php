<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Panchang;
use App\Services\PanchangService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PanchangController extends Controller
{
    public function show(Request $request, PanchangService $service, $date = null)
    {
        $latitude = $request->latitude ?? 10.5270099;
        $longitude = $request->longitude ?? 76.214621;
        $timezone = $request->timezone ?? 'Asia/Kolkata';
        $ayanamsa = $request->ayanamsa ?? '1';

        if ($date == null) {
            $date = $request->date ?? now()->format('Y-m-d');
        } else {
            $date = Carbon::parse($date)->toDateString();
        }

        // Check if already cached
        $existing = Panchang::whereDate('datetime', $date)->first();

        if ($existing) {
            return response()->json([
                'status' => true,
                'message' => 'Panchang fetched from database cache',
                'data' => $existing,
            ]);
        }

        // Fetch and save using service
        $panchang = $service->fetchAndSave($date, $latitude, $longitude, $timezone, $ayanamsa);

        if (! $panchang) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch panchang data',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Panchang data fetched and saved successfully',
            'data' => $panchang,
        ]);
    }

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

    public function yearlyRecord(Request $request, PanchangService $service)
    {
        $year = $request->year ?? now()->year;

        $startDate = Carbon::create($year, 5, 1);
        $endDate = Carbon::create($year, 6, 30);

        $results = [];

        while ($startDate->lte($endDate)) {
            $date = $startDate->format('Y-m-d');

            $panchang = $service->getForDate($date);

            $results[] = [
                'date' => $date,
                'source' => $panchang && $panchang->wasRecentlyCreated ? 'api' : 'database',
                'data' => $panchang,
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
