<?php

namespace App\Console\Commands;

use App\Models\Panchang;
use App\Services\ProkeralaService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PanchangCronFourDays extends Command
{
    protected $signature = 'panchang:generate-four-days {year?}';

    protected $description = 'Generate Panchang for next 4 missing days';

    public function handle(ProkeralaService $service)
    {
        Log::info('Panchang cron started');
        $year = $this->argument('year') ?? now()->year;

        // Get dates already stored
        $existingDates = Panchang::whereYear('datetime', $year)
            ->pluck('datetime')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Start from Jan 1
        $date = Carbon::create($year, 1, 1);
        $generated = 0;

        while ($generated < 4 && $date->year == $year) {

            $dateStr = $date->format('Y-m-d');

            if (! in_array($dateStr, $existingDates)) {
                Log::info('Panchang cron Ended'.$dateStr);
                $this->generateSingleDay($dateStr, $service);
                $generated++;

                // small sleep to respect rate limits
                sleep(1);
            }

            $date->addDay();
        }

        $this->info("Generated {$generated} Panchang day(s)");
        Log::info('Panchang cron Ended');
    }

    private function generateSingleDay(string $date, ProkeralaService $service)
    {
        $dateToStore = $date.' 06:00:00';
        $datetime = Carbon::parse($date.' 06:00:00')->toIso8601String();

        $panchang = $service->getPanchang(
            $datetime,
            10.5270099,
            76.214621,
            'Asia/Kolkata',
            '1'
        );
        try {
            $calendarData = $service->getCalendar($datetime, 'Asia/Kolkata');
            $calendar = $calendarData['data']['calendar_date'];

            $formattedDate = $calendar['month_name']
                .' '.$calendar['id']
                .', '.$calendar['year'];
        } catch (\Exception $e) {
            $this->info("Generated {$date} Panchang day(s)");
            Log::info($e->getMessage());
        }
        $data = $panchang['data'] ?? [];

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
            'datetime' => $dateToStore,
            'latitude' => 10.5270099,
            'longitude' => 76.214621,
            'timezone' => 'Asia/Kolkata',
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

        $saved = Panchang::create($data);

    }
}
