<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AgentCategory;
use App\Models\MetaAccount;
use App\Models\User;
use App\Models\UserAgent;
use App\Models\UserAgentCalendarEntry;
use App\Models\UserAgentIdentity;
use App\Models\UserAgentInspiringAccount;
use App\Models\UserAgentPublicationVisual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserAgentCalendarGenerationTest extends TestCase
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

    private function makeAgent(User $user): UserAgent
    {
        $category = AgentCategory::create(['name' => 'Community Manager', 'is_active' => true]);
        $agent = Agent::create([
            'name' => 'Community Manager',
            'description' => 'Gestion de la communauté et de la présence en ligne.',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        return UserAgent::create([
            'user_id' => $user->id,
            'agent_id' => $agent->id,
            'custom_name' => 'Mon agent',
            'domain' => 'community-management',
            'tone' => 'expert',
        ]);
    }

    private function makeIdentity(UserAgent $userAgent, array $attributes = []): UserAgentIdentity
    {
        return UserAgentIdentity::create(array_merge([
            'user_agent_id' => $userAgent->id,
            'objective' => 'Augmenter de 30 % le nombre d\'abonnés de la page Facebook.',
            'charter' => 'Ton professionnel, tutoiement, phrases courtes.',
            'palette' => [['name' => 'Cyan', 'hex' => '#2BC7E8']],
        ], $attributes));
    }

    private function fakeOllama(string $calendar): void
    {
        Http::fake([
            '*/api/tags' => Http::response([
                'models' => [
                    ['name' => 'mistral:latest'],
                ],
            ]),
            '*/api/generate' => Http::response([
                'response' => $calendar,
            ]),
        ]);
    }

    private function calendarPayload(string $platform = 'facebook'): string
    {
        return json_encode([
            'date' => '2026-01-05',
            'time' => '09:00',
            'platform' => $platform,
            'pillar' => 'Pedagogie',
            'title' => 'Titre',
            'caption' => 'Legende',
            'visual_prompt' => 'Visuel',
            'hashtags' => '#test',
            'status' => 'pending',
        ]);
    }

    private function generate(User $user, UserAgent $userAgent)
    {
        return $this->actingAs($user)->postJson(
            route('user.agents.calendar.generate', $userAgent),
            ['model' => 'mistral:latest', 'weeks' => 1, 'posts_per_week' => 1]
        );
    }

    public function test_generation_requires_an_identity(): void
    {
        $user = $this->makeUser();
        $userAgent = $this->makeAgent($user);

        $this->assertNull($userAgent->identity);

        $this->fakeOllama($this->calendarPayload());

        $this->generate($user, $userAgent)
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'L\'identité de votre agent n\'est pas encore renseignée. Complétez au minimum l\'objectif dans « Identité & inspiration » avant de générer le calendrier.'
            );

        $this->assertDatabaseCount('user_agent_calendar_entries', 0);
    }

    public function test_generation_uses_the_configured_identity(): void
    {
        $user = $this->makeUser();
        $userAgent = $this->makeAgent($user);
        $this->makeIdentity($userAgent);

        UserAgentInspiringAccount::create([
            'user_agent_id' => $userAgent->id,
            'platform' => 'facebook',
            'handle' => 'marque.top',
            'notes' => 'Ton punchy et visuels très contrastés.',
        ]);

        $this->fakeOllama($this->calendarPayload());

        $this->generate($user, $userAgent)->assertOk()->assertJsonPath('success', true);

        $prompt = '';

        foreach (Http::recorded() as [$request, $response]) {
            if (str_contains($request->url(), '/api/generate')) {
                $prompt = (string) ($request->data()['prompt'] ?? '');
            }
        }

        $this->assertStringContainsString('Augmenter de 30 % le nombre d\'abonnés de la page Facebook.', $prompt);
        $this->assertStringContainsString('Ton professionnel, tutoiement, phrases courtes.', $prompt);
        $this->assertStringContainsString('#2BC7E8', $prompt);
        $this->assertStringContainsString('marque.top', $prompt);
        $this->assertStringContainsString('Ton punchy et visuels très contrastés.', $prompt);
        $this->assertStringContainsString('Gestion de la communauté et de la présence en ligne.', $prompt);
    }

    public function test_generation_forces_the_facebook_platform(): void
    {
        $user = $this->makeUser();
        $userAgent = $this->makeAgent($user);
        $this->makeIdentity($userAgent);

        $this->fakeOllama($this->calendarPayload('instagram'));

        $this->generate($user, $userAgent)
            ->assertOk()
            ->assertJsonPath('entries.0.platform', 'facebook');

        $this->assertDatabaseHas('user_agent_calendar_entries', [
            'user_agent_id' => $userAgent->id,
            'platform' => 'facebook',
        ]);
    }

    public function test_entries_cannot_be_moved_to_another_platform(): void
    {
        $user = $this->makeUser();
        $userAgent = $this->makeAgent($user);

        $entry = UserAgentCalendarEntry::create([
            'user_agent_id' => $userAgent->id,
            'published_at' => '2026-01-05 09:00:00',
            'platform' => 'facebook',
            'pillar' => 'Pedagogie',
            'title' => 'Titre',
            'caption' => 'Legende',
            'visual_prompt' => 'Visuel',
            'hashtags' => '#test',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->patchJson(route('user.agents.calendar.entries.update', [$userAgent, $entry]), [
                'published_at' => '2026-01-05 10:00:00',
                'platform' => 'instagram',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('platform');

        $this->assertSame('facebook', $entry->fresh()->platform);
    }

    public function test_veille_returns_activity_using_the_meta_account_and_visual_relations(): void
    {
        $user = $this->makeUser();
        $userAgent = $this->makeAgent($user);
        $metaAccount = MetaAccount::create([
            'user_id' => $user->id,
            'name' => 'Page de test',
            'platform' => 'facebook',
            'meta_id' => 'page-123',
            'status' => 'connected',
        ]);
        $entry = UserAgentCalendarEntry::create([
            'user_agent_id' => $userAgent->id,
            'meta_account_id' => $metaAccount->id,
            'published_at' => now()->subDay(),
            'platform' => 'facebook',
            'pillar' => 'Conseils',
            'title' => 'Conseil publié',
            'caption' => 'Exemple',
            'status' => 'published',
            'platform_post_id' => 'post-123',
        ]);
        UserAgentPublicationVisual::create([
            'calendar_entry_id' => $entry->id,
            'prompt' => 'Visuel de test',
            'image_path' => 'visuals/test.png',
            'aspect_ratio' => '4:5',
            'status' => 'generated',
        ]);

        $this->actingAs($user)->getJson('/user/veille')
            ->assertOk()
            ->assertJsonPath('published', 1)
            ->assertJsonPath('signals.1.text', '« Page de test » (facebook) totalise 1 publication(s) publiée(s).')
            ->assertJsonPath('signals.2.text', 'Le format « 4:5 » est le plus utilisé avec 1 publication(s).');
    }
}