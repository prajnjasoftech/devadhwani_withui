<?php

namespace Tests\Feature;

use App\Models\Devotee;
use App\Models\Temple;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevoteeTest extends TestCase
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
        $response = $this->getJson('/api/devotees');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_list_devotees()
    {
        Devotee::factory()->count(5)->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/devotees?temple_id='.$this->temple->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ])
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function it_can_search_devotees()
    {
        Devotee::factory()->create([
            'temple_id' => $this->temple->id,
            'devotee_name' => 'Ravi Kumar',
        ]);

        Devotee::factory()->create([
            'temple_id' => $this->temple->id,
            'devotee_name' => 'Sita Devi',
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/devotees?search=Ravi');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_create_a_devotee()
    {
        $data = [
            'temple_id' => $this->temple->id,
            'devotee_name' => 'Test Devotee',
            'devotee_phone' => '+919876543210',
            'nakshatra' => 'Ashwini',
            'address' => '123 Test Street',
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/devotees', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Devotee created successfully.',
            ]);

        $this->assertDatabaseHas('devotees', [
            'devotee_name' => 'Test Devotee',
            'devotee_phone' => '+919876543210',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_devotee()
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/devotees', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['status', 'error', 'errors']);
    }

    /** @test */
    public function it_can_show_a_devotee()
    {
        $devotee = Devotee::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/devotees/'.$devotee->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => ['id' => $devotee->id],
            ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_devotee()
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/devotees/99999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'error' => 'Devotee not found.',
            ]);
    }

    /** @test */
    public function it_can_update_a_devotee()
    {
        $devotee = Devotee::factory()->create([
            'temple_id' => $this->temple->id,
            'devotee_name' => 'Original Name',
        ]);

        $response = $this->withToken($this->token)
            ->putJson('/api/devotees/'.$devotee->id, [
                'devotee_name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Devotee updated successfully.',
            ]);

        $this->assertDatabaseHas('devotees', [
            'id' => $devotee->id,
            'devotee_name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function it_can_soft_delete_a_devotee()
    {
        $devotee = Devotee::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson('/api/devotees/'.$devotee->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Devotee soft deleted successfully.',
            ]);

        $this->assertSoftDeleted('devotees', ['id' => $devotee->id]);
    }

    /** @test */
    public function it_can_restore_a_soft_deleted_devotee()
    {
        $devotee = Devotee::factory()->create([
            'temple_id' => $this->temple->id,
        ]);
        $devotee->delete();

        $response = $this->withToken($this->token)
            ->postJson('/api/devotees/'.$devotee->id.'/restore');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Devotee restored successfully.',
            ]);

        $this->assertDatabaseHas('devotees', [
            'id' => $devotee->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function it_can_force_delete_a_devotee()
    {
        $devotee = Devotee::factory()->create([
            'temple_id' => $this->temple->id,
        ]);
        $devotee->delete();

        $response = $this->withToken($this->token)
            ->deleteJson('/api/devotees/'.$devotee->id.'/force');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Devotee permanently deleted.',
            ]);

        $this->assertDatabaseMissing('devotees', ['id' => $devotee->id]);
    }

    /** @test */
    public function it_paginates_devotee_list()
    {
        Devotee::factory()->count(25)->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/devotees?temple_id='.$this->temple->id.'&per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 25);
    }

    /** @test */
    public function it_can_fetch_temples_for_dropdown()
    {
        // Create additional temples for dropdown
        Temple::factory()->count(3)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/temples?fields=dropdown');

        $response->assertStatus(200);

        // Verify lightweight response (only id and temple_name)
        $firstTemple = $response->json('data.0');
        $this->assertArrayHasKey('id', $firstTemple);
        $this->assertArrayHasKey('temple_name', $firstTemple);
        $this->assertArrayNotHasKey('temple_address', $firstTemple);
        $this->assertArrayNotHasKey('temple_logo_base64', $firstTemple);
    }
}
