<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\MetaAccount;
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
            ->assertSee('icon:ICO.calendar', false)
            ->assertSee('Calendrier Éditorial', false);
    }

    public function test_studio_exposes_separate_publication_and_visual_generation_actions(): void
    {
        $user = User::forceCreate([
            'first_name' => 'Utilisateur',
            'last_name' => 'Test',
            'email' => 'user@example.test',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $this->actingAs($user)->get('/user/dashboard')
            ->assertOk()
            ->assertSee('id="studioGenerateBtn"', false)
            ->assertSee('id="studioGenerateVisualBtn"', false)
            ->assertSee('id="studioTextStatus"', false)
            ->assertSee('id="studioVisualStatus"', false);
    }

    public function test_dashboard_renders_database_values_on_initial_load(): void
    {
        $user = User::forceCreate([
            'first_name' => 'Utilisateur',
            'last_name' => 'Test',
            'email' => 'database@example.test',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        MetaAccount::create([
            'user_id' => $user->id,
            'name' => 'Page récupération',
            'platform' => 'facebook',
            'meta_id' => '123456',
            'status' => 'connected',
        ]);

        $this->actingAs($user)->get('/user/dashboard')
            ->assertOk()
            ->assertSee('Page récupération')
            ->assertSee('data-stat="meta_connected"', false)
            ->assertSee('>1</div>', false);
    }

    public function test_meta_account_lists_have_distinct_targets_for_dashboard_and_management_page(): void
    {
        $user = User::forceCreate([
            'first_name' => 'Utilisateur',
            'last_name' => 'Test',
            'email' => 'meta-targets@example.test',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get('/user/dashboard')->assertOk();
        $content = $response->getContent();

        $this->assertSame(1, substr_count($content, 'id="dashboardMetaAccountsList"'));
        $this->assertSame(1, substr_count($content, 'id="metaAccountsList"'));
    }
}
