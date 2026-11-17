<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_the_admin_agent_navigation(): void
    {
        $admin = User::forceCreate([
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        Agent::create(['name' => 'Agent de test']);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Agent de test')
            ->assertSee('href="' . route('admin.agents.index') . '"', false);
    }
}
