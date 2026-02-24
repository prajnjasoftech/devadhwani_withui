<?php

namespace Tests\Feature;

use App\Models\Temple;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TempleTest extends TestCase
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
    public function it_requires_authentication_to_list_temples()
    {
        $response = $this->getJson('/api/temples');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_list_temples()
    {
        Temple::factory()->count(5)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/temples');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);
    }

    /** @test */
    public function it_can_filter_temples_by_id()
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/temples?temple_id='.$this->temple->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.data.0.id', $this->temple->id);
    }

    /** @test */
    public function it_can_search_temples()
    {
        Temple::factory()->create(['temple_name' => 'Unique Temple Name']);

        $response = $this->withToken($this->token)
            ->getJson('/api/temples?search=Unique');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_update_temple_details()
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/temples/'.$this->temple->id, [
                'temple_name' => 'Updated Temple Name',
                'temple_address' => 'Updated Address',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Temple updated successfully',
            ]);

        $this->assertDatabaseHas('temples', [
            'id' => $this->temple->id,
            'temple_name' => 'Updated Temple Name',
        ]);
    }

    /** @test */
    public function it_can_upload_temple_logo()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/temples/'.$this->temple->id, [
                'temple_logo' => $file,
            ]);

        $response->assertStatus(200);

        $this->temple->refresh();
        $this->assertNotNull($this->temple->temple_logo);
    }

    /** @test */
    public function it_returns_404_for_non_existent_temple()
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/temples/99999', [
                'temple_name' => 'Test',
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Temple not found',
            ]);
    }

    /** @test */
    public function it_paginates_temple_list()
    {
        Temple::factory()->count(25)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/temples?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 10);
    }

    /** @test */
    public function it_returns_lightweight_response_for_dropdown()
    {
        Temple::factory()->count(3)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/temples?fields=dropdown');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'temple_name'],
                ],
            ]);

        // Ensure only id and temple_name are returned (no other fields)
        $firstTemple = $response->json('data.0');
        $this->assertArrayHasKey('id', $firstTemple);
        $this->assertArrayHasKey('temple_name', $firstTemple);
        $this->assertArrayNotHasKey('temple_address', $firstTemple);
        $this->assertArrayNotHasKey('phone', $firstTemple);
        $this->assertArrayNotHasKey('temple_logo', $firstTemple);
        $this->assertArrayNotHasKey('temple_logo_base64', $firstTemple);
    }

    /** @test */
    public function it_does_not_include_temple_logo_base64_in_list_response()
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/temples');

        $response->assertStatus(200);

        // Verify temple_logo_base64 is not auto-appended
        $firstTemple = $response->json('data.data.0');
        $this->assertArrayNotHasKey('temple_logo_base64', $firstTemple);
    }
}
