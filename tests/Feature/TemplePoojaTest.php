<?php

namespace Tests\Feature;

use App\Models\Temple;
use App\Models\TemplePooja;
use App\Models\TemplePoojaBooking;
use App\Models\TemplePoojaBookingTracking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplePoojaTest extends TestCase
{
    use RefreshDatabase;

    protected Temple $temple;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temple = Temple::factory()->create();
        $this->token = $this->temple->createToken('test_token')->plainTextToken;
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/temple-poojas');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_list_poojas()
    {
        TemplePooja::factory()->count(5)->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/temple-poojas?temple_id='.$this->temple->id);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);
    }

    /** @test */
    public function it_can_create_a_pooja()
    {
        $data = [
            'temple_id' => $this->temple->id,
            'pooja_name' => 'Test Pooja',
            'period' => 'monthly',
            'amount' => 500,
            'next_pooja_perform_date' => now()->addDays(10)->format('Y-m-d'),
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/temple-poojas', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Temple Pooja created successfully.',
            ]);

        $this->assertDatabaseHas('temple_poojas', [
            'pooja_name' => 'Test Pooja',
            'period' => 'monthly',
        ]);
    }

    /** @test */
    public function it_can_update_a_pooja()
    {
        $pooja = TemplePooja::factory()->create([
            'temple_id' => $this->temple->id,
            'pooja_name' => 'Original Pooja',
            'period' => 'monthly',
            'next_pooja_perform_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response = $this->withToken($this->token)
            ->putJson('/api/temple-poojas/'.$pooja->id, [
                'pooja_name' => 'Updated Pooja',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Temple Pooja updated successfully.',
            ]);

        $this->assertDatabaseHas('temple_poojas', [
            'id' => $pooja->id,
            'pooja_name' => 'Updated Pooja',
        ]);
    }

    /** @test */
    public function it_updates_tracking_when_next_pooja_date_changes()
    {
        // Create pooja with initial date
        $initialDate = now()->addDays(5)->format('Y-m-d');
        $pooja = TemplePooja::factory()->create([
            'temple_id' => $this->temple->id,
            'period' => 'monthly',
            'next_pooja_perform_date' => $initialDate,
        ]);

        // Create a booking for this pooja
        $booking = TemplePoojaBooking::factory()->create([
            'temple_id' => $this->temple->id,
            'pooja_id' => $pooja->id,
            'period' => 'monthly',
            'booking_date' => now()->format('Y-m-d'),
            'booking_end_date' => now()->addMonths(6)->format('Y-m-d'),
            'booking_status' => 'pending',
            'payment_status' => 'pending',
            'service_charge' => 0,
        ]);

        // Create tracking record for the initial date
        $tracking = TemplePoojaBookingTracking::create([
            'booking_id' => $booking->id,
            'pooja_date' => $initialDate,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
        ]);

        // Update the pooja's next date
        $newDate = now()->addDays(15)->format('Y-m-d');
        $response = $this->withToken($this->token)
            ->putJson('/api/temple-poojas/'.$pooja->id, [
                'next_pooja_perform_date' => $newDate,
            ]);

        $response->assertStatus(200);

        // Verify tracking was updated
        $this->assertDatabaseHas('temple_pooja_bookings_tracking', [
            'id' => $tracking->id,
            'pooja_date' => $newDate,
        ]);
    }

    /** @test */
    public function it_updates_next_pending_tracking_after_pooja_completed()
    {
        // Scenario: Pooja done on March 5th, next date set to March 28th
        $march5 = now()->startOfMonth()->addDays(4)->format('Y-m-d');
        $march28 = now()->startOfMonth()->addDays(27)->format('Y-m-d');
        $april15 = now()->startOfMonth()->addMonth()->addDays(14)->format('Y-m-d');

        $pooja = TemplePooja::factory()->create([
            'temple_id' => $this->temple->id,
            'period' => 'monthly',
            'next_pooja_perform_date' => $march5,
        ]);

        $booking = TemplePoojaBooking::factory()->create([
            'temple_id' => $this->temple->id,
            'pooja_id' => $pooja->id,
            'period' => 'monthly',
            'booking_date' => now()->startOfMonth()->format('Y-m-d'),
            'booking_end_date' => now()->addMonths(6)->format('Y-m-d'),
            'booking_status' => 'pending',
            'payment_status' => 'pending',
            'service_charge' => 0,
        ]);

        // March tracking - already completed
        TemplePoojaBookingTracking::create([
            'booking_id' => $booking->id,
            'pooja_date' => $march5,
            'payment_status' => 'done',
            'booking_status' => 'completed',
        ]);

        // April tracking - pending (this should be updated)
        $aprilTracking = TemplePoojaBookingTracking::create([
            'booking_id' => $booking->id,
            'pooja_date' => $april15,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
        ]);

        // Update pooja's next date to March 28th
        $response = $this->withToken($this->token)
            ->putJson('/api/temple-poojas/'.$pooja->id, [
                'next_pooja_perform_date' => $march28,
            ]);

        $response->assertStatus(200);

        // April's tracking should now be March 28th
        $this->assertDatabaseHas('temple_pooja_bookings_tracking', [
            'id' => $aprilTracking->id,
            'pooja_date' => $march28,
        ]);
    }

    /** @test */
    public function it_extends_booking_end_date_when_new_date_exceeds_it()
    {
        $pooja = TemplePooja::factory()->create([
            'temple_id' => $this->temple->id,
            'period' => 'monthly',
            'next_pooja_perform_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $bookingEndDate = now()->addDays(10)->format('Y-m-d');
        $booking = TemplePoojaBooking::factory()->create([
            'temple_id' => $this->temple->id,
            'pooja_id' => $pooja->id,
            'period' => 'monthly',
            'booking_date' => now()->format('Y-m-d'),
            'booking_end_date' => $bookingEndDate,
            'booking_status' => 'pending',
            'payment_status' => 'pending',
            'service_charge' => 0,
        ]);

        TemplePoojaBookingTracking::create([
            'booking_id' => $booking->id,
            'pooja_date' => now()->addDays(5)->format('Y-m-d'),
            'payment_status' => 'pending',
            'booking_status' => 'pending',
        ]);

        // Set new date beyond booking_end_date
        $newDate = now()->addDays(20)->format('Y-m-d');
        $response = $this->withToken($this->token)
            ->putJson('/api/temple-poojas/'.$pooja->id, [
                'next_pooja_perform_date' => $newDate,
            ]);

        $response->assertStatus(200);

        // Booking end date should be extended
        $this->assertDatabaseHas('temple_pooja_bookings', [
            'id' => $booking->id,
            'booking_end_date' => $newDate,
        ]);
    }

    /** @test */
    public function it_does_not_include_temple_relation_in_list_response()
    {
        TemplePooja::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/temple-poojas?temple_id='.$this->temple->id);

        $response->assertStatus(200);

        // Verify temple relation is not eagerly loaded (performance optimization)
        $firstPooja = $response->json('data.data.0');
        $this->assertArrayNotHasKey('temple', $firstPooja);
        $this->assertArrayNotHasKey('member', $firstPooja);
    }

    /** @test */
    public function it_can_soft_delete_a_pooja()
    {
        $pooja = TemplePooja::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson('/api/temple-poojas/'.$pooja->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Temple Pooja soft deleted successfully.',
            ]);

        $this->assertSoftDeleted('temple_poojas', ['id' => $pooja->id]);
    }

    /** @test */
    public function it_can_restore_a_soft_deleted_pooja()
    {
        $pooja = TemplePooja::factory()->create([
            'temple_id' => $this->temple->id,
        ]);
        $pooja->delete();

        $response = $this->withToken($this->token)
            ->postJson('/api/temple-poojas/'.$pooja->id.'/restore');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Temple Pooja restored successfully.',
            ]);

        $this->assertDatabaseHas('temple_poojas', [
            'id' => $pooja->id,
            'deleted_at' => null,
        ]);
    }
}
