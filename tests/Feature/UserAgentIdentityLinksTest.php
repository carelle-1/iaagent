<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AgentCategory;
use App\Models\User;
use App\Models\UserAgent;
use App\Models\UserAgentContact;
use App\Models\UserAgentWebsite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAgentIdentityLinksTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email = 'user@example.test'): User
    {
        return User::forceCreate([
            'first_name' => 'Utilisateur',
            'last_name' => 'Test',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }

    private function makeAgent(User $user): UserAgent
    {
        $category = AgentCategory::create(['name' => 'Community Manager', 'is_active' => true]);
        $agent = Agent::create(['name' => 'Community Manager', 'category_id' => $category->id, 'is_active' => true]);

        return UserAgent::create([
            'user_id' => $user->id,
            'agent_id' => $agent->id,
            'custom_name' => 'Mon agent',
        ]);
    }

    public function test_user_can_manage_websites(): void
    {
        $user = $this->makeUser();
        $userAgent = $this->makeAgent($user);

        $this->actingAs($user)
            ->postJson(route('user.agents.identity.websites.store', $userAgent), [
                'label' => 'Site vitrine',
                'url' => 'https://exemple.com',
                'notes' => 'Page d\'accueil',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('website.label', 'Site vitrine');

        $website = UserAgentWebsite::firstOrFail();

        $this->actingAs($user)
            ->putJson(route('user.agents.identity.websites.update', [$userAgent, $website]), [
                'label' => 'Site catalogue',
                'url' => 'https://exemple.com/catalogue',
            ])
            ->assertOk()
            ->assertJsonPath('website.label', 'Site catalogue');

        $this->assertSame('https://exemple.com/catalogue', $website->fresh()->url);

        $this->actingAs($user)
            ->deleteJson(route('user.agents.identity.websites.destroy', [$userAgent, $website]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('user_agent_websites', 0);
    }

    public function test_user_can_manage_contacts(): void
    {
        $user = $this->makeUser();
        $userAgent = $this->makeAgent($user);

        $this->actingAs($user)
            ->postJson(route('user.agents.identity.contacts.store', $userAgent), [
                'name' => 'Service client',
                'phone' => '+33 6 12 34 56 78',
                'notes' => 'Du lundi au vendredi',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('contact.phone', '+33 6 12 34 56 78');

        $contact = UserAgentContact::firstOrFail();

        $this->actingAs($user)
            ->putJson(route('user.agents.identity.contacts.update', [$userAgent, $contact]), [
                'name' => 'Accueil',
                'phone' => '0102030405',
            ])
            ->assertOk()
            ->assertJsonPath('contact.name', 'Accueil');

        $this->assertSame('0102030405', $contact->fresh()->phone);

        $this->actingAs($user)
            ->deleteJson(route('user.agents.identity.contacts.destroy', [$userAgent, $contact]))
            ->assertOk();

        $this->assertDatabaseCount('user_agent_contacts', 0);
    }

    public function test_websites_and_contacts_are_returned_by_the_identity_endpoint(): void
    {
        $user = $this->makeUser();
        $userAgent = $this->makeAgent($user);

        UserAgentWebsite::create([
            'user_agent_id' => $userAgent->id,
            'label' => 'Site vitrine',
            'url' => 'https://exemple.com',
        ]);

        UserAgentContact::create([
            'user_agent_id' => $userAgent->id,
            'name' => 'Service client',
            'phone' => '+33 6 12 34 56 78',
        ]);

        $this->actingAs($user)
            ->getJson(route('user.agents.identity.show', $userAgent))
            ->assertOk()
            ->assertJsonCount(1, 'websites')
            ->assertJsonCount(1, 'contacts')
            ->assertJsonPath('websites.0.label', 'Site vitrine')
            ->assertJsonPath('contacts.0.name', 'Service client');
    }

    public function test_identity_page_exposes_website_and_contact_management(): void
    {
        $user = $this->makeUser();
        $this->makeAgent($user);

        $this->actingAs($user)
            ->get('/user/dashboard')
            ->assertOk()
            ->assertSee('id="websitesList"', false)
            ->assertSee('id="contactsList"', false)
            ->assertSee('id="websiteAddBtn"', false)
            ->assertSee('id="contactAddBtn"', false)
            ->assertSee('id="websiteModal"', false)
            ->assertSee('id="contactModal"', false)
            ->assertSee('numéros de téléphone', false);
    }

    public function test_another_user_cannot_manage_links(): void
    {
        $owner = $this->makeUser();
        $other = $this->makeUser('other@example.test');
        $userAgent = $this->makeAgent($owner);

        $this->actingAs($other)
            ->postJson(route('user.agents.identity.websites.store', $userAgent), [
                'label' => 'Site pirate',
                'url' => 'https://exemple.com',
            ])
            ->assertForbidden();

        $this->actingAs($other)
            ->postJson(route('user.agents.identity.contacts.store', $userAgent), [
                'name' => 'Pirate',
                'phone' => '0000000000',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('user_agent_websites', 0);
        $this->assertDatabaseCount('user_agent_contacts', 0);
    }
}