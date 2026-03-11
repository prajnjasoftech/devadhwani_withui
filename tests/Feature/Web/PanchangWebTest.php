<?php

namespace Tests\Feature\Web;

use App\Models\Panchang;
use App\Models\Temple;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanchangWebTest extends TestCase
{
    use RefreshDatabase;

    protected Temple $temple;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temple = Temple::factory()->create();
    }

    /** @test */
    public function it_requires_authentication_to_access_panchang()
    {
        $response = $this->get('/panchang');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_can_display_panchang_index_page()
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

        $response = $this->actingAs($this->temple, 'web')
            ->get('/panchang');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Panchang/Index')
                ->has('calendarDays')
                ->has('currentMonth')
                ->has('currentYear')
            );
    }

    /** @test */
    public function it_can_navigate_to_next_month()
    {
        $nextMonth = now()->month + 1;
        $year = now()->year;

        // Handle year rollover
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $year++;
        }

        $response = $this->actingAs($this->temple, 'web')
            ->get("/panchang?month={$nextMonth}&year={$year}");

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Panchang/Index')
                ->where('currentMonth', $nextMonth)
                ->where('currentYear', $year)
            );
    }

    /** @test */
    public function it_can_navigate_to_previous_month()
    {
        $prevMonth = now()->month - 1;
        $year = now()->year;

        // Handle year rollover
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $year--;
        }

        $response = $this->actingAs($this->temple, 'web')
            ->get("/panchang?month={$prevMonth}&year={$year}");

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Panchang/Index')
                ->where('currentMonth', $prevMonth)
                ->where('currentYear', $year)
            );
    }

    /** @test */
    public function it_handles_month_as_string_parameter()
    {
        // This tests the fix for string concatenation bug
        // Previously "3" + 1 would become "31" instead of 4
        $response = $this->actingAs($this->temple, 'web')
            ->get('/panchang?month=3&year=2026');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Panchang/Index')
                ->where('currentMonth', 3)
                ->where('currentYear', 2026)
            );
    }

    /** @test */
    public function it_defaults_to_current_month_when_no_params()
    {
        $response = $this->actingAs($this->temple, 'web')
            ->get('/panchang');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Panchang/Index')
                ->where('currentMonth', now()->month)
                ->where('currentYear', now()->year)
            );
    }

    /** @test */
    public function it_shows_panchang_data_for_dates_in_calendar()
    {
        $date = now()->startOfMonth()->addDays(5);

        Panchang::create([
            'datetime' => $date->toDateString(),
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

        $response = $this->actingAs($this->temple, 'web')
            ->get('/panchang');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Panchang/Index')
                ->has('calendarDays')
            );
    }
}
