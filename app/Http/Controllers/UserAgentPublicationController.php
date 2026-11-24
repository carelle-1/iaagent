<?php

namespace App\Http\Controllers;

use App\Jobs\CheckComfyVisualGeneration;
use App\Jobs\StoreComfyVisualImage;
use App\Models\MetaAccount;
use App\Models\UserAgent;
use App\Models\UserAgentCalendarEntry;
use App\Models\UserAgentPublicationVisual;
use App\Services\ComfyUIClient;
use App\Services\MetaGraphService;
use App\Services\OllamaService;
use App\Services\VisualPromptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserAgentPublicationController extends Controller
{
    public function __construct(
        private ComfyUIClient $comfyui,
        private MetaGraphService $meta,
        private OllamaService $ollama,
        private VisualPromptService $visualPrompts,
    ) {
    }

    public function index(): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        $entries = UserAgentCalendarEntry::with(['userAgent.agent', 'visuals'])
            ->whereIn('user_agent_id', $userAgentIds)
            ->whereIn('status', ['validated', 'published'])
            ->orderBy('published_at')
            ->get();

        return response()->json([
            'success' => true,
            'entries' => $entries->map(fn (UserAgentCalendarEntry $entry) => $this->serializeEntry($entry))->values()->all(),
        ]);
    }

    public function dashboardStats(): JsonResponse
    {
        $userId = Auth::id();
        $userAgentIds = UserAgent::where('user_id', $userId)->pluck('id');

        $agentsOperational = UserAgent::where('user_id', $userId)->count();

        $metaConnected = MetaAccount::where('user_id', $userId)
            ->where('status', 'connected')
            ->count();

        $pendingValidation = UserAgentCalendarEntry::whereIn('user_agent_id', $userAgentIds)
            ->where('status', 'validated')
            ->count();

        $autoReady = UserAgentCalendarEntry::whereIn('user_agent_id', $userAgentIds)
            ->where('studio_generated', true)
            ->where('status', '!=', 'published')
            ->count();

        $onlineMeta = UserAgentCalendarEntry::whereIn('user_agent_id', $userAgentIds)
            ->whereNotNull('platform_post_id')
            ->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'agents_operational' => (int) $agentsOperational,
                'meta_connected' => (int) $metaConnected,
                'pending_validation' => (int) $pendingValidation,
                'auto_ready' => (int) $autoReady,
                'online_meta' => (int) $onlineMeta,
            ],
        ]);
    }

    public function generate(UserAgentCalendarEntry $entry): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        abort_unless($userAgentIds->contains($entry->user_agent_id), 403);
        abort_unless($entry->status === 'validated', 422, 'Cette publication doit être validée avant génération.');

        $entry->load(['userAgent.agent', 'userAgent.identity', 'userAgent.inspiringAccounts', 'userAgent.websites', 'userAgent.contacts', 'userAgent.identityImages']);

        $agent = $entry->userAgent;
        $identity = $agent->identity;
        $agentName = $agent->custom_name ?: ($agent->agent->name ?? 'Agent IA');

        $title = $entry->title ?: 'Publication';

        // Try to generate with Ollama
        $ollamaResult = $this->generatePublicationContentWithOllama($entry, $agent, $identity);

        $studioCaption = '';
        $studioHashtags = '';
        $studioVisualPrompt = '';
        $studioCta = 'En savoir plus';
        $ollamaSuccess = false;

        if ($ollamaResult['ok'] ?? false) {
            $generated = $ollamaResult['data'] ?? [];
            $studioCaption = is_string($generated['caption'] ?? '') ? $generated['caption'] : '';
            $studioHashtags = is_string($generated['hashtags'] ?? '') ? $generated['hashtags'] : '';
            $studioVisualPrompt = is_string($generated['visual_prompt'] ?? '') ? $generated['visual_prompt'] : '';
            $studioCta = isset($generated['cta']) && is_string($generated['cta']) ? $generated['cta'] : 'En savoir plus';
            $ollamaSuccess = true;
        } else {
            // Ollama failed or unavailable - log for debugging
            \Log::warning('Ollama generation failed or unavailable', ['error' => $ollamaResult['error'] ?? 'unknown']);
        }

        // Fallback to calendar data if Ollama failed or returned empty
        if (!$ollamaSuccess || $studioCaption === '') {
            $studioCaption = $entry->caption ?: '';
        }
        if (!$ollamaSuccess || $studioHashtags === '') {
            $studioHashtags = $entry->hashtags ?: '';
        }
        if (!$ollamaSuccess || $studioVisualPrompt === '') {
            $studioVisualPrompt = $entry->visual_prompt ?: '';
        }
        if (!$ollamaSuccess || $studioCta === 'En savoir plus') {
            $studioCta = 'En savoir plus';
        }

        // Build the full publication text
        $text = trim($title . "\n\n" . $studioCaption . ($studioHashtags !== '' ? "\n\n" . $studioHashtags : ''));

        // Save to production fields
        $entry->update([
            'studio_caption' => $studioCaption,
            'studio_hashtags' => $studioHashtags,
            'studio_visual_prompt' => $studioVisualPrompt,
            'studio_cta' => $studioCta,
            'studio_generated_at' => now(),
            'studio_generated' => $ollamaSuccess,
            'generated_content' => $text,
        ]);

        // Automatically trigger visual generation if we have a visual prompt
        $visual = null;
        $visualStatus = 'none';
        if ($studioVisualPrompt !== '') {
            $visualResult = $this->generateVisualForEntry($entry, $agent, $identity);
            $visual = $visualResult['visual'] ?? null;
            $visualStatus = $visualResult['status'] ?? 'pending';
            if (! empty($visual['prompt'])) {
                $studioVisualPrompt = $visual['prompt'];
                $entry->update(['studio_visual_prompt' => $studioVisualPrompt]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $ollamaSuccess ? 'Publication générée avec Ollama.' : 'Publication générée (Ollama indisponible, utilisez les données du calendrier).',
            'publication' => [
                'entry_id' => $entry->id,
                'agent' => $agentName,
                'platform' => $entry->platform,
                'pillar' => $entry->pillar,
                'published_at' => $entry->published_at->format('Y-m-d\TH:i:s'),
                'title' => $title,
                // Calendar planning fields (read-only reference)
                'calendar_caption' => $entry->caption,
                'calendar_hashtags' => $entry->hashtags,
                'calendar_visual_prompt' => $entry->visual_prompt,
                // Studio production fields (AI-generated or fallback)
                'caption' => $studioCaption,
                'hashtags' => $studioHashtags,
                'visual_prompt' => $studioVisualPrompt,
                'cta' => $studioCta,
                'text' => $text,
                'studio_generated' => $ollamaSuccess,
                'studio_generated_at' => $entry->studio_generated_at?->format('Y-m-d\TH:i:s'),
                'status' => 'draft',
                // Visual generation info
                'visual' => $visual,
                'visual_status' => $visualStatus,
            ],
        ]);
    }

    private function generateVisualForEntry(
        UserAgentCalendarEntry $entry,
        UserAgent $agent,
        $identity,
        array $manualOverrides = []
    ): array
    {
        $theme = $this->monthlyTheme($entry->published_at);
        $latest = $entry->visuals()->latest('id')->first();

        if ($latest && in_array($latest->status, ['pending', 'running', 'downloading'], true)) {
            return ['visual' => $this->serializeVisual($latest), 'status' => $latest->status];
        }

        if ($latest && $latest->status === 'generated') {
            $disk = Storage::disk('public');
            if ($latest->image_path && $disk->exists($latest->image_path) && $disk->size($latest->image_path) > 0) {
                return ['visual' => $this->serializeVisual($latest), 'status' => 'generated'];
            }

            $latest->update([
                'status' => 'failed',
                'error' => 'Le fichier image enregistré est absent ou vide.',
                'completed_at' => now(),
            ]);
            $latest->refresh();
        }

        $manualPrompt = trim((string) ($manualOverrides['visual_prompt'] ?? ''));
        if ($latest && $latest->status === 'failed' && $latest->comfy_filename && $manualOverrides === []) {
                $latest->update([
                    'status' => 'downloading',
                    'error' => null,
                    'completed_at' => null,
                ]);
                StoreComfyVisualImage::dispatch($latest->id);

                return ['visual' => $this->serializeVisual($latest->refresh()), 'status' => 'downloading'];
        }

        try {
            if ($manualPrompt !== '') {
                $direction = $latest?->visual_direction ?? [];
                $prepared = [
                    'visual_prompt' => $manualPrompt,
                    'negative_prompt' => trim((string) ($manualOverrides['negative_prompt'] ?? $latest?->negative_prompt ?? config('services.comfyui.negative_prompt', ''))),
                    'visual_subject' => $latest?->visual_subject ?: trim((string) ($entry->title ?: 'Sujet de la publication')),
                    'visual_style' => $latest?->visual_style ?: 'Direction artistique modifiée manuellement',
                    'visual_direction' => $direction ?: null,
                    'aspect_ratio' => '1:1',
                    'source' => 'manual',
                ];
                if ($prepared['visual_direction'] !== null) {
                    $prepared['visual_direction']['visual_prompt'] = $manualPrompt;
                    $prepared['visual_direction']['negative_prompt'] = $prepared['negative_prompt'];
                }
            } else {
                $direction = $this->visualPrompts->prepare($entry, $agent, $identity);
                $prepared = [
                    'visual_prompt' => $direction['visual_prompt'],
                    'negative_prompt' => $direction['negative_prompt'],
                    'visual_subject' => $direction['subject'],
                    'visual_style' => $direction['visual_style'],
                    'visual_direction' => $direction,
                    'aspect_ratio' => '1:1',
                    'source' => 'ollama',
                ];
                if (array_key_exists('negative_prompt', $manualOverrides)) {
                    $prepared['negative_prompt'] = trim((string) $manualOverrides['negative_prompt']);
                    $prepared['visual_direction']['negative_prompt'] = $prepared['negative_prompt'];
                }
            }
        } catch (\Throwable $exception) {
            Log::error('Visual direction preparation failed; the publication text is preserved.', [
                'entry_id' => $entry->id,
                'error' => $exception->getMessage(),
            ]);
            $prepared = [
                'visual_prompt' => '',
                'negative_prompt' => (string) config('services.comfyui.negative_prompt', ''),
                'visual_subject' => trim((string) ($entry->title ?: 'Publication')),
                'visual_style' => '',
                'visual_direction' => null,
                'aspect_ratio' => '1:1',
                'source' => 'ollama_error',
            ];
            $visual = $this->recordFailedVisual(
                $entry,
                $latest,
                $prepared,
                $theme['label'],
                $exception->getMessage()
            );

            return ['visual' => $this->serializeVisual($visual), 'status' => 'failed'];
        }

        return $this->submitVisualWorkflow($entry, $latest, $theme, $prepared);
    }

    private function submitVisualWorkflow(
        UserAgentCalendarEntry $entry,
        ?UserAgentPublicationVisual $latest,
        array $theme,
        array $prepared
    ): array {
        Log::info('Visual prompt prepared for ComfyUI.', [
            'entry_id' => $entry->id,
            'source' => $prepared['source'] ?? 'unknown',
            'visual_subject' => $prepared['visual_subject'],
            'visual_style' => $prepared['visual_style'],
            'aspect_ratio' => $prepared['aspect_ratio'],
            'visual_scene' => $prepared['visual_direction']['scene'] ?? null,
        ]);

        $attributes = [
            'prompt' => $prepared['visual_prompt'],
            'negative_prompt' => $prepared['negative_prompt'],
            'visual_subject' => $prepared['visual_subject'],
            'visual_style' => $prepared['visual_style'],
            'aspect_ratio' => $prepared['aspect_ratio'],
            'visual_direction' => $prepared['visual_direction'] ?? null,
            'image_path' => null,
            'image_name' => null,
            'comfy_filename' => null,
            'comfy_subfolder' => null,
            'comfy_type' => null,
            'model' => config('services.comfyui.checkpoint'),
            'prompt_id' => null,
            'client_id' => null,
            'theme' => $theme['label'],
            'status' => 'pending',
            'error' => null,
            'started_at' => now(),
            'completed_at' => null,
        ];

        try {
            $result = $this->comfyui->submit($prepared['visual_prompt'], $prepared['negative_prompt']);
        } catch (\Throwable $exception) {
            Log::error('ComfyUI workflow submission failed.', [
                'entry_id' => $entry->id,
                'error' => $exception->getMessage(),
            ]);

            $visual = $this->recordFailedVisual(
                $entry,
                $latest,
                $prepared,
                $theme['label'],
                $exception->getMessage()
            );

            return ['visual' => $this->serializeVisual($visual), 'status' => 'failed'];
        }

        $attributes['prompt_id'] = $result['prompt_id'];
        $attributes['client_id'] = $result['client_id'] ?? null;

        $visual = $latest && $latest->status === 'failed'
            ? tap($latest)->update($attributes)
            : $entry->visuals()->create($attributes);

        $visual->refresh();

        Log::info('ComfyUI prompt_id saved; asynchronous history checks scheduled.', [
            'entry_id' => $entry->id,
            'visual_id' => $visual->id,
            'prompt_id' => $visual->prompt_id,
            'initial_check_delay_seconds' => 15,
        ]);

        CheckComfyVisualGeneration::dispatch($visual->id)->delay(now()->addSeconds(15));

        return [
            'visual' => $this->serializeVisual($visual),
            'status' => 'pending',
        ];
    }

    private function generatePublicationContentWithOllama(UserAgentCalendarEntry $entry, UserAgent $agent, $identity): array
    {
        $availableModels = $this->ollama->models();

        if (empty($availableModels)) {
            return ['ok' => false, 'error' => 'Le moteur de génération est injoignable ou aucun modèle n\'est installé.'];
        }

        $model = config('services.ollama.model') ?? $availableModels[0];

        if (!in_array($model, $availableModels, true)) {
            $model = $availableModels[0];
        }

        $prompt = $this->buildPublicationOllamaPrompt($entry, $agent, $identity);

        $result = $this->ollama->generate($model, $prompt, null, 800);

        if (!($result['ok'] ?? false)) {
            return ['ok' => false, 'error' => $result['error'] ?? 'Erreur lors de la génération.'];
        }

        $raw = $result['raw'] ?? '';
        $data = $this->parseOllamaResponse($raw);

        // Validate that we have the required fields
        $requiredFields = ['caption', 'hashtags', 'visual_prompt'];
        $hasRequired = true;
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || !is_string($data[$field]) || trim($data[$field]) === '') {
                $hasRequired = false;
                break;
            }
        }

        if (!$hasRequired) {
            \Log::warning('Ollama response missing required fields', ['raw' => $raw, 'parsed' => $data]);
            return ['ok' => false, 'error' => 'Réponse Ollama invalide ou incomplète.'];
        }

        return ['ok' => true, 'data' => $data];
    }

    private function parseOllamaResponse(string $raw): array
    {
        $data = [];

        // Try to parse JSON from the response
        $jsonStart = strpos($raw, '{');
        $jsonEnd = strrpos($raw, '}');

        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $jsonStr = substr($raw, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($jsonStr, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data = $decoded;
            }
        }

        // If JSON parsing failed or missing fields, try to extract from raw text
        if (empty($data['caption']) || empty($data['hashtags']) || empty($data['visual_prompt'])) {
            $extracted = $this->extractFieldsFromText($raw);
            $data = array_merge($data, $extracted);
        }

        return $data;
    }

    private function extractFieldsFromText(string $text): array
    {
        $data = [];

        // Try to find caption
        if (preg_match('/["\']caption["\']\s*:\s*["\']([^"\']+)["\']/i', $text, $m)) {
            $data['caption'] = trim($m[1]);
        } elseif (preg_match('/légende\s*[:\-]\s*(.+)/i', $text, $m)) {
            $data['caption'] = trim($m[1]);
        }

        // Try to find hashtags
        if (preg_match('/["\']hashtags["\']\s*:\s*["\']([^"\']+)["\']/i', $text, $m)) {
            $data['hashtags'] = trim($m[1]);
        } elseif (preg_match('/hashtags?\s*[:\-]\s*(.+)/i', $text, $m)) {
            $data['hashtags'] = trim($m[1]);
        }

        // Try to find visual_prompt
        if (preg_match('/["\']visual_prompt["\']\s*:\s*["\']([^"\']+)["\']/i', $text, $m)) {
            $data['visual_prompt'] = trim($m[1]);
        } elseif (preg_match('/prompt\s*visuel\s*[:\-]\s*(.+)/i', $text, $m)) {
            $data['visual_prompt'] = trim($m[1]);
        } elseif (preg_match('/["\']prompt["\']\s*:\s*["\']([^"\']+)["\']/i', $text, $m)) {
            $data['visual_prompt'] = trim($m[1]);
        }

        // Try to find cta
        if (preg_match('/["\']cta["\']\s*:\s*["\']([^"\']+)["\']/i', $text, $m)) {
            $data['cta'] = trim($m[1]);
        }

        return $data;
    }

    private function buildPublicationOllamaPrompt(UserAgentCalendarEntry $entry, UserAgent $agent, $identity): string
    {
        $agentModelName = $agent->agent?->name ?: 'Non défini';
        $agentDescription = trim((string) ($agent->agent?->description ?? ''));
        $categoryName = $agent->agent?->category?->name ?: 'Non définie';
        $agentName = $agent->custom_name ?: $agentModelName;
        $domain = $agent->domain ?: 'Non défini';
        $tone = $agent->tone ?: 'Non défini';
        $pillar = $entry->pillar ?: 'Non défini';
        $title = $entry->title ?: 'Publication';
        $objective = trim((string) ($identity?->objective ?? ''));
        $secteurActivite = trim((string) ($identity?->secteur_activite ?? ''));
        $localisation = trim((string) ($identity?->localisation ?? ''));
        $cible = trim((string) ($identity?->cible ?? ''));
        $charter = trim((string) ($identity?->charter ?? ''));
        $logo = $identity?->logo_path ? 'Oui' : 'Non';

        $palette = collect($identity?->palette ?? [])
            ->map(function ($color) {
                $name = trim((string) ($color['name'] ?? ''));
                $hex = trim((string) ($color['hex'] ?? ''));

                if ($name === '' || $name === $hex) {
                    return $hex;
                }

                return $name . ' ' . $hex;
            })
            ->filter()
            ->implode(', ');

        $palette = $palette !== '' ? $palette : 'Non définie';

        $accounts = $agent->inspiringAccounts ?? collect();
        $inspiring = $accounts
            ->map(function ($account) {
                $label = trim(($account->platform ?? '') . ' ' . ($account->handle ?? ''));
                $notes = trim((string) ($account->notes ?? ''));

                return $notes !== '' ? $label . ' — ' . $notes : $label;
            })
            ->filter()
            ->implode(' ; ');

        $inspiring = $inspiring !== '' ? $inspiring : 'Aucun compte de référence renseigné';

        $contacts = $agent->contacts ?? collect();
        $contactsStr = $contacts
            ->map(function ($contact) {
                $label = trim(($contact->name ?? '') . ' — ' . ($contact->phone ?? ''));
                $notes = trim((string) ($contact->notes ?? ''));

                return $notes !== '' ? $label . ' (' . $notes . ')' : $label;
            })
            ->filter()
            ->implode(' ; ');

        $contactsStr = $contactsStr !== '' ? $contactsStr : 'Aucun contact renseigné';

        $websites = $agent->websites ?? collect();
        $websitesStr = $websites
            ->map(function ($website) {
                $label = trim(($website->label ?? '') . ' — ' . ($website->url ?? ''));
                $notes = trim((string) ($website->notes ?? ''));

                return $notes !== '' ? $label . ' (' . $notes . ')' : $label;
            })
            ->filter()
            ->implode(' ; ');

        $websitesStr = $websitesStr !== '' ? $websitesStr : 'Aucun site web renseigné';

        $images = $agent->identityImages ?? collect();
        $imageDescriptions = $images
            ->map(function ($img) {
                return $img->caption ?: 'Image de référence';
            })
            ->filter()
            ->implode(' ; ');

        $imageDescriptions = $imageDescriptions !== '' ? $imageDescriptions : 'Aucune image de référence';

        $agentDescription = $agentDescription !== '' ? $agentDescription : 'Non renseignée';
        $charter = $charter !== '' ? $charter : 'Aucune charte particulière : respecte la tonalité et la palette.';
        $objective = $objective !== '' ? $objective : 'Non renseigné';
        $secteurActivite = $secteurActivite !== '' ? $secteurActivite : 'Non renseigné';
        $localisation = $localisation !== '' ? $localisation : 'Non renseignée';
        $cible = $cible !== '' ? $cible : 'Non renseignée';

        return <<<PROMPT
Tu es un stratège éditorial expert en réseaux sociaux pour une agence de communication digitale.

Rédige le contenu d'une seule publication Facebook pour la marque de l'utilisateur.

=== CONTEXTE DE L'AGENT ===
Nom affiché: {$agentName}
Modèle d'agent: {$agentModelName}
Catégorie: {$categoryName}
Description du modèle d'agent: {$agentDescription}
Domaine d'activité: {$domain}
Tonalité: {$tone}

=== IDENTITÉ DE LA MARQUE (à respecter impérativement) ===
Objectif principal: {$objective}
Secteur d'activité: {$secteurActivite}
Localisation: {$localisation}
Cible commerciale: {$cible}
Charte éditoriale: {$charter}
Palette & couleurs: {$palette}
Logo disponible: {$logo}
Sites web: {$websitesStr}
Contacts: {$contactsStr}
Images de référence: {$imageDescriptions}

=== COMPTES INSPIRANTS (référence de style, ne pas citer) ===
{$inspiring}

=== PUBLICATION À RÉDIGER ===
Pilier: {$pillar}
Titre: {$title}

=== CONTRAINTES ===
- Plateforme: Facebook uniquement.
- La publication doit faire progresser l'objectif principal ci-dessus.
- Le sujet provient du domaine d'activité, de l'objectif, de la charte, des comptes inspirants et des images de référence.
- Interdit de mentionner l'application, l'agent IA, le calendrier, les piliers, la tonalité ou un quelconque réglage interne.
- N'invente aucune fonctionnalité produit ni aucun service non lié au domaine d'activité.
- Rédige une légende concise (2 phrases maximum) prête à publier, avec 3 à 5 hashtags pertinents.
- Donne un "visual_prompt" concret et descriptif (15-25 mots) : une scène réelle liée au sujet de la publication (lieu, objet, action, lumière, personnages), cohérente avec la charte et la palette. Le visuel sera généré comme une affiche publicitaire professionnelle format portrait 4:5 (style agence de communication).
- Le "visual_prompt" ne doit jamais décrire une interface (écran, application, bouton, menu, calendrier, logiciel, capture d'écran) ni contenir de texte à afficher.

=== FORMAT DE RÉPONSE (JSON UNIQUEMENT) ===
{"caption":"Légende concise","hashtags":"#hashtag1 #hashtag2 #hashtag3","visual_prompt":"Prompt visuel détaillé 15-25 mots"}
PROMPT;
    }

    public function generateVisual(Request $request, UserAgentCalendarEntry $entry): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        abort_unless($userAgentIds->contains($entry->user_agent_id), 403);
        abort_unless($entry->status === 'validated', 422, 'Cette publication doit être validée avant génération du visuel.');

        $manualOverrides = $request->validate([
            'visual_prompt' => 'sometimes|required|string|max:2000',
            'negative_prompt' => 'sometimes|nullable|string|max:2000',
        ]);
        $entry->load(['userAgent.agent', 'userAgent.identity', 'userAgent.identityImages']);
        $result = $this->generateVisualForEntry(
            $entry,
            $entry->userAgent,
            $entry->userAgent->identity,
            $manualOverrides
        );
        $status = $result['status'];
        $httpStatus = $status === 'failed' ? 502 : ($status === 'generated' ? 200 : 202);

        return response()->json([
            'success' => $status !== 'failed',
            'message' => $status === 'failed'
                ? ($result['visual']['error'] ?? 'Échec de la génération du visuel.')
                : 'Le traitement asynchrone du visuel est enregistré.',
            'visual' => $result['visual'],
            'status' => $status,
        ], $httpStatus);
    }

    public function visualStatus(UserAgentPublicationVisual $visual): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        abort_unless($userAgentIds->contains($visual->entry->user_agent_id), 403);

        if ($visual->status === 'generated') {
            $disk = Storage::disk('public');
            if (empty($visual->image_path) || ! $disk->exists($visual->image_path) || $disk->size($visual->image_path) <= 0) {
                $visual->update([
                    'status' => 'failed',
                    'error' => 'Le fichier image enregistré est absent ou vide. Relancez la génération du visuel.',
                    'completed_at' => now(),
                ]);
            }
        }

        if ($visual->status === 'pending' && empty($visual->prompt_id)) {
            $visual->update([
                'status' => 'failed',
                'error' => 'Identifiant ComfyUI manquant.',
                'completed_at' => now(),
            ]);
        }

        Log::debug('Serving persisted visual generation status to the Studio.', [
            'visual_id' => $visual->id,
            'prompt_id' => $visual->prompt_id,
            'status' => $visual->status,
        ]);

        return response()->json([
            'success' => true,
            'status' => $visual->status,
            'visual' => $this->serializeVisual($visual->refresh()),
            'message' => $visual->error,
        ]);
    }

    public function save(Request $request, UserAgentCalendarEntry $entry): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        abort_unless($userAgentIds->contains($entry->user_agent_id), 403);
        abort_unless($entry->status === 'validated', 422, 'Cette publication doit être validée avant enregistrement.');

        $validated = $request->validate([
            'generated_content' => 'nullable|string',
        ]);

        $entry->update([
            'generated_content' => $validated['generated_content'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contenu enregistré dans l\'entrée.',
            'entry' => $this->serializeEntry($entry->fresh()),
        ]);
    }

    public function publish(UserAgentCalendarEntry $entry, Request $request): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        abort_unless($userAgentIds->contains($entry->user_agent_id), 403);
        abort_unless(empty($entry->platform_post_id), 422, 'Cette publication est déjà publiée.');

        $platform = strtolower((string) $entry->platform);
        abort_unless(in_array($platform, ['facebook', 'instagram']), 422, 'Plateforme non supportée pour la publication automatique.');

        $message = trim((string) ($entry->generated_content ?: $this->buildPublishText($entry)));
        abort_if($message === '', 422, 'Aucun contenu à publier. Générez d’abord la publication.');

        $visual = $entry->visuals()->where('status', 'generated')->first();

        if ($platform === 'instagram') {
            abort_if(! $visual || ! $visual->image_path, 422, 'Instagram nécessite un visuel généré avant publication.');
        }

        // Récupération du ou des comptes Meta sélectionnés
        $selectedIds = $request->input('meta_account_ids');
        if (! is_array($selectedIds) || empty($selectedIds)) {
            $selectedIds = [$request->input('meta_account_id')];
        }
        $selectedIds = array_filter(array_map('intval', $selectedIds));
        abort_if(empty($selectedIds), 422, 'Veuillez sélectionner au moins un compte Meta.');

        $accounts = MetaAccount::whereIn('id', $selectedIds)
            ->where('user_id', Auth::id())
            ->where('status', 'connected')
            ->get();

        abort_if($accounts->isEmpty(), 422, 'Aucun compte Meta valide sélectionné.');

        $publishedOn = [];
        $errors = [];

        foreach ($accounts as $account) {
            if ($account->platform !== $platform) {
                $errors[] = 'Le compte « '.$account->name.' » est sur '.$account->platform.', pas sur '.$platform.'.';
                continue;
            }
            if (empty($account->access_token)) {
                $errors[] = 'Le compte « '.$account->name.' » ne possède pas de jeton d’accès.';
                continue;
            }

            try {
                if ($platform === 'instagram') {
                    $result = $this->meta->publishInstagram($account, $message, url('storage/' . $visual->image_path));
                } else {
                    $result = $this->meta->publishFacebook(
                        $account,
                        $message,
                        $visual?->image_path,
                        $visual?->image_name
                    );
                }

                $publishedOn[] = [
                    'account' => $account->name,
                    'platform' => $account->platform,
                    'post_id' => $result['id'] ?? null,
                    'permalink' => $result['permalink'] ?? null,
                ];

                // Mettre à jour l'entrée avec le premier compte publié
                if (empty($entry->platform_post_id)) {
                    $entry->update([
                        'status' => 'published',
                        'meta_account_id' => $account->id,
                        'platform_post_id' => $result['id'] ?? null,
                        'published_permalink' => $result['permalink'] ?? null,
                    ]);
                }
            } catch (\Throwable $e) {
                report($e);
                $errors[] = 'Échec sur « '.$account->name.' » : '.$e->getMessage();
            }
        }

        if (! empty($errors) && empty($publishedOn)) {
            return response()->json([
                'success' => false,
                'message' => implode(' ', $errors),
            ], 502);
        }

        return response()->json([
            'success' => true,
            'message' => count($publishedOn) > 1
                ? 'Publication publiée sur '.count($publishedOn).' compte(s).'
                : 'Publication publiée sur '.$publishedOn[0]['account'].'.',
            'published_on' => $publishedOn,
            'errors' => $errors,
            'entry' => $this->serializeEntry($entry->fresh(['userAgent.agent', 'visuals'])),
        ]);
    }

    public function insights(UserAgentCalendarEntry $entry): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        abort_unless($userAgentIds->contains($entry->user_agent_id), 403);
        abort_unless(! empty($entry->platform_post_id), 422, 'Cette publication n’a pas encore été publiée.');

        $platform = strtolower((string) $entry->platform);
        $account = $this->resolveAccount($entry, $platform);

        try {
            $stats = $this->meta->getInsights($account, $entry->platform_post_id, $platform);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 502);
        }

        return response()->json([
            'success' => true,
            'insights' => $stats,
        ]);
    }

    public function comments(UserAgentCalendarEntry $entry): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        abort_unless($userAgentIds->contains($entry->user_agent_id), 403);
        abort_unless(! empty($entry->platform_post_id), 422, 'Cette publication n’a pas encore été publiée.');

        $platform = strtolower((string) $entry->platform);
        $account = $this->resolveAccount($entry, $platform);

        try {
            $items = $this->meta->getComments($account, $entry->platform_post_id, $platform);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 502);
        }

        return response()->json([
            'success' => true,
            'comments' => $items,
        ]);
    }

    public function replyComment(Request $request, UserAgentCalendarEntry $entry): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        abort_unless($userAgentIds->contains($entry->user_agent_id), 403);
        abort_unless(! empty($entry->platform_post_id), 422, 'Cette publication n’a pas encore été publiée.');

        $validated = $request->validate([
            'comment_id' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $platform = strtolower((string) $entry->platform);
        $account = $this->resolveAccount($entry, $platform);

        try {
            $reply = $this->meta->replyToComment($account, $validated['comment_id'], $validated['message'], $platform);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 502);
        }

        return response()->json([
            'success' => true,
            'message' => 'Réponse publiée.',
            'reply' => $reply,
        ]);
    }

    private function resolveAccount(UserAgentCalendarEntry $entry, string $platform): MetaAccount
    {
        $account = $entry->meta_account_id
            ? MetaAccount::where('id', $entry->meta_account_id)
                ->where('user_id', Auth::id())
                ->where('platform', $platform)
                ->first()
            : null;

        if (! $account) {
            $account = Auth::user()->metaAccounts()
                ->where('platform', $platform)
                ->where('status', 'connected')
                ->first();
        }

        $label = $platform === 'instagram' ? 'Instagram' : 'Facebook';

        abort_if(! $account, 422, 'Aucun compte Meta connecté pour ' . $label . '. Connectez-le dans la rubrique Comptes Meta.');
        abort_if(empty($account->access_token), 422, 'Le compte Meta sélectionné ne possède pas de jeton d’accès. Renseignez-le dans la rubrique Comptes Meta.');

        return $account;
    }

    private function buildPublishText(UserAgentCalendarEntry $entry): string
    {
        return implode("\n\n", array_filter([
            $entry->title,
            $entry->caption,
            $entry->hashtags,
        ]));
    }

    private function monthlyTheme(?Carbon $date = null): array
    {
        if ($date === null) {
            $date = Carbon::now();
        }

        $themes = [
            1 => ['label' => 'Nouvelle année', 'keywords' => 'objectifs, nouveau départ, élan, bleu hivernal, confettis'],
            2 => ['label' => 'Saint-Valentin', 'keywords' => 'relations, proximité, rouge et rose, cœurs, élégance'],
            3 => ['label' => 'Printemps', 'keywords' => 'renouveau, croissance, vert tendre, fleurs, lumière douce'],
            4 => ['label' => 'Avril', 'keywords' => 'innovation, projets, pluie de printemps, bleu-vert, fraîcheur'],
            5 => ['label' => 'Mai', 'keywords' => 'solidarité, travail, floral, vert et rose, énergie'],
            6 => ['label' => 'Été', 'keywords' => 'vacances, soleil, énergie, jaune et orange, chaleur'],
            7 => ['label' => 'Juillet', 'keywords' => 'liberté, été, bleu et corail, voyage, luminosité'],
            8 => ['label' => 'Août', 'keywords' => 'détente, vacances, turquoise, sable, douceur'],
            9 => ['label' => 'Rentrée', 'keywords' => 'organisation, productivité, rentrée, orange et bleu, dynamisme'],
            10 => ['label' => 'Automne & Halloween', 'keywords' => 'automne, récolte, Halloween, orange, violet, feuilles'],
            11 => ['label' => 'Novembre', 'keywords' => 'préparation fin d\'année, gratitude, marron, orange, sobriété'],
            12 => ['label' => 'Noël & fêtes', 'keywords' => 'Noël, fêtes, hiver, rouge et vert, or, sapins, lumières'],
        ];

        $month = (int) $date->format('n');
        $theme = $themes[$month] ?? $themes[1];
        $theme['month'] = $date->format('F Y');

        return $theme;
    }

    private function buildVisualPrompt(UserAgentCalendarEntry $entry, UserAgent $agent, $identity, array $theme): string
    {
        $domain = trim((string) ($agent->domain ?? ''));
        $pillar = trim((string) ($entry->pillar ?? ''));
        $title = trim((string) ($entry->title ?? ''));
        $caption = trim((string) ($entry->caption ?? ''));
        $visualPrompt = $entry->studio_visual_prompt ?: ($entry->visual_prompt ?? '');
        $visual = trim((string) $visualPrompt);
        $agentName = $agent->custom_name ?: ($agent->agent?->name ?? 'Agent IA');
        $brandName = $identity?->objective ? $this->extractBrandName($identity->objective) : $agentName;
        $palette = $this->describePalette($identity?->palette ?? []);
        $primaryColor = $this->getPrimaryBrandColor($identity?->palette ?? []);
        $secondaryColor = $this->getSecondaryBrandColor($identity?->palette ?? []);

        $primaryColorName = $this->describeColor($primaryColor);
        $secondaryColorName = $this->describeColor($secondaryColor);

        $theme = $entry->published_at ? $this->monthlyTheme($entry->published_at) : ['label' => '', 'keywords' => ''];
        $keywords = trim((string) ($theme['keywords'] ?? ''));

        $subject = $this->resolveVisualSubject($visual, $title, $caption, $pillar);
        $styleTokens = $this->visualStyleTokens($pillar);

        $pillarPrompts = $this->getPillarVisualDetails($pillar, $title, $caption, $agentName, $brandName, $primaryColor, $secondaryColor, $primaryColorName, $secondaryColorName, $keywords);

        $colorNames = $this->getColorNames($identity?->palette ?? []);
        $colorNamesStr = implode(' et ', $colorNames);

        $parts = [
            $pillarPrompts['composition'],
            $pillarPrompts['character'],
            $pillarPrompts['textLayout'],
            str_replace([$primaryColor, $secondaryColor], [$primaryColorName, $secondaryColorName], $pillarPrompts['colorScheme']),
            "Style: {$styleTokens}",
            $colorNamesStr !== '' ? "couleurs de la marque: {$colorNamesStr}" : '',
            $domain !== '' ? "univers {$domain}" : '',
            $subject !== '' ? "Sujet visuel: {$subject}" : '',
            $keywords !== '' ? "Ambiance: {$keywords}" : '',
            'Format: Portrait 4:5, haute définition, qualité agence de communication professionnelle',
            'Rendu: Ultra photoréaliste, éclairage studio professionnel, détails nets, composition équilibrée et aérée',
        ];

        $prompt = trim(preg_replace('/\s+/', ' ', implode('. ', array_filter($parts))));

        return rtrim($prompt, '.') . '.';
    }

    private function extractBrandName(string $objective): string
    {
        if (preg_match('/\b(GHOSTROAR|Ghostroar|[A-Z]{2,})\b/u', $objective, $matches)) {
            return strtoupper($matches[1]);
        }
        return 'VOTRE MARQUE';
    }

    private function getPrimaryBrandColor(array $palette): string
    {
        foreach ($palette as $color) {
            $hex = is_array($color) ? (string) ($color['hex'] ?? '') : (string) $color;
            $hex = ltrim($hex, '#');
            if (preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
                return '#' . strtoupper($hex);
            }
        }
        return '#299B16';
    }

    private function getSecondaryBrandColor(array $palette): string
    {
        $colors = [];
        foreach ($palette as $color) {
            $hex = is_array($color) ? (string) ($color['hex'] ?? '') : (string) $color;
            $hex = ltrim($hex, '#');
            if (preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
                $colors[] = '#' . strtoupper($hex);
            }
        }
        return $colors[1] ?? '#0A3D06';
    }

    private function getPillarVisualDetails(string $pillar, string $title, string $caption, string $agentName, string $brandName, string $primaryColor, string $secondaryColor, string $primaryColorName, string $secondaryColorName, string $keywords): array
    {
        $key = Str::ascii(mb_strtolower($pillar));

        $base = [
            'composition' => "Créer une affiche publicitaire professionnelle haut de gamme au format portrait 4:5, destinée aux réseaux sociaux, pour une agence de communication digitale appelée « {$brandName} »",
            'character' => '',
            'textLayout' => '',
            'colorScheme' => "Palette de couleurs composée principalement de {$primaryColorName} et {$secondaryColorName}, noir et blanc. Fond blanc lumineux avec de légers éléments graphiques géométriques très discrets. Design corporate premium, minimaliste, élégant et moderne.",
        ];

        if (str_contains($key, 'pedagog') || str_contains($key, 'conseil') || str_contains($key, 'astuce') || str_contains($key, 'guide')) {
            $base['character'] = "Sur la partie droite, une jeune femme africaine élégante et professionnelle, coiffure courte moderne, chemise {$primaryColorName} élégante à manches longues, pantalon noir ajusté, escarpins noirs. Posture debout, bras croisés, expression confiante, regard vers l'objectif. Photographie photoréaliste qualité studio, visible de la tête aux pieds.";
            $base['textLayout'] = "En haut à gauche: Logo typographique « {$brandName} » avec le nom en {$primaryColorName} et accents en noir. Titre principal: « {$title} » en grand, {$primaryColorName}. Liste de 5 conseils verticale à gauche, chacun avec icône {$primaryColorName} dans carré blanc arrondi ombré. Titres conseils en vert foncé gras majuscules, explications en noir avec mots-clés en {$primaryColorName}. Bas gauche: encadré horizontal icône ampoule blanche sur fond {$primaryColorName}, texte: « Réfléchis aujourd'hui, PERFORME DEMAIN » (2ème ligne {$secondaryColorName} gras).";
        } elseif (str_contains($key, 'preuve') || str_contains($key, 'resultat') || str_contains($key, 'cas') || str_contains($key, 'temoignage')) {
            $base['character'] = "Au centre, une mise en scène professionnelle sur le terrain: résultat concret, documents, tableau de bord ou écran montrant des métriques. Éclairage naturel, contexte réel, qualité reportage corporate. ambiance saisonnière";
            $base['textLayout'] = "Titre: « {$title} » en {$primaryColorName} gras. Sous-titre: « {$caption} » en noir. Citation client ou chiffre clé en grand sur fond {$primaryColorName} texte blanc. Logo {$brandName} en haut à gauche. Bas: appel à l'action « Découvrir nos résultats » sur bouton {$primaryColorName}.";
        } elseif (str_contains($key, 'offre') || str_contains($key, 'service') || str_contains($key, 'produit') || str_contains($key, 'solution')) {
            $base['character'] = "Mise en scène élégante du service/produit: interface épurée sur appareil, ou concept abstrait stylisé (flèches, graphiques, cubes 3D) en {$primaryColorName} et {$secondaryColorName}. Composition soignée, fond blanc, ombres portées subtiles.";
            $base['textLayout'] = "Titre: « {$title} » en {$primaryColorName}. Description courte: « {$caption} » en noir. 3 bénéfices clés en liste avec icônes {$primaryColorName}. Bouton CTA « En savoir plus » en {$primaryColorName} texte blanc. Logo {$brandName} discret en haut.";
        } elseif (str_contains($key, 'coulisse') || str_contains($key, 'equipe') || str_contains($key, 'derriere') || str_contains($key, 'making')) {
            $base['character'] = "Photo d'équipe authentique en situation de travail: 2-3 personnes collaborant, écrans visibles, ambiance chaleureuse. Tenues professionnelles décontractées. Lumière naturelle traversant les fenêtres, rendu documentaire corporate.";
            $base['textLayout'] = "Titre: « {$title} » en {$primaryColorName}. Légende: « {$caption} » en noir. Citation d'un membre d'équipe en italique. Logo {$brandName} en bas à droite. Hashtags discrets en {$primaryColorName}.";
        } elseif (str_contains($key, 'inspir') || str_contains($key, 'motivat') || str_contains($key, 'vision') || str_contains($key, 'reve')) {
            $base['character'] = "Visuel lifestyle inspirant: personne face à l'horizon, ou espace de travail épuré baigné de lumière, plante verte, carnet ouvert. Ambiance sereine, aspirante. Palette douce avec accents {$primaryColorName}.";
            $base['textLayout'] = "Citation inspirante en grand: « {$title} » en {$primaryColorName} sur fond blanc. Auteur/contexte en petit en noir. Logo {$brandName} en bas à gauche. Ligne finale: « Construisons ensemble » en {$secondaryColorName} gras.";
        } else {
            $base['character'] = "Composition éditoriale soignée, contexte professionnel. Éléments graphiques abstraits ({$primaryColorName}, {$secondaryColorName}) évoquant le domaine d'activité. Fond blanc, géométrie épurée.";
            $base['textLayout'] = "Titre: « {$title} » en {$primaryColorName} gras. Sous-titre: « {$caption} » en noir. Logo {$brandName} en haut à gauche. Bas: CTA « En savoir plus » sur fond {$primaryColorName} texte blanc.";
        }

        return $base;
    }

    private function resolveVisualSubject(string $visual, string $title, string $caption, string $pillar): string
    {
        $visual = rtrim($visual, " .");

        if ($visual === '') {
            $visual = $title !== '' ? $title : ($pillar !== '' ? $pillar : 'scène professionnelle');
        }

        return $visual;
    }

    private function visualStyleTokens(string $pillar): string
    {
        $key = Str::ascii(mb_strtolower($pillar));

        $tokens = [
            'pedagog' => 'illustration pédagogique claire, schéma simple, contexte de bureau',
            'preuve' => 'photo professionnelle sur le terrain, résultat concret, contexte réel',
            'offre' => 'mise en scène de produit élégante, service mis en valeur, contexte soigné',
            'coulisse' => "photo d'équipe en situation de travail, ambiance authentique",
            'inspir' => 'visuel lifestyle inspirant, lumière douce, ambiance chaleureuse',
        ];

        foreach ($tokens as $needle => $token) {
            if ($key !== '' && str_contains($key, $needle)) {
                return $token;
            }
        }

        return 'composition éditoriale soignée, contexte professionnel';
    }

    private function describePalette($colors): string
    {
        $names = [];

        foreach ($colors as $color) {
            $hex = is_array($color) ? (string) ($color['hex'] ?? '') : (string) $color;
            $name = $this->describeColor($hex);

            if ($name !== '' && !in_array($name, $names, true)) {
                $names[] = $name;
            }
        }

        return implode(' et ', array_slice($names, 0, 3));
    }

    private function getColorNames(array $colors): array
    {
        $names = [];

        foreach ($colors as $color) {
            $hex = is_array($color) ? (string) ($color['hex'] ?? '') : (string) $color;
            $name = $this->describeColor($hex);

            if ($name !== '' && !in_array($name, $names, true)) {
                $names[] = $name;
            }
        }

        return array_slice($names, 0, 3);
    }

    private function describeColor(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');

        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return '';
        }

        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        $palette = [
            'rouge' => [220, 40, 40],
            'orange' => [240, 140, 40],
            'jaune' => [240, 210, 50],
            'vert' => [40, 180, 70],
            'turquoise' => [40, 200, 200],
            'cyan' => [40, 190, 230],
            'bleu' => [40, 90, 220],
            'violet' => [140, 70, 200],
            'rose' => [230, 90, 160],
            'marron' => [130, 80, 50],
            'gris' => [140, 140, 140],
            'blanc' => [245, 245, 245],
            'noir' => [25, 25, 25],
            'or' => [210, 170, 60],
            'argent' => [180, 180, 190],
        ];

        $best = '';
        $bestDistance = PHP_INT_MAX;

        foreach ($palette as $name => $rgb) {
            $distance = ($red - $rgb[0]) ** 2 + ($green - $rgb[1]) ** 2 + ($blue - $rgb[2]) ** 2;

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $best = $name;
            }
        }

        return $best;
    }

    private function serializeEntry(UserAgentCalendarEntry $entry): array
    {
        $agentName = $entry->userAgent->custom_name
            ?: ($entry->userAgent->agent?->name ?? 'Agent IA');
        $visual = $entry->visuals->first();
        $visualDirection = $visual?->visual_direction ?? [];

        return [
            'id' => $entry->id,
            'user_agent_id' => $entry->user_agent_id,
            'agent_name' => $agentName,
            'published_at' => $entry->published_at->format('Y-m-d\TH:i:s'),
            'platform' => $entry->platform,
            'pillar' => $entry->pillar,
            'title' => $entry->title,
            // Calendar planning fields (read-only)
            'calendar_caption' => $entry->caption,
            'calendar_hashtags' => $entry->hashtags,
            'calendar_visual_prompt' => $entry->visual_prompt,
            // Studio production fields (AI-generated)
            'caption' => $entry->studio_caption,
            'hashtags' => $entry->studio_hashtags,
            'visual_prompt' => $entry->studio_visual_prompt,
            'cta' => $entry->studio_cta,
            'generated_content' => $entry->generated_content,
            'visual_prompt_used' => $visual?->prompt,
            'visual_negative_prompt' => $visual?->negative_prompt,
            'visual_direction' => $visualDirection,
            'visual_scene' => $visualDirection['scene'] ?? null,
            'studio_generated' => $entry->studio_generated,
            'studio_generated_at' => $entry->studio_generated_at?->format('Y-m-d\TH:i:s'),
            'status' => $entry->status,
            'platform_post_id' => $entry->platform_post_id,
            'published_permalink' => $entry->published_permalink,
            'is_published' => ! empty($entry->platform_post_id),
            'visual_id' => $visual?->id,
            'visual_status' => $visual?->status,
            'visual_prompt_status' => $visual?->prompt ? 'prepared' : null,
            'visual_subject' => $visual?->visual_subject,
            'visual_style' => $visual?->visual_style,
            'aspect_ratio' => $visual?->aspect_ratio,
            'visual_url' => ($visual && $visual->image_path) ? url('storage/' . $visual->image_path) : null,
            'visual_download_url' => ($visual && $visual->image_path) ? url('storage/' . $visual->image_path) : null,
            'visual_name' => $visual?->image_name,
            'visual_theme' => $visual?->theme,
            'visual_error' => $visual?->error,
        ];
    }

    private function serializeVisual(UserAgentPublicationVisual $visual): array
    {
        return [
            'id' => $visual->id,
            'url' => $visual->image_path ? url('storage/' . $visual->image_path) : null,
            'prompt' => $visual->prompt,
            'negative_prompt' => $visual->negative_prompt,
            'theme' => $visual->theme,
            'model' => $visual->model,
            'status' => $visual->status,
            'error' => $visual->error,
            'filename' => $visual->image_name,
            'download_url' => $visual->image_path ? url('storage/' . $visual->image_path) : null,
            'visual_subject' => $visual->visual_subject,
            'visual_style' => $visual->visual_style,
            'aspect_ratio' => $visual->aspect_ratio,
            'visual_direction' => $visual->visual_direction,
            'visual_scene' => $visual->visual_direction['scene'] ?? null,
            'prompt_status' => $visual->prompt ? 'prepared' : 'pending',
            'started_at' => $visual->started_at?->toIso8601String(),
            'completed_at' => $visual->completed_at?->toIso8601String(),
            'created_at' => $visual->created_at->toIso8601String(),
        ];
    }

    private function recordFailedVisual(
        UserAgentCalendarEntry $entry,
        ?UserAgentPublicationVisual $latest,
        array $prepared,
        string $theme,
        string $error
    ): UserAgentPublicationVisual {
        $attributes = [
            'prompt' => $prepared['visual_prompt'],
            'negative_prompt' => $prepared['negative_prompt'],
            'visual_subject' => $prepared['visual_subject'],
            'visual_style' => $prepared['visual_style'],
            'aspect_ratio' => $prepared['aspect_ratio'],
            'visual_direction' => $prepared['visual_direction'] ?? null,
            'image_path' => null,
            'image_name' => null,
            'model' => config('services.comfyui.checkpoint'),
            'prompt_id' => null,
            'client_id' => null,
            'theme' => $theme,
            'status' => 'failed',
            'error' => $error,
            'started_at' => now(),
            'completed_at' => now(),
        ];

        if ($latest && $latest->status === 'failed') {
            $latest->update($attributes);

            return $latest->refresh();
        }

        return $entry->visuals()->create($attributes);
    }

    public function veille(): JsonResponse
    {
        $userId = Auth::id();
        $userAgentIds = UserAgent::where('user_id', $userId)->pluck('id');

        $entries = UserAgentCalendarEntry::whereIn('user_agent_id', $userAgentIds)
            ->whereIn('status', ['validated', 'published'])
            ->count();

        $published = UserAgentCalendarEntry::whereIn('user_agent_id', $userAgentIds)
            ->whereNotNull('platform_post_id')
            ->count();

        $validated = UserAgentCalendarEntry::whereIn('user_agent_id', $userAgentIds)
            ->where('status', 'validated')
            ->count();

        // Sujets émergents : piliers les plus utilisés cette semaine
        $topPillars = UserAgentCalendarEntry::whereIn('user_agent_id', $userAgentIds)
            ->whereIn('status', ['validated', 'published'])
            ->where('published_at', '>=', now()->subDays(7))
            ->whereNotNull('pillar')
            ->groupBy('pillar')
            ->selectRaw('pillar, count(*) as total')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        $signals = [];

        if ($topPillars->isNotEmpty()) {
            $top = $topPillars->first();
            $signals[] = [
                'key' => 'sujet',
                'title' => 'Sujet émergent',
                'text' => 'Le pilier « ' . e($top->pillar) . ' » domine vos publications cette semaine avec ' . (int) $top->total . ' publication(s).',
                'icon' => 'trending-up',
            ];
        } else {
            $signals[] = [
                'key' => 'sujet',
                'title' => 'Sujet émergent',
                'text' => 'Aucune publication validée cette semaine pour détecter un sujet émergent.',
                'icon' => 'trending-up',
            ];
        }

        // Comptes Meta connectés et leur activité (concurrents actifs)
        $metaAccounts = MetaAccount::where('user_id', $userId)
            ->where('status', 'connected')
            ->withCount(['calendarEntries as published_count' => function ($q) use ($userAgentIds) {
                $q->whereIn('user_agent_id', $userAgentIds)
                  ->whereNotNull('platform_post_id');
            }])
            ->get();

        $topAccount = $metaAccounts->sortByDesc('published_count')->first();
        if ($topAccount) {
            $signals[] = [
                'key' => 'concurrent',
                'title' => 'Compte le plus actif',
                'text' => '« ' . e($topAccount->name) . ' » (' . e($topAccount->platform) . ') totalise ' . (int) $topAccount->published_count . ' publication(s) publiée(s).',
                'icon' => 'alert-circle',
            ];
        } else {
            $signals[] = [
                'key' => 'concurrent',
                'title' => 'Compte le plus actif',
                'text' => 'Aucun compte Meta connecté avec publications.',
                'icon' => 'alert-circle',
            ];
        }

        // Formats gagnants : ratio des visuels publiés le plus utilisé
        $topFormat = UserAgentPublicationVisual::query()
            ->join('user_agent_calendar_entries as calendar_entries', 'calendar_entries.id', '=', 'user_agent_publication_visuals.calendar_entry_id')
            ->whereIn('calendar_entries.user_agent_id', $userAgentIds)
            ->whereIn('calendar_entries.status', ['validated', 'published'])
            ->whereNotNull('user_agent_publication_visuals.aspect_ratio')
            ->groupBy('user_agent_publication_visuals.aspect_ratio')
            ->selectRaw('user_agent_publication_visuals.aspect_ratio, count(*) as total')
            ->orderByDesc('total')
            ->limit(1)
            ->first();

        if ($topFormat) {
            $aspectRatio = $topFormat->aspect_ratio;
            $signals[] = [
                'key' => 'format',
                'title' => 'Format gagnant',
                'text' => 'Le format « ' . e($aspectRatio) . ' » est le plus utilisé avec ' . (int) $topFormat->total . ' publication(s).',
                'icon' => 'play-circle',
            ];
        } else {
            $signals[] = [
                'key' => 'format',
                'title' => 'Format gagnant',
                'text' => 'Pas assez de données pour déterminer un format gagnant.',
                'icon' => 'play-circle',
            ];
        }

        return response()->json([
            'success' => true,
            'entries' => (int) $entries,
            'published' => (int) $published,
            'validated' => (int) $validated,
            'signals' => $signals,
        ]);
    }

    public function subscription(): JsonResponse
    {
        $userId = Auth::id();
        $userAgentIds = UserAgent::where('user_id', $userId)->pluck('id');

        $publishedCount = UserAgentCalendarEntry::whereIn('user_agent_id', $userAgentIds)
            ->whereNotNull('platform_post_id')
            ->count();

        $validatedCount = UserAgentCalendarEntry::whereIn('user_agent_id', $userAgentIds)
            ->where('status', 'validated')
            ->count();

        $totalEntries = UserAgentCalendarEntry::whereIn('user_agent_id', $userAgentIds)
            ->count();

        // Récupérer l'abonnement actif associé aux agents de l'utilisateur
        $subscription = \App\Models\Subscription::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->first();

        $planName = $subscription ? $subscription->name : 'Gratuit';
        $planPrice = $subscription ? $subscription->price : 0;
        $planCurrency = $subscription ? $subscription->currency : 'EUR';
        $planInterval = $subscription ? $subscription->interval : 'month';
        $planFeatures = $subscription ? ($subscription->features ?? []) : [];
        $planSlug = $subscription ? $subscription->slug : 'free';

        // Quota de posts par défaut selon le plan
        $quotaLimit = match ($planSlug) {
            'premium' => 9999,
            'pro' => 500,
            'starter' => 100,
            default => 30,
        };

        $renewalDate = now()->addMonth(1)->format('d F Y');

        return response()->json([
            'success' => true,
            'subscription' => [
                'name' => $planName,
                'slug' => $planSlug,
                'price' => (float) $planPrice,
                'currency' => $planCurrency,
                'interval' => $planInterval,
                'features' => $planFeatures,
                'renewal_at' => $renewalDate,
            ],
            'quota' => [
                'used' => (int) $publishedCount,
                'limit' => $quotaLimit,
                'remaining' => max(0, $quotaLimit - (int) $publishedCount),
                'percent' => $quotaLimit > 0 ? round(((int) $publishedCount / $quotaLimit) * 100, 1) : 0,
            ],
            'stats' => [
                'total_entries' => (int) $totalEntries,
                'published' => (int) $publishedCount,
                'validated' => (int) $validatedCount,
            ],
        ]);
    }
}