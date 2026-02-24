<?php

namespace Tests\Unit;

use App\Models\Temple;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TempleModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_temple()
    {
        $temple = Temple::create([
            'temple_name' => 'Test Temple',
            'temple_address' => '123 Temple Street',
            'phone' => '+919876543210',
        ]);

        $this->assertDatabaseHas('temples', [
            'id' => $temple->id,
            'temple_name' => 'Test Temple',
        ]);
    }

    /** @test */
    public function it_can_create_api_tokens()
    {
        $temple = Temple::factory()->create();

        $token = $temple->createToken('test_token');

        $this->assertNotNull($token->plainTextToken);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $temple->id,
            'tokenable_type' => Temple::class,
            'name' => 'test_token',
        ]);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $temple = new Temple;
        $fillable = $temple->getFillable();

        $this->assertContains('temple_name', $fillable);
        $this->assertContains('temple_address', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('temple_logo', $fillable);
        $this->assertContains('database_name', $fillable);
    }

    /** @test */
    public function it_returns_null_for_temple_logo_base64_when_no_logo()
    {
        $temple = Temple::factory()->create([
            'temple_logo' => null,
        ]);

        $this->assertNull($temple->temple_logo_base64);
    }

    /** @test */
    public function it_hides_remember_token_in_serialization()
    {
        $temple = Temple::factory()->create();
        $array = $temple->toArray();

        $this->assertArrayNotHasKey('remember_token', $array);
    }

    /** @test */
    public function it_does_not_auto_append_temple_logo_base64()
    {
        $temple = Temple::factory()->create();
        $array = $temple->toArray();

        // temple_logo_base64 should NOT be auto-appended for performance
        $this->assertArrayNotHasKey('temple_logo_base64', $array);
    }

    /** @test */
    public function it_can_explicitly_append_temple_logo_base64()
    {
        $temple = Temple::factory()->create([
            'temple_logo' => null,
        ]);

        // Explicitly append the attribute
        $temple->append('temple_logo_base64');
        $array = $temple->toArray();

        // Now it should be present
        $this->assertArrayHasKey('temple_logo_base64', $array);
        $this->assertNull($array['temple_logo_base64']);
    }
}
