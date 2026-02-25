<?php

namespace Tests\Feature\Web;

use App\Models\Member;
use App\Models\Role;
use App\Models\Temple;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleWebTest extends TestCase
{
    use RefreshDatabase;

    protected Temple $temple;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temple = Temple::factory()->create();
    }

    /** @test */
    public function it_requires_authentication_to_access_roles()
    {
        $response = $this->get('/roles');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_can_display_roles_index_page()
    {
        Role::factory()->count(3)->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/roles');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Role/Index')
                ->has('roles.data', 3)
            );
    }

    /** @test */
    public function it_can_search_roles()
    {
        Role::factory()->create([
            'temple_id' => $this->temple->id,
            'role_name' => 'Administrator',
        ]);

        Role::factory()->create([
            'temple_id' => $this->temple->id,
            'role_name' => 'Receptionist',
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/roles?search=Admin');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Role/Index')
                ->has('roles.data', 1)
            );
    }

    /** @test */
    public function it_can_display_create_role_page()
    {
        $response = $this->actingAs($this->temple, 'web')
            ->get('/roles/create');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Role/Create')
                ->has('availablePermissions')
            );
    }

    /** @test */
    public function it_can_store_a_new_role()
    {
        $response = $this->actingAs($this->temple, 'web')
            ->post('/roles', [
                'role_name' => 'Manager',
                'role' => ['dashboard', 'devotees', 'bookings'],
            ]);

        $response->assertRedirect('/roles')
            ->assertSessionHas('success', 'Role created successfully.');

        $this->assertDatabaseHas('roles', [
            'role_name' => 'Manager',
            'temple_id' => $this->temple->id,
        ]);

        $role = Role::where('role_name', 'Manager')->first();
        $this->assertEquals(['dashboard', 'devotees', 'bookings'], $role->role);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_role()
    {
        $response = $this->actingAs($this->temple, 'web')
            ->post('/roles', []);

        $response->assertSessionHasErrors(['role_name']);
    }

    /** @test */
    public function it_validates_unique_role_name_per_temple()
    {
        Role::factory()->create([
            'temple_id' => $this->temple->id,
            'role_name' => 'Manager',
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->post('/roles', [
                'role_name' => 'Manager',
            ]);

        $response->assertSessionHasErrors(['role_name']);
    }

    /** @test */
    public function it_can_display_edit_role_page()
    {
        $role = Role::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get("/roles/{$role->id}/edit");

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Role/Edit')
                ->has('role')
                ->has('availablePermissions')
            );
    }

    /** @test */
    public function it_can_update_a_role()
    {
        $role = Role::factory()->create([
            'temple_id' => $this->temple->id,
            'role_name' => 'Original Role',
            'role' => ['dashboard'],
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->put("/roles/{$role->id}", [
                'role_name' => 'Updated Role',
                'role' => ['dashboard', 'devotees', 'bookings'],
            ]);

        $response->assertRedirect('/roles')
            ->assertSessionHas('success', 'Role updated successfully.');

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'role_name' => 'Updated Role',
        ]);

        $role->refresh();
        $this->assertEquals(['dashboard', 'devotees', 'bookings'], $role->role);
    }

    /** @test */
    public function it_can_delete_a_role()
    {
        $role = Role::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->delete("/roles/{$role->id}");

        $response->assertRedirect('/roles')
            ->assertSessionHas('success', 'Role deleted successfully.');

        $this->assertSoftDeleted('roles', ['id' => $role->id]);
    }

    /** @test */
    public function it_cannot_delete_role_assigned_to_members()
    {
        $role = Role::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        Member::factory()->create([
            'temple_id' => $this->temple->id,
            'role_id' => $role->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->delete("/roles/{$role->id}");

        $response->assertRedirect('/roles')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    /** @test */
    public function it_cannot_access_other_temples_roles()
    {
        $otherTemple = Temple::factory()->create();
        $role = Role::factory()->create([
            'temple_id' => $otherTemple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get("/roles/{$role->id}/edit");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_paginates_roles_list()
    {
        Role::factory()->count(15)->create([
            'temple_id' => $this->temple->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/roles');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Role/Index')
                ->has('roles.data', 10)
                ->where('roles.total', 15)
            );
    }

    /** @test */
    public function it_shows_members_count_for_each_role()
    {
        $role = Role::factory()->create([
            'temple_id' => $this->temple->id,
        ]);

        Member::factory()->count(3)->create([
            'temple_id' => $this->temple->id,
            'role_id' => $role->id,
        ]);

        $response = $this->actingAs($this->temple, 'web')
            ->get('/roles');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Role/Index')
                ->where('roles.data.0.members_count', 3)
            );
    }
}
