<?php

namespace Tests\Feature;

use App\Models\AgentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgentCategoryAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::forceCreate([
            'first_name' => ucfirst($role),
            'last_name' => 'Test',
            'email' => $role . '@example.test',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    public function test_user_cannot_access_agent_category_pages(): void
    {
        $user = $this->makeUser('user');
        $category = AgentCategory::create(['name' => 'Catégorie existante']);

        $this->actingAs($user)->get(route('admin.agent-categories.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.agent-categories.create'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.agent-categories.show', $category))->assertForbidden();
        $this->actingAs($user)->get(route('admin.agent-categories.edit', $category))->assertForbidden();
    }

    public function test_user_cannot_create_update_or_delete_agent_categories(): void
    {
        $user = $this->makeUser('user');
        $category = AgentCategory::create(['name' => 'Catégorie existante']);

        $this->actingAs($user)
            ->post(route('admin.agent-categories.store'), ['name' => 'Catégorie pirate'])
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.agent-categories.update', $category), ['name' => 'Renommée'])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('admin.agent-categories.destroy', $category))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.agent-categories.assign-agents', $category), ['agent_ids' => []])
            ->assertForbidden();

        $this->assertDatabaseMissing('agent_categories', ['name' => 'Catégorie pirate']);
        $this->assertDatabaseHas('agent_categories', ['name' => 'Catégorie existante']);
    }

    public function test_user_dashboard_does_not_offer_category_creation(): void
    {
        $user = $this->makeUser('user');

        $this->actingAs($user)
            ->get(route('user.dashboard'))
            ->assertOk()
            ->assertDontSee('Créer une catégorie', false)
            ->assertDontSee(route('admin.agent-categories.create'), false);
    }

    public function test_admin_can_create_an_agent_category(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->post(route('admin.agent-categories.store'), [
                'name' => 'Community Management',
                'description' => 'Gestion de communauté',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.agent-categories.index'));

        $this->assertDatabaseHas('agent_categories', [
            'name' => 'Community Management',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }
}