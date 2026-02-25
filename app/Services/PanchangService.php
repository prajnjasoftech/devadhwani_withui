<?php

namespace App\Services;

use App\Models\Panchang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PanchangService
{
    protected ProkeralaService $prokeralaService;

    protected float $defaultLatitude = 10.5270099;

    protected float $defaultLongitude = 76.214621;

    protected string $defaultTimezone = 'Asia/Kolkata';

    public function __construct(ProkeralaService $prokeralaService)
    {
        $this->prokeralaService = $prokeralaService;
    }

    /**
     * Get panchang for a date - from cache or fetch from API
     */
    public function getForDate(
        string $date,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $timezone = null,
        string $ayanamsa = '1'
    ): ?Panchang {
        $latitude = $latitude ?? $this->defaultLatitude;
        $longitude = $longitude ?? $this->defaultLongitude;
        $timezone = $timezone ?? $this->defaultTimezone;

        // Check cache first
        $existing = Panchang::whereDate('datetime', $date)->first();

        if ($existing) {
            return $existing;
        }

        // Fetch from API
        return $this->fetchAndSave($date, $latitude, $longitude, $timezone, $ayanamsa);
    }

    /**
     * Fetch panchang from API and save to database
     */
    public function fetchAndSave(
        string $date,
        float $latitude,
        float $longitude,
        string $timezone,
        string $ayanamsa = '1'
    ): ?Panchang {
        $time = now()->format('H:i:s');
        $datetime = "{$date} {$time}";
        $isoDatetime = Carbon::parse($datetime)->toIso8601String();

        // Get calendar data (Malayalam calendar)
        $formattedDate = null;
        $calendarData = null;
        try {
            $calendarData = $this->prokeralaService->getCalendar($date, $timezone);
            $calendar = $calendarData['data']['calendar_date'];
            $formattedDate = $calendar['month_name'].' '.$calendar['day'].', '.$calendar['year'];
        } catch (\Exception $e) {
            Log::info('Calendar API error: '.$e->getMessage());
        }

        // Get panchang data
        try {
            $panchang = $this->prokeralaService->getPanchang($isoDatetime, $latitude, $longitude, $timezone, $ayanamsa);
            $data = $panchang['data'] ?? null;

            if (! $data) {
                return null;
            }

            $panchangData = [
                'datetime' => $datetime,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timezone' => $timezone,
                'day' => $data['vaara'] ?? null,
                'tithi' => collect($data['tithi'] ?? [])->pluck('name')->implode(', '),
                'nakshatra' => collect($data['nakshatra'] ?? [])->pluck('name')->implode(', '),
                'yoga' => collect($data['yoga'] ?? [])->pluck('name')->implode(', '),
                'karana' => collect($data['karana'] ?? [])->pluck('name')->implode(', '),
                'sunrise' => $data['sunrise'] ?? null,
                'sunset' => $data['sunset'] ?? null,
                'raw_data' => $panchang,
                'day_name' => $formattedDate,
                'day_raw' => json_encode($calendarData),
            ];

            return Panchang::create($panchangData);
        } catch (\Exception $e) {
            Log::error('Panchang fetch error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Check if panchang exists for a date
     */
    public function exists(string $date): bool
    {
        return Panchang::whereDate('datetime', $date)->exists();
    }

    /**
     * Get cached panchang (no API call)
     */
    public function getCached(string $date): ?Panchang
    {
        return Panchang::whereDate('datetime', $date)->first();
    }
}
