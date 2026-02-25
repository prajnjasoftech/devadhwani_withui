<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ProkeralaService
{
    protected $clientId;

    protected $clientSecret;

    protected $baseUrl = 'https://api.prokerala.com/v2/';

    public function __construct()
    {
        $this->clientId = config('services.prokerala.client_id');
        $this->clientSecret = config('services.prokerala.client_secret');
    }

    protected function http()
    {
        // Disable SSL verification for local development
        if (app()->environment('local')) {
            return Http::withoutVerifying();
        }

        return Http::withOptions([]);
    }

    protected function getAccessToken()
    {
        $response = $this->http()->asForm()->post('https://api.prokerala.com/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to get access token: '.$response->body());
        }

        return $response->json()['access_token'];
    }

    public function getPanchang($datetime, $latitude, $longitude, $timezone, $ayanamsa = 1)
    {
        $accessToken = $this->getAccessToken();

        $response = $this->http()->withToken($accessToken)->get($this->baseUrl.'astrology/panchang', [
            'datetime' => $datetime,     // e.g. 2025-10-27T06:00:00+05:30
            'latitude' => $latitude,     // e.g. 10.5270099
            'longitude' => $longitude,   // e.g. 76.214621
            'coordinates' => "$latitude,$longitude",
            'timezone' => $timezone,     // e.g. Asia/Kolkata
            'ayanamsa' => $ayanamsa,     // e.g. 1
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch Panchang: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Get Calendar Details
     */
    public function getCalendar(string $date, string $timezone = 'Asia/Kolkata'): array
    {
        $token = $this->getAccessToken();

        $response = $this->http()->withToken($token)
            ->get($this->baseUrl.'calendar', [
                'date' => $date,
                'timezone' => $timezone,
                'calendar' => 'malayalam',
            ]);

        if ($response->failed()) {
            throw new \Exception($response->body());
        }

        return $response->json();
    }
}
