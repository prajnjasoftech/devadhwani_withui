<?php

namespace Tests\Feature;

use App\Models\Panchang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanchangTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_panchang_for_today()
    {
        Panchang::create([
            'datetime' => now()->toDateString(),
            'latitude' => 10.5270099,
            'longitude' => 76.214621,
            'timezone' => 'Asia/Kolkata',
            'tithi' => 'Purnima',
            'nakshatra' => 'Ashwini',
            'yoga' => 'Siddhi',
            'karana' => 'Bava',
            'sunrise' => '06:00',
            'sunset' => '18:00',
        ]);

        $response = $this->getJson('/api/panchang');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_get_panchang_for_specific_date()
    {
        $date = '2025-10-15';

        Panchang::create([
            'datetime' => $date,
            'latitude' => 10.5270099,
            'longitude' => 76.214621,
            'timezone' => 'Asia/Kolkata',
            'tithi' => 'Ekadashi',
            'nakshatra' => 'Rohini',
            'yoga' => 'Shubha',
            'karana' => 'Kaulava',
            'sunrise' => '06:15',
            'sunset' => '18:30',
        ]);

        $response = $this->getJson('/api/panchang/listDate/'.$date);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_list_panchang_records()
    {
        Panchang::create([
            'datetime' => now()->toDateString(),
            'latitude' => 10.5270099,
            'longitude' => 76.214621,
            'timezone' => 'Asia/Kolkata',
            'tithi' => 'Amavasya',
            'nakshatra' => 'Bharani',
            'yoga' => 'Vyatipata',
            'karana' => 'Taitila',
            'sunrise' => '06:00',
            'sunset' => '18:00',
        ]);

        $response = $this->getJson('/api/panchang/list');

        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * @group external-api
     */
    public function it_can_get_yearly_panchang_records()
    {
        $this->markTestSkipped('This test requires external API with credits.');
    }
}
