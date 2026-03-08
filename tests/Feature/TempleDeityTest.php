<?php

namespace Tests\Feature;

use App\Models\Temple;
use App\Models\TempleDeity;
use App\Models\TemplePooja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TempleDeityTest extends TestCase
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
        $response = $this->getJson('/api/temple-deities');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_list_deities()
    {
        TempleDeity::factory()->count(5)->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/temple-deities?temple_id='.$this->temple->id);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);
    }

    /** @test */
    public function it_can_create_a_deity()
    {
        $data = [
            'temple_id' => $this->temple->id,
            'name' => 'Lord Ganesha',
            'description' => 'The elephant-headed god',
            'is_active' => true,
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/temple-deities', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Temple Deity created successfully.',
            ]);

        $this->assertDatabaseHas('temple_deities', [
            'name' => 'Lord Ganesha',
            'temple_id' => $this->temple->id,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating()
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/temple-deities', [
                'temple_id' => $this->temple->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_can_show_a_deity()
    {
        $deity = TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/temple-deities/'.$deity->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'id' => $deity->id,
                    'name' => $deity->name,
                ],
            ]);
    }

    /** @test */
    public function it_can_update_a_deity()
    {
        $deity = TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
            'name' => 'Original Name',
        ]);

        $response = $this->withToken($this->token)
            ->putJson('/api/temple-deities/'.$deity->id, [
                'name' => 'Updated Name',
                'description' => 'Updated description',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Temple Deity updated successfully.',
            ]);

        $this->assertDatabaseHas('temple_deities', [
            'id' => $deity->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function it_can_soft_delete_a_deity()
    {
        $deity = TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson('/api/temple-deities/'.$deity->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Temple Deity soft deleted successfully.',
            ]);

        $this->assertSoftDeleted('temple_deities', ['id' => $deity->id]);
    }

    /** @test */
    public function it_can_restore_a_soft_deleted_deity()
    {
        $deity = TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
        ]);
        $deity->delete();

        $response = $this->withToken($this->token)
            ->postJson('/api/temple-deities/'.$deity->id.'/restore');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Temple Deity restored successfully.',
            ]);

        $this->assertDatabaseHas('temple_deities', [
            'id' => $deity->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function it_can_force_delete_a_deity()
    {
        $deity = TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
        ]);
        $deity->delete();

        $response = $this->withToken($this->token)
            ->deleteJson('/api/temple-deities/'.$deity->id.'/force');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Temple Deity permanently deleted.',
            ]);

        $this->assertDatabaseMissing('temple_deities', ['id' => $deity->id]);
    }

    /** @test */
    public function it_can_list_trashed_deities()
    {
        $deity = TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
        ]);
        $deity->delete();

        $response = $this->withToken($this->token)
            ->getJson('/api/temple-deities/trashed/list?temple_id='.$this->temple->id);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);
    }

    /** @test */
    public function it_can_assign_deity_to_pooja()
    {
        $deity = TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $poojaData = [
            'temple_id' => $this->temple->id,
            'pooja_name' => 'Test Pooja',
            'period' => 'monthly',
            'amount' => 500,
            'deity_id' => $deity->id,
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/temple-poojas', $poojaData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('temple_poojas', [
            'pooja_name' => 'Test Pooja',
            'deity_id' => $deity->id,
        ]);
    }

    /** @test */
    public function deity_relationship_is_loaded_with_pooja()
    {
        $deity = TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $pooja = TemplePooja::factory()->create([
            'temple_id' => $this->temple->id,
            'deity_id' => $deity->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/temple-poojas/'.$pooja->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.deity.id', $deity->id)
            ->assertJsonPath('data.deity.name', $deity->name);
    }
}
