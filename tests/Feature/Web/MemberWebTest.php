<?php

namespace Tests\Feature\Web;

use App\Models\Member;
use App\Models\Role;
use App\Models\Temple;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberWebTest extends TestCase
{
    use RefreshDatabase;

    protected Temple $temple;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temple = Temple::factory()->create();
    }

    /** @test */
    public function it_requires_authentication_to_access_members()
    {
        $response = $this->get('/members');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_can_display_members_index_page()
    {
        Member::factory()->count(3)->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/members');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Member/Index')
                ->has('members.data', 3)
            );
    }

    /** @test */
    public function it_can_search_members()
    {
        Member::factory()->create([
            'temple_id' => $this->temple->id,
            'name' => 'John Doe',
        ]);

        Member::factory()->create([
            'temple_id' => $this->temple->id,
            'name' => 'Jane Smith',
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/members?search=John');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Member/Index')
                ->has('members.data', 1)
            );
    }

    /** @test */
    public function it_can_display_create_member_page()
    {
        Role::factory()->count(2)->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/members/create');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Member/Create')
                ->has('roles', 2)
            );
    }

    /** @test */
    public function it_can_store_a_new_member()
    {
        $role = Role::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->post('/members', [
                'name' => 'Test Member',
                'phone' => '+919876543210',
                'email' => 'test@example.com',
                'role_id' => $role->id,
            ]);

        $response->assertRedirect('/members')
            ->assertSessionHas('success', 'Member created successfully.');

        $this->assertDatabaseHas('members', [
            'name' => 'Test Member',
            'phone' => '+919876543210',
            'temple_id' => $this->temple->id,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_member()
    {
        $response = $this->actingAs($this->temple, 'web')
            ->post('/members', []);

        $response->assertSessionHasErrors(['name', 'phone']);
    }

    /** @test */
    public function it_validates_unique_phone()
    {
        $existingMember = Member::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->post('/members', [
                'name' => 'Another Member',
                'phone' => $existingMember->phone,
            ]);

        $response->assertSessionHasErrors(['phone']);
    }

    /** @test */
    public function it_can_display_edit_member_page()
    {
        $member = Member::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        Role::factory()->count(2)->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get("/members/{$member->id}/edit");

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Member/Edit')
                ->has('member')
                ->has('roles', 2)
            );
    }

    /** @test */
    public function it_can_update_a_member()
    {
        $member = Member::factory()->create([
            'temple_id' => $this->temple->id,
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->put("/members/{$member->id}", [
                'name' => 'Updated Name',
                'phone' => $member->phone,
            ]);

        $response->assertRedirect('/members')
            ->assertSessionHas('success', 'Member updated successfully.');

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function it_can_delete_a_member()
    {
        $member = Member::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->delete("/members/{$member->id}");

        $response->assertRedirect('/members')
            ->assertSessionHas('success', 'Member deleted successfully.');

        $this->assertSoftDeleted('members', ['id' => $member->id]);
    }

    /** @test */
    public function it_cannot_access_other_temples_members()
    {
        $otherTemple = Temple::factory()->create();
        $member = Member::factory()->create([
            'temple_id' => $otherTemple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get("/members/{$member->id}/edit");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_paginates_members_list()
    {
        Member::factory()->count(15)->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/members');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Member/Index')
                ->has('members.data', 10)
                ->where('members.total', 15)
            );
    }
}
