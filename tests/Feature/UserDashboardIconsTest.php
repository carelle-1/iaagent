<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserDashboardIconsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_lucide_components_for_dynamic_agent_navigation(): void
    {
        $user = User::forceCreate([
            'first_name' => 'Utilisateur',
            'last_name' => 'Test',
            'email' => 'user@example.test',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get('/user/dashboard');

        $response->assertOk()
            ->assertDontSee('<x-lucide-calendar', false)
            ->assertDontSee('<use href="#', false)
            ->assertSee("icon:'<svg", false);
    }
}
