<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AgentCategory;
use App\Models\User;
use App\Models\UserAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAgentCreationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::forceCreate([
            'first_name' => 'Utilisateur',
            'last_name' => 'Test',
            'email' => 'user@example.test',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }

    public function test_user_can_create_an_agent_inside_a_category(): void
    {
        $user = $this->makeUser();
        $category = AgentCategory::create(['name' => 'Community Manager', 'is_active' => true]);
        $agent = Agent::create(['name' => 'Community Manager', 'category_id' => $category->id, 'is_active' => true]);

        $this->actingAs($user)
            ->post(route('user.agents.store'), [
                'agent_id' => $agent->id,
                'category_id' => $category->id,
                'custom_name' => 'Mon agent',
                'domain' => 'community-management',
                'tone' => 'expert',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('user_agents', [
            'user_id' => $user->id,
            'agent_id' => $agent->id,
            'custom_name' => 'Mon agent',
        ]);
    }

    public function test_creation_requires_a_category(): void
    {
        $user = $this->makeUser();
        $category = AgentCategory::create(['name' => 'Community Manager', 'is_active' => true]);
        $agent = Agent::create(['name' => 'Community Manager', 'category_id' => $category->id, 'is_active' => true]);

        $this->actingAs($user)
            ->post(route('user.agents.store'), [
                'agent_id' => $agent->id,
                'domain' => 'community-management',
                'tone' => 'expert',
            ])
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('user_agents', 0);
    }

    public function test_creation_rejects_a_category_that_does_not_match_the_model(): void
    {
        $user = $this->makeUser();
        $categoryA = AgentCategory::create(['name' => 'Community Manager', 'is_active' => true]);
        $categoryB = AgentCategory::create(['name' => 'Support Client', 'is_active' => true]);
        $agent = Agent::create(['name' => 'Service client', 'category_id' => $categoryA->id, 'is_active' => true]);

        $this->actingAs($user)
            ->post(route('user.agents.store'), [
                'agent_id' => $agent->id,
                'category_id' => $categoryB->id,
                'domain' => 'support-client',
                'tone' => 'expert',
            ])
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('user_agents', 0);
    }

    public function test_user_agents_are_grouped_by_category_in_the_api(): void
    {
        $user = $this->makeUser();
        $category = AgentCategory::create(['name' => 'Community Manager', 'is_active' => true]);
        $agent = Agent::create(['name' => 'Community Manager', 'category_id' => $category->id, 'is_active' => true]);
        UserAgent::create([
            'user_id' => $user->id,
            'agent_id' => $agent->id,
            'custom_name' => 'Mon agent',
        ]);

        $this->actingAs($user)
            ->getJson('/user/agents')
            ->assertOk()
            ->assertJsonPath('userAgents.0.agent.category_id', $category->id)
            ->assertJsonPath('userAgents.0.agent.category.name', 'Community Manager');
    }
}