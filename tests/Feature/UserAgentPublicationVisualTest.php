<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AgentCategory;
use App\Models\User;
use App\Models\UserAgent;
use App\Models\UserAgentCalendarEntry;
use App\Models\UserAgentPublicationVisual;
use App\Jobs\CheckComfyVisualGeneration;
use App\Jobs\StoreComfyVisualImage;
use App\Services\ComfyUIClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery;
use App\Services\OllamaService;
use Tests\TestCase;

class UserAgentPublicationVisualTest extends TestCase
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

    private function makeValidatedEntry(User $user): UserAgentCalendarEntry
    {
        $category = AgentCategory::create(['name' => 'Community Manager', 'is_active' => true]);
        $agent = Agent::create(['name' => 'Community Manager', 'category_id' => $category->id, 'is_active' => true]);

        $userAgent = UserAgent::create([
            'user_id' => $user->id,
            'agent_id' => $agent->id,
            'custom_name' => 'Mon agent',
        ]);

        return UserAgentCalendarEntry::create([
            'user_agent_id' => $userAgent->id,
            'published_at' => '2026-01-05 09:00:00',
            'platform' => 'facebook',
            'pillar' => 'Pedagogie',
            'title' => 'Titre',
            'caption' => 'Legende',
            'visual_prompt' => 'Un bureau moderne',
            'hashtags' => '#test',
            'status' => 'validated',
        ]);
    }

    public function test_async_image_job_downloads_and_persists_comfy_output(): void
    {
        $user = $this->makeUser();
        $entry = $this->makeValidatedEntry($user);
        Storage::fake('public');

        $visual = UserAgentPublicationVisual::create([
            'calendar_entry_id' => $entry->id,
            'prompt' => 'Un bureau moderne',
            'negative_prompt' => '',
            'image_path' => null,
            'model' => 'v1-5-pruned-emaonly.safetensors',
            'prompt_id' => 'prompt-123',
            'theme' => 'Janvier',
            'comfy_filename' => 'IA_Agent_Studio_00001_.png',
            'comfy_subfolder' => '',
            'comfy_type' => 'output',
            'status' => 'downloading',
            'started_at' => now(),
        ]);

        Http::fake([
            '*/view*' => Http::response("\x89PNG\r\n\x1a\nPNG-CONTENU"),
        ]);

        (new StoreComfyVisualImage($visual->id))->handle(app(ComfyUIClient::class));

        $response = $this->actingAs($user)
            ->getJson(route('user.publications.visuals.status', $visual))
            ->assertOk()
            ->assertJsonPath('status', 'generated')
            ->assertJsonPath('success', true);

        $this->assertSame('generated', $visual->fresh()->status);
        $this->assertNotNull($visual->fresh()->image_path);
        $this->assertNotNull($visual->fresh()->completed_at);
        $response->assertJsonPath('visual.url', url('storage/' . $visual->fresh()->image_path));
        Storage::disk('public')->assertExists($visual->fresh()->image_path);
        $this->assertGreaterThan(0, Storage::disk('public')->size($visual->fresh()->image_path));

        Http::assertSent(function (HttpRequest $request): bool {
            if ($request->method() !== 'GET' || ! str_contains($request->url(), '/view?')) {
                return false;
            }

            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ($query['filename'] ?? null) === 'IA_Agent_Studio_00001_.png'
                && ($query['subfolder'] ?? null) === ''
                && ($query['type'] ?? null) === 'output';
        });
    }

    public function test_image_download_rejects_non_png_responses(): void
    {
        Storage::fake('public');
        Http::fake([
            '*/view*' => Http::response('<html>not an image</html>'),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('PNG valide');

        app(ComfyUIClient::class)->downloadAndStoreImage(
            'invalid.png',
            '',
            'output',
            'agent-1',
            'prompt-invalid'
        );
    }

    public function test_history_check_reschedules_when_comfy_is_temporarily_unreachable(): void
    {
        $user = $this->makeUser();
        $entry = $this->makeValidatedEntry($user);

        $visual = UserAgentPublicationVisual::create([
            'calendar_entry_id' => $entry->id,
            'prompt' => 'Un bureau moderne',
            'negative_prompt' => '',
            'image_path' => '',
            'model' => 'v1-5-pruned-emaonly.safetensors',
            'prompt_id' => 'prompt-456',
            'theme' => 'Janvier',
            'status' => 'pending',
            'started_at' => now(),
        ]);

        Queue::fake();
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('cURL error 28: Operation timed out');
        });

        (new CheckComfyVisualGeneration($visual->id))->handle(app(ComfyUIClient::class));

        $this->assertSame('pending', $visual->fresh()->status);
        Queue::assertPushed(CheckComfyVisualGeneration::class, function (CheckComfyVisualGeneration $job): bool {
            $delay = $job->delay;
            $delaySeconds = $delay instanceof \DateTimeInterface
                ? $delay->getTimestamp() - now()->getTimestamp()
                : 0;

            return $delaySeconds >= 29 && $delaySeconds <= 30;
        });
    }

    public function test_history_check_surfaces_comfy_execution_errors(): void
    {
        $user = $this->makeUser();
        $entry = $this->makeValidatedEntry($user);

        $visual = UserAgentPublicationVisual::create([
            'calendar_entry_id' => $entry->id,
            'prompt' => 'Un bureau moderne',
            'negative_prompt' => '',
            'image_path' => null,
            'model' => 'v1-5-pruned-emaonly.safetensors',
            'prompt_id' => 'prompt-error',
            'theme' => 'Janvier',
            'status' => 'pending',
            'started_at' => now(),
        ]);

        Http::fake([
            '*/history/prompt-error' => Http::response([
                'prompt-error' => [
                    'status' => [
                        'status_str' => 'error',
                        'messages' => [
                            ['execution_error', ['exception_message' => 'Checkpoint introuvable']],
                        ],
                    ],
                    'outputs' => [],
                ],
            ]),
        ]);

        (new CheckComfyVisualGeneration($visual->id))->handle(app(ComfyUIClient::class));

        $this->assertSame('failed', $visual->fresh()->status);
        $this->assertSame('ComfyUI a échoué : Checkpoint introuvable', $visual->fresh()->error);
    }

    public function test_generation_running_more_than_thirty_minutes_is_not_abandoned(): void
    {
        $user = $this->makeUser();
        $entry = $this->makeValidatedEntry($user);
        config()->set('services.comfyui.timeout', 14400);

        $visual = UserAgentPublicationVisual::create([
            'calendar_entry_id' => $entry->id,
            'prompt' => 'Un bureau moderne',
            'negative_prompt' => '',
            'image_path' => null,
            'model' => 'v1-5-pruned-emaonly.safetensors',
            'prompt_id' => 'prompt-timeout',
            'theme' => 'Janvier',
            'status' => 'pending',
            'started_at' => now()->subMinutes(31),
        ]);

        Queue::fake();
        Http::fake([
            '*/history/prompt-timeout' => Http::response([], 200),
        ]);

        (new CheckComfyVisualGeneration($visual->id))->handle(app(ComfyUIClient::class));

        $this->assertSame('pending', $visual->fresh()->status);
        Queue::assertPushed(CheckComfyVisualGeneration::class);
    }

    public function test_completed_comfy_image_is_retrieved_even_if_history_check_was_delayed_past_timeout(): void
    {
        $user = $this->makeUser();
        $entry = $this->makeValidatedEntry($user);

        $visual = UserAgentPublicationVisual::create([
            'calendar_entry_id' => $entry->id,
            'prompt' => 'A real visual prompt',
            'negative_prompt' => '',
            'image_path' => null,
            'model' => 'v1-5-pruned-emaonly.safetensors',
            'prompt_id' => 'prompt-completed-late',
            'theme' => 'Janvier',
            'status' => 'pending',
            'started_at' => now()->subHours(5),
        ]);

        Queue::fake();
        Http::fake([
            '*/history/prompt-completed-late' => Http::response([
                'prompt-completed-late' => [
                    'status' => ['status_str' => 'success', 'completed' => true],
                    'outputs' => [
                        '9' => [
                            'images' => [[
                                'filename' => 'IA_Agent_Studio_00017_.png',
                                'subfolder' => '',
                                'type' => 'output',
                            ]],
                        ],
                    ],
                ],
            ]),
        ]);

        (new CheckComfyVisualGeneration($visual->id))->handle(app(ComfyUIClient::class));

        $this->assertSame('downloading', $visual->fresh()->status);
        $this->assertSame('IA_Agent_Studio_00017_.png', $visual->fresh()->comfy_filename);
        $this->assertNull($visual->fresh()->error);
        Queue::assertPushed(StoreComfyVisualImage::class, function (StoreComfyVisualImage $job) use ($visual): bool {
            return $job->visualId === $visual->id;
        });
    }

    public function test_visual_prompts_for_five_publication_types_are_sent_to_the_comfy_workflow(): void
    {
        $examples = [
            [
                'title' => 'Formation en développement web',
                'caption' => 'Les apprenants découvrent HTML et JavaScript.',
                'subject' => 'Web development learners practicing code',
                'scene' => 'Learners follow a web development lesson.',
                'positive' => 'African instructor teaching HTML and JavaScript to learners practicing on laptops in a real classroom',
                'negative' => 'restaurant dishes, dresses, buildings, unrelated scene',
            ],
            [
                'title' => 'Les plats de notre restaurant',
                'caption' => 'Découvrez notre poisson braisé et ses accompagnements.',
                'subject' => 'Grilled fish meal',
                'scene' => 'A chef presents the restaurant meal.',
                'positive' => 'Freshly grilled fish with vegetables plated by a chef in a welcoming African restaurant dining room',
                'negative' => 'computer classroom, clothing shop, apartment interior, unrelated scene',
            ],
            [
                'title' => 'Collection de vêtements féminins',
                'caption' => 'Des robes en lin pour la nouvelle collection.',
                'subject' => 'Woman wearing a linen dress',
                'scene' => 'A model presents a linen dress.',
                'positive' => 'A woman wearing a flowing linen dress in a tasteful fashion editorial with natural fabric details',
                'negative' => 'coding class, food plate, office building, unrelated scene',
            ],
            [
                'title' => 'Service immobilier résidentiel',
                'caption' => 'Nous accompagnons les familles dans leur recherche de logement.',
                'subject' => 'Family viewing a residential home',
                'scene' => 'An agent presents a bright apartment to a family.',
                'positive' => 'A real estate agent showing a bright modern apartment living room to a family during a property visit',
                'negative' => 'restaurant meal, fashion runway, computer class, unrelated scene',
            ],
            [
                'title' => 'Conférence professionnelle annuelle',
                'caption' => 'Rencontrez les acteurs du secteur lors de notre rencontre.',
                'subject' => 'Professionals networking at a conference',
                'scene' => 'Attendees exchange ideas at a business event.',
                'positive' => 'Business professionals networking in a conference venue beside a stage and audience seating',
                'negative' => 'fashion shop, plated food, apartment tour, unrelated scene',
            ],
        ];

        $user = $this->makeUser();
        $ollama = Mockery::mock(OllamaService::class);
        $ollama->shouldReceive('models')->times(count($examples))->andReturn(['test-model']);
        $ollama->shouldReceive('generate')->times(count($examples) * 2)
            ->andReturnUsing(function ($model, $prompt, $timeout, $numPredict, $format) use ($examples) {
                if (str_contains($prompt, 'skeptical semantic reviewer')) {
                    $this->assertSame(['relevant', 'reason'], $format['required'] ?? []);

                    return ['ok' => true, 'raw' => '{"relevant":true,"reason":"The scene matches the publication."}'];
                }

                $this->assertSame('object', $format['type'] ?? null);
                $this->assertSame('array', $format['properties']['main_objects']['type'] ?? null);
                $this->assertSame(
                    ['subject', 'message', 'scene', 'characters', 'environment', 'composition', 'visual_style', 'lighting', 'color_palette', 'visual_prompt', 'negative_prompt', 'main_objects'],
                    $format['required'] ?? []
                );

                foreach ($examples as $example) {
                    if (str_contains($prompt, $example['title'])) {
                        return [
                            'ok' => true,
                            'raw' => json_encode([
                                'subject' => $example['subject'],
                                'message' => 'The publication topic is represented directly.',
                                'scene' => $example['scene'],
                                'main_objects' => ['Primary subject', 'Relevant setting'],
                                'characters' => 'People relevant to the publication.',
                                'environment' => 'A context matching the real activity.',
                                'composition' => 'Square editorial composition centered on the main subject.',
                                'visual_style' => 'Documentary advertising photography',
                                'lighting' => 'Soft natural light',
                                'color_palette' => 'Natural colors with brand accents',
                                'visual_prompt' => $example['positive'],
                                'negative_prompt' => $example['negative'] . ', text, watermark, low quality',
                            ], JSON_UNESCAPED_SLASHES),
                        ];
                    }
                }

                throw new \RuntimeException('No visual prompt fixture matched the publication.');
            });
        $this->app->instance(OllamaService::class, $ollama);
        Queue::fake();

        $promptId = 0;
        Http::fake(function (HttpRequest $request) use (&$promptId) {
            if ($request->url() === 'http://127.0.0.1:8188/system_stats') {
                return Http::response([], 200);
            }

            if ($request->method() === 'POST' && $request->url() === 'http://127.0.0.1:8188/prompt') {
                $promptId++;

                return Http::response(['prompt_id' => 'visual-test-' . $promptId], 200);
            }

            return Http::response([], 200);
        });

        foreach ($examples as $example) {
            $entry = $this->makeValidatedEntry($user);
            $entry->update([
                'title' => $example['title'],
                'caption' => $example['caption'],
                'generated_content' => $example['title'] . "\n\n" . $example['caption'],
                'visual_prompt' => 'Calendar hint: ' . $example['subject'],
            ]);

            $response = $this->actingAs($user)
                ->postJson(route('user.publications.visual', $entry))
                ->assertStatus(202)
                ->assertJsonPath('visual.visual_direction.subject', $example['subject'])
                ->assertJsonPath('visual.visual_direction.scene', $example['scene'])
                ->assertJsonPath('visual.status', 'pending');

            $visualPrompt = $example['positive'] . '. No text, watermark, signature, or invented logo.';
            $negativePrompt = $example['negative'] . ', text, watermark, low quality, lettering, signature, invented logo';
            $this->assertSame($visualPrompt, $response->json('visual.prompt'));
            $this->assertSame($negativePrompt, $response->json('visual.negative_prompt'));

            Http::assertSent(function (HttpRequest $request) use ($visualPrompt, $negativePrompt): bool {
                if ($request->method() !== 'POST' || $request->url() !== 'http://127.0.0.1:8188/prompt') {
                    return false;
                }

                $workflow = $request->data()['prompt'] ?? [];

                return ($workflow['6']['class_type'] ?? null) === 'CLIPTextEncode'
                    && ($workflow['6']['inputs']['text'] ?? null) === $visualPrompt
                    && ($workflow['7']['class_type'] ?? null) === 'CLIPTextEncode'
                    && ($workflow['7']['inputs']['text'] ?? null) === $negativePrompt;
            });
        }
    }

    public function test_manual_visual_prompt_edits_are_used_in_the_comfy_workflow(): void
    {
        $user = $this->makeUser();
        $entry = $this->makeValidatedEntry($user);
        Queue::fake();
        Http::fake([
            '*/system_stats' => Http::response([], 200),
            '*/prompt' => Http::response(['prompt_id' => 'manual-visual-prompt'], 200),
        ]);

        $positive = 'A chef plating the restaurant’s grilled fish meal in a warm dining room.';
        $negative = 'text, watermark, unrelated clothing products';

        $this->actingAs($user)
            ->postJson(route('user.publications.visual', $entry), [
                'visual_prompt' => $positive,
                'negative_prompt' => $negative,
            ])
            ->assertStatus(202)
            ->assertJsonPath('visual.prompt', $positive);

        Http::assertSent(function (HttpRequest $request) use ($positive, $negative): bool {
            $workflow = $request->data()['prompt'] ?? [];

            return $request->method() === 'POST'
                && ($workflow['6']['inputs']['text'] ?? null) === $positive
                && ($workflow['7']['inputs']['text'] ?? null) === $negative;
        });
    }

    public function test_semantically_unrelated_visual_direction_is_corrected_before_submission(): void
    {
        $user = $this->makeUser();
        $entry = $this->makeValidatedEntry($user);
        $entry->update([
            'title' => 'Vente de robes en lin',
            'caption' => 'Une collection de vêtements féminins.',
            'generated_content' => 'Vente de robes en lin. Une collection de vêtements féminins.',
        ]);
        $ollama = Mockery::mock(OllamaService::class);
        $ollama->shouldReceive('models')->once()->andReturn(['test-model']);
        $reviewCount = 0;
        $generationCount = 0;
        $ollama->shouldReceive('generate')->times(4)->andReturnUsing(
            function ($model, $prompt) use (&$reviewCount, &$generationCount) {
                if (str_contains($prompt, 'skeptical semantic reviewer')) {
                    $reviewCount++;

                    return $reviewCount === 1
                        ? ['ok' => true, 'raw' => '{"relevant":false,"reason":"The office computer scene does not show the clothing collection."}']
                        : ['ok' => true, 'raw' => '{"relevant":true,"reason":"The dress is the central subject."}'];
                }

                $generationCount++;
                if ($generationCount === 2) {
                    $this->assertStringContainsString('Correction demandée', $prompt);
                    return ['ok' => true, 'raw' => $this->visualDirectionJson(
                        'Woman wearing a linen dress',
                        'A model presents the linen clothing collection.',
                        'A woman wearing a linen dress in a fashion editorial, natural fabric detail, sunlit garden'
                    )];
                }

                return ['ok' => true, 'raw' => $this->visualDirectionJson(
                    'Generic office computer scene',
                    'A person works at a computer.',
                    'A person working at a desktop computer in a generic office'
                )];
            }
        );
        $this->app->instance(OllamaService::class, $ollama);
        Queue::fake();
        Http::fake([
            '*/system_stats' => Http::response([], 200),
            '*/prompt' => Http::response(['prompt_id' => 'corrected-visual'], 200),
        ]);

        $this->actingAs($user)
            ->postJson(route('user.publications.visual', $entry))
            ->assertStatus(202)
            ->assertJsonPath('visual.visual_direction.subject', 'Woman wearing a linen dress');

        Http::assertSent(function (HttpRequest $request): bool {
            $workflow = $request->data()['prompt'] ?? [];

            return $request->method() === 'POST'
                && str_contains(strtolower($workflow['6']['inputs']['text'] ?? ''), 'linen dress')
                && ! str_contains(strtolower($workflow['6']['inputs']['text'] ?? ''), 'desktop computer');
        });
    }

    public function test_negative_prompt_does_not_exclude_the_described_scene(): void
    {
        $user = $this->makeUser();
        $entry = $this->makeValidatedEntry($user);
        $ollama = Mockery::mock(OllamaService::class);
        $ollama->shouldReceive('models')->once()->andReturn(['test-model']);
        $ollama->shouldReceive('generate')->twice()->andReturnUsing(function ($model, $prompt) {
            if (str_contains($prompt, 'skeptical semantic reviewer')) {
                return ['ok' => true, 'raw' => '{"relevant":true,"reason":"The scene matches the publication."}'];
            }

            $direction = json_decode($this->visualDirectionJson(
                'Web development learners',
                'Learners practice coding on laptops and computers in a classroom.',
                'Web development learners practicing HTML code and JavaScript on laptops and computers in a classroom'
            ), true);
            $direction['negative_prompt'] = 'no learners, no laptops, no computers, no classroom, no code, code not visible, multiple people, non-technical setting, text, watermark, blur, low quality';

            return ['ok' => true, 'raw' => json_encode($direction)];
        });
        $this->app->instance(OllamaService::class, $ollama);
        Queue::fake();
        Http::fake([
            '*/system_stats' => Http::response([], 200),
            '*/prompt' => Http::response(['prompt_id' => 'negative-prompt-check'], 200),
        ]);

        $this->actingAs($user)
            ->postJson(route('user.publications.visual', $entry))
            ->assertStatus(202)
            ->assertJsonPath(
                'visual.negative_prompt',
                'text, watermark, blur, low quality, lettering, signature, invented logo'
            );
    }

    public function test_invalid_ollama_json_is_retried_before_workflow_submission(): void
    {
        $user = $this->makeUser();
        $entry = $this->makeValidatedEntry($user);
        $ollama = Mockery::mock(OllamaService::class);
        $ollama->shouldReceive('models')->once()->andReturn(['test-model']);
        $generationCount = 0;
        $ollama->shouldReceive('generate')->times(3)->andReturnUsing(
            function ($model, $prompt) use (&$generationCount) {
                if (str_contains($prompt, 'skeptical semantic reviewer')) {
                    return ['ok' => true, 'raw' => '{"relevant":true,"reason":"The direction is coherent."}'];
                }

                $generationCount++;
                if ($generationCount === 1) {
                    return ['ok' => true, 'raw' => '{"subject": invalid json'];
                }

                $this->assertStringContainsString('Correction demandée', $prompt);

                return [
                    'ok' => true,
                    'raw' => $this->visualDirectionJson(
                        'A web development class',
                        'Learners practice coding with an instructor.',
                        'Learners practice web development with an instructor in a classroom'
                    ),
                ];
            }
        );
        $this->app->instance(OllamaService::class, $ollama);
        Queue::fake();
        Http::fake([
            '*/system_stats' => Http::response([], 200),
            '*/prompt' => Http::response(['prompt_id' => 'valid-json-retry'], 200),
        ]);

        $this->actingAs($user)
            ->postJson(route('user.publications.visual', $entry))
            ->assertStatus(202)
            ->assertJsonPath('visual.visual_direction.subject', 'A web development class');
    }

    private function visualDirectionJson(string $subject, string $scene, string $prompt): string
    {
        return json_encode([
            'subject' => $subject,
            'message' => 'The visual communicates the publication topic.',
            'scene' => $scene,
            'main_objects' => ['Topic-specific subject', 'Relevant environment'],
            'characters' => 'People directly related to the topic.',
            'environment' => 'A setting that supports the activity.',
            'composition' => 'Square advertising composition focused on the subject.',
            'visual_style' => 'Editorial commercial photography',
            'lighting' => 'Soft natural light',
            'color_palette' => 'Natural colors',
            'visual_prompt' => $prompt,
            'negative_prompt' => 'unrelated scene, text, watermark, blur, low quality',
        ], JSON_UNESCAPED_SLASHES);
    }

    public function test_generating_a_publication_persists_its_text(): void
    {
        $user = $this->makeUser();
        $entry = $this->makeValidatedEntry($user);
        $ollama = Mockery::mock(OllamaService::class);
        $ollama->shouldReceive('models')->twice()->andReturn([], ['test-model']);
        $ollama->shouldReceive('generate')->twice()->andReturnUsing(function ($model, $prompt) {
            if (str_contains($prompt, 'skeptical semantic reviewer')) {
                return ['ok' => true, 'raw' => '{"relevant":true,"reason":"The visual scene matches the publication."}'];
            }

            if (str_contains($prompt, 'senior advertising art director')) {
                return [
                    'ok' => true,
                    'raw' => json_encode([
                        'subject' => 'A relevant publication scene',
                        'message' => 'The scene explains the publication subject.',
                        'scene' => 'A real, topic-specific scene.',
                        'main_objects' => ['Relevant subject'],
                        'characters' => 'People relevant to the topic.',
                        'environment' => 'A matching real-world setting.',
                        'composition' => 'Square centered advertising composition.',
                        'visual_style' => 'Editorial photography',
                        'lighting' => 'Soft natural light',
                        'color_palette' => 'Natural colors',
                        'visual_prompt' => 'A specific editorial scene closely illustrating the publication topic',
                        'negative_prompt' => 'unrelated scene, text, watermark, blur',
                    ], JSON_UNESCAPED_SLASHES),
                ];
            }

            throw new \RuntimeException('Unexpected Ollama generation call in this fallback-text test.');
        });
        $this->app->instance(OllamaService::class, $ollama);
        Http::fake([
            '*/system_stats' => Http::response([], 200),
            '*/prompt' => Http::response(['prompt_id' => 'text-test-prompt'], 200),
        ]);
        Queue::fake();

        $this->actingAs($user)
            ->postJson(route('user.publications.generate', $entry))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('publication.text', "Titre\n\nLegende\n\n#test")
            ->assertJsonPath('publication.visual_status', 'pending');

        $this->assertSame("Titre\n\nLegende\n\n#test", $entry->fresh()->generated_content);
        Queue::assertPushed(CheckComfyVisualGeneration::class, function (CheckComfyVisualGeneration $job): bool {
            $delay = $job->delay;
            $delaySeconds = $delay instanceof \DateTimeInterface
                ? $delay->getTimestamp() - now()->getTimestamp()
                : 0;

            return $delaySeconds >= 14 && $delaySeconds <= 15;
        });
    }

    public function test_entries_expose_the_visual_state_used_by_the_studio_button(): void
    {
        $user = $this->makeUser();
        $entry = $this->makeValidatedEntry($user);
        $entry->update(['studio_generated_at' => '2026-01-05 10:30:00']);

        $this->actingAs($user)
            ->getJson(route('user.publications.validated'))
            ->assertOk()
            ->assertJsonPath('entries.0.id', $entry->id)
            ->assertJsonPath('entries.0.studio_generated_at', '2026-01-05T10:30:00')
            ->assertJsonPath('entries.0.visual_id', null)
            ->assertJsonPath('entries.0.visual_status', null)
            ->assertJsonPath('entries.0.visual_url', null);

        UserAgentPublicationVisual::create([
            'calendar_entry_id' => $entry->id,
            'prompt' => 'Un bureau moderne',
            'negative_prompt' => '',
            'image_path' => '',
            'model' => 'v1-5-pruned-emaonly.safetensors',
            'prompt_id' => 'prompt-789',
            'theme' => 'Janvier',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->getJson(route('user.publications.validated'))
            ->assertOk()
            ->assertJsonPath('entries.0.visual_status', 'pending');
    }

    public function test_visual_generation_reuses_the_failed_row(): void
    {
        $user = $this->makeUser();
        $entry = $this->makeValidatedEntry($user);

        $visual = UserAgentPublicationVisual::create([
            'calendar_entry_id' => $entry->id,
            'prompt' => 'Ancien prompt',
            'negative_prompt' => '',
            'image_path' => '',
            'model' => 'v1-5-pruned-emaonly.safetensors',
            'prompt_id' => 'old-prompt',
            'theme' => 'Janvier',
            'status' => 'failed',
            'error' => 'timeout',
        ]);

        Http::fake([
            '*/system_stats' => Http::response([], 200),
            '*/prompt' => Http::response(['prompt_id' => 'new-prompt', 'model' => 'v1-5-pruned-emaonly.safetensors']),
        ]);
        Queue::fake();

        $this->actingAs($user)
            ->postJson(route('user.publications.visual', $entry), [
                'visual_prompt' => 'A tailored editorial scene illustrating the publication precisely.',
                'negative_prompt' => 'unrelated scene, text, watermark',
            ])
            ->assertStatus(202)
            ->assertJsonPath('status', 'pending');

        $this->assertDatabaseCount('user_agent_publication_visuals', 1);
        $this->assertSame('pending', $visual->fresh()->status);
        $this->assertSame('new-prompt', $visual->fresh()->prompt_id);
        Queue::assertPushed(CheckComfyVisualGeneration::class);

        Http::assertSent(function (HttpRequest $request): bool {
            if ($request->method() !== 'POST' || $request->url() !== 'http://127.0.0.1:8188/prompt') {
                return false;
            }

            $workflow = $request->data()['prompt'] ?? [];

            return ($workflow['4']['class_type'] ?? null) === 'CheckpointLoaderSimple'
                && ($workflow['4']['inputs']['ckpt_name'] ?? null) === 'v1-5-pruned-emaonly.safetensors'
                && ($workflow['9']['class_type'] ?? null) === 'SaveImage'
                && ($workflow['9']['inputs']['images'] ?? null) === ['8', 0];
        });
        Http::assertSent(function (HttpRequest $request): bool {
            return $request->method() === 'GET'
                && $request->url() === 'http://127.0.0.1:8188/system_stats';
        });
        Http::assertNotSent(function (HttpRequest $request): bool {
            return $request->url() === 'http://127.0.0.1:8188/prompt'
                && $request->method() === 'GET';
        });
    }

    public function test_text_is_kept_and_failed_visual_can_be_retried_without_regenerating_text(): void
    {
        $user = $this->makeUser();
        $entry = $this->makeValidatedEntry($user);
        $ollama = Mockery::mock(OllamaService::class);
        $entry->update([
            'title' => 'Nouvelle collection de robes en lin',
            'caption' => 'Une collection durable.',
            'visual_prompt' => 'Une robe en lin portée dans un jardin lumineux',
        ]);
        $ollama->shouldReceive('models')->times(3)->andReturn(['test-model']);
        $visualPromptRequest = '';
        $ollama->shouldReceive('generate')->times(5)
            ->andReturnUsing(function ($model, $prompt) use (&$visualPromptRequest) {
                if (str_contains($prompt, 'skeptical semantic reviewer')) {
                    return ['ok' => true, 'raw' => '{"relevant":true,"reason":"The scene matches the clothing publication."}'];
                }

                if (str_contains($prompt, 'senior advertising art director')) {
                    $visualPromptRequest = $prompt;
                    return [
                        'ok' => true,
                        'raw' => '{"subject":"Woman wearing a linen dress","message":"Natural clothing can be elegant and comfortable.","scene":"A model presents a linen dress in a sunlit garden.","main_objects":["linen dress"],"characters":"A woman modeling the dress.","environment":"A sunlit garden.","composition":"Square editorial portrait with the dress in focus.","visual_style":"Editorial fashion photography","lighting":"Soft warm natural light","color_palette":"Warm neutrals and natural greens","visual_prompt":"A woman wearing a linen dress in a sunlit garden, natural fabric details, editorial fashion photography, soft warm light, balanced square composition","negative_prompt":"text, watermark, logo, low quality, blurry"}',
                    ];
                }

                return [
                    'ok' => true,
                    'raw' => '{"caption":"Texte Ollama conservé","hashtags":"#test","visual_prompt":"Une scène professionnelle lumineuse"}',
                ];
            });
        $this->app->instance(OllamaService::class, $ollama);
        Queue::fake();

        $promptRequests = 0;
        Http::fake(function (HttpRequest $request) use (&$promptRequests) {
            if ($request->url() === 'http://127.0.0.1:8188/system_stats') {
                return Http::response([], 200);
            }

            if ($request->method() === 'POST' && $request->url() === 'http://127.0.0.1:8188/prompt') {
                $promptRequests++;

                if ($promptRequests === 1) {
                    return Http::response([
                        'error' => ['message' => 'Prompt outputs failed validation'],
                        'node_errors' => ['4' => ['ckpt_name' => 'Checkpoint missing']],
                    ], 400);
                }

                return Http::response(['prompt_id' => 'retry-prompt'], 200);
            }

            return Http::response([], 200);
        });

        $this->actingAs($user)
            ->postJson(route('user.publications.generate', $entry))
            ->assertOk()
            ->assertJsonPath('publication.caption', 'Texte Ollama conservé')
            ->assertJsonPath('publication.visual_status', 'failed')
            ->assertJsonPath('publication.visual.status', 'failed')
            ->assertJsonPath('publication.visual.error', fn ($error) => str_contains($error, 'Checkpoint missing'));

        $visual = UserAgentPublicationVisual::query()->where('calendar_entry_id', $entry->id)->firstOrFail();
        $this->assertSame('Texte Ollama conservé', $entry->fresh()->studio_caption);
        $this->assertSame("Nouvelle collection de robes en lin\n\nTexte Ollama conservé\n\n#test", $entry->fresh()->generated_content);
        $this->assertSame('Woman wearing a linen dress', $visual->visual_subject);
        $this->assertStringContainsString('woman wearing a linen dress', strtolower($visual->prompt));
        $this->assertStringContainsString('Une robe en lin portée dans un jardin lumineux', $visualPromptRequest);
        $this->assertStringContainsString('Texte Ollama conservé', $visualPromptRequest);
        $this->assertSame('1:1', $visual->aspect_ratio);

        $this->actingAs($user)
            ->postJson(route('user.publications.visual', $entry))
            ->assertStatus(202)
            ->assertJsonPath('status', 'pending');

        $this->assertSame('pending', $visual->fresh()->status);
        $this->assertSame('retry-prompt', $visual->fresh()->prompt_id);
        $this->assertSame(2, $promptRequests);
        Queue::assertPushed(CheckComfyVisualGeneration::class);
    }
}