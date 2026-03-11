<?php

namespace Tests\Feature\Web;

use App\Models\PaymentDetail;
use App\Models\Temple;
use App\Models\TemplePooja;
use App\Models\TemplePoojaBooking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportWebTest extends TestCase
{
    use RefreshDatabase;

    protected Temple $temple;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temple = Temple::factory()->create();
    }

    /** @test */
    public function it_requires_authentication_to_access_reports()
    {
        $response = $this->get('/reports');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_can_display_reports_index_page()
    {
        $response = $this->actingAs($this->temple, 'web')
            ->get('/reports');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Report/Index')
                ->has('filters')
                ->has('summary')
                ->has('poojaDetails')
                ->has('expenseDetails')
            );
    }

    /** @test */
    public function it_defaults_to_today_date_filter()
    {
        $response = $this->actingAs($this->temple, 'web')
            ->get('/reports');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Report/Index')
                ->where('filters.start_date', now()->toDateString())
                ->where('filters.end_date', now()->toDateString())
            );
    }

    /** @test */
    public function it_can_filter_by_date_range()
    {
        $startDate = '2026-03-01';
        $endDate = '2026-03-10';

        $response = $this->actingAs($this->temple, 'web')
            ->get("/reports?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Report/Index')
                ->where('filters.start_date', $startDate)
                ->where('filters.end_date', $endDate)
            );
    }

    /** @test */
    public function it_shows_summary_with_income_and_expense()
    {
        // Create a pooja payment (income)
        PaymentDetail::create([
            'temple_id' => $this->temple->id,
            'payment_date' => now(),
            'payment' => 500.00,
            'source' => 'pooja',
            'type' => 'credit',
            'payment_mode' => 'cash',
        ]);

        // Create a purchase payment (expense)
        PaymentDetail::create([
            'temple_id' => $this->temple->id,
            'payment_date' => now(),
            'payment' => 100.00,
            'source' => 'purchase',
            'type' => 'debit',
            'payment_mode' => 'cash',
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/reports');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Report/Index')
                ->where('summary.total_income', fn ($value) => $value == 500)
                ->where('summary.total_expense', fn ($value) => $value == 100)
                ->where('summary.net_amount', fn ($value) => $value == 400)
            );
    }

    /** @test */
    public function it_only_shows_data_for_authenticated_temple()
    {
        $otherTemple = Temple::factory()->create();

        // Create payment for other temple
        PaymentDetail::create([
            'temple_id' => $otherTemple->id,
            'payment_date' => now(),
            'payment' => 1000.00,
            'source' => 'pooja',
            'type' => 'credit',
            'payment_mode' => 'cash',
        ]);

        // Create payment for our temple
        PaymentDetail::create([
            'temple_id' => $this->temple->id,
            'payment_date' => now(),
            'payment' => 200.00,
            'source' => 'pooja',
            'type' => 'credit',
            'payment_mode' => 'cash',
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/reports');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Report/Index')
                ->where('summary.total_income', fn ($value) => $value == 200)
            );
    }

    /** @test */
    public function it_excludes_data_outside_date_range()
    {
        // Create payment inside date range
        PaymentDetail::create([
            'temple_id' => $this->temple->id,
            'payment_date' => '2026-03-05 10:00:00',
            'payment' => 300.00,
            'source' => 'pooja',
            'type' => 'credit',
            'payment_mode' => 'cash',
        ]);

        // Create payment outside date range
        PaymentDetail::create([
            'temple_id' => $this->temple->id,
            'payment_date' => '2026-02-15 10:00:00',
            'payment' => 500.00,
            'source' => 'pooja',
            'type' => 'credit',
            'payment_mode' => 'cash',
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/reports?start_date=2026-03-01&end_date=2026-03-10');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Report/Index')
                ->where('summary.total_income', fn ($value) => $value == 300)
            );
    }

    /** @test */
    public function it_returns_zero_when_no_payments_exist()
    {
        $response = $this->actingAs($this->temple, 'web')
            ->get('/reports');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Report/Index')
                ->where('summary.total_income', 0)
                ->where('summary.total_expense', 0)
                ->where('summary.net_amount', 0)
                ->where('summary.total_bookings', 0)
            );
    }
}
