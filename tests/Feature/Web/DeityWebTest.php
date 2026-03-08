<?php

namespace Tests\Feature\Web;

use App\Models\Temple;
use App\Models\TempleDeity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeityWebTest extends TestCase
{
    use RefreshDatabase;

    protected Temple $temple;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temple = Temple::factory()->create();
    }

    /** @test */
    public function it_requires_authentication_to_access_deities()
    {
        $response = $this->get('/deities');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_can_display_deities_index_page()
    {
        TempleDeity::factory()->count(3)->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/deities');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Deity/Index')
                ->has('deities.data', 3)
            );
    }

    /** @test */
    public function it_can_search_deities()
    {
        TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
            'name' => 'Lord Ganesha',
        ]);

        TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
            'name' => 'Lord Shiva',
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/deities?search=Ganesha');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Deity/Index')
                ->has('deities.data', 1)
            );
    }

    /** @test */
    public function it_can_filter_deities_by_status()
    {
        TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
            'is_active' => true,
        ]);

        TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/deities?status=active');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Deity/Index')
                ->has('deities.data', 1)
            );
    }

    /** @test */
    public function it_can_display_create_deity_page()
    {
        $response = $this->actingAs($this->temple, 'web')
            ->get('/deities/create');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Deity/Create')
            );
    }

    /** @test */
    public function it_can_store_a_new_deity()
    {
        $response = $this->actingAs($this->temple, 'web')
            ->post('/deities', [
                'name' => 'Lord Ganesha',
                'description' => 'The elephant-headed god',
                'is_active' => true,
            ]);

        $response->assertRedirect('/deities')
            ->assertSessionHas('success', 'Deity created successfully.');

        $this->assertDatabaseHas('temple_deities', [
            'name' => 'Lord Ganesha',
            'temple_id' => $this->temple->id,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_deity()
    {
        $response = $this->actingAs($this->temple, 'web')
            ->post('/deities', []);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function it_can_display_edit_deity_page()
    {
        $deity = TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get("/deities/{$deity->id}/edit");

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Deity/Edit')
                ->has('deity')
            );
    }

    /** @test */
    public function it_can_update_a_deity()
    {
        $deity = TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->put("/deities/{$deity->id}", [
                'name' => 'Updated Name',
                'description' => 'Updated description',
                'is_active' => true,
            ]);

        $response->assertRedirect('/deities')
            ->assertSessionHas('success', 'Deity updated successfully.');

        $this->assertDatabaseHas('temple_deities', [
            'id' => $deity->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function it_can_delete_a_deity()
    {
        $deity = TempleDeity::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->delete("/deities/{$deity->id}");

        $response->assertRedirect('/deities')
            ->assertSessionHas('success', 'Deity deleted successfully.');

        $this->assertSoftDeleted('temple_deities', ['id' => $deity->id]);
    }

    /** @test */
    public function it_cannot_access_other_temples_deities()
    {
        $otherTemple = Temple::factory()->create();
        $deity = TempleDeity::factory()->create([
            'temple_id' => $otherTemple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get("/deities/{$deity->id}/edit");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_paginates_deities_list()
    {
        TempleDeity::factory()->count(15)->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/deities');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Deity/Index')
                ->has('deities.data', 10)
                ->where('deities.total', 15)
            );
    }

    /** @test */
    public function it_defaults_is_active_to_true_when_creating()
    {
        $response = $this->actingAs($this->temple, 'web')
            ->post('/deities', [
                'name' => 'Test Deity',
            ]);

        $response->assertRedirect('/deities');

        $this->assertDatabaseHas('temple_deities', [
            'name' => 'Test Deity',
            'is_active' => true,
        ]);
    }
}
