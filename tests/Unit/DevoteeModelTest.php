<?php

namespace Tests\Unit;

use App\Models\Devotee;
use App\Models\Temple;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevoteeModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_devotee()
    {
        $temple = Temple::factory()->create();

        $devotee = Devotee::create([
            'temple_id' => $temple->id,
            'devotee_name' => 'Test Devotee',
            'devotee_phone' => '+919876543210',
            'nakshatra' => 'Ashwini',
            'address' => '123 Test Street',
        ]);

        $this->assertDatabaseHas('devotees', [
            'id' => $devotee->id,
            'devotee_name' => 'Test Devotee',
        ]);
    }

    /** @test */
    public function it_belongs_to_a_temple()
    {
        $temple = Temple::factory()->create();
        $devotee = Devotee::factory()->create([
            'temple_id' => $temple->id,
        ]);

        $this->assertInstanceOf(Temple::class, $devotee->temple);
        $this->assertEquals($temple->id, $devotee->temple->id);
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $devotee = Devotee::factory()->create();

        $devotee->delete();

        $this->assertSoftDeleted('devotees', ['id' => $devotee->id]);
        $this->assertNotNull(Devotee::withTrashed()->find($devotee->id));
    }

    /** @test */
    public function it_can_be_restored_after_soft_delete()
    {
        $devotee = Devotee::factory()->create();
        $devotee->delete();

        $devotee->restore();

        $this->assertNull($devotee->fresh()->deleted_at);
    }

    /** @test */
    public function it_can_be_force_deleted()
    {
        $devotee = Devotee::factory()->create();
        $id = $devotee->id;

        $devotee->forceDelete();

        $this->assertDatabaseMissing('devotees', ['id' => $id]);
    }

    /** @test */
    public function it_casts_device_created_at_to_datetime()
    {
        $devotee = Devotee::factory()->create([
            'device_created_at' => '2025-01-15 10:30:00',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $devotee->device_created_at);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $devotee = new Devotee;
        $fillable = $devotee->getFillable();

        $this->assertContains('temple_id', $fillable);
        $this->assertContains('devotee_name', $fillable);
        $this->assertContains('devotee_phone', $fillable);
        $this->assertContains('nakshatra', $fillable);
        $this->assertContains('address', $fillable);
    }
}
