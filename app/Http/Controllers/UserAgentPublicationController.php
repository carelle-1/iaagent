<?php

namespace App\Http\Controllers;

use App\Models\MetaAccount;
use App\Models\UserAgent;
use App\Models\UserAgentCalendarEntry;
use App\Models\UserAgentPublicationVisual;
use App\Services\ComfyUIClient;
use App\Services\MetaGraphService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserAgentPublicationController extends Controller
{
    public function __construct(
        private ComfyUIClient $comfyui,
        private MetaGraphService $meta,
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

    public function generate(UserAgentCalendarEntry $entry): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        abort_unless($userAgentIds->contains($entry->user_agent_id), 403);
        abort_unless($entry->status === 'validated', 422, 'Cette publication doit être validée avant génération.');

        $entry->load('userAgent.agent');

        $agentName = $entry->userAgent->custom_name
            ?: ($entry->userAgent->agent->name ?? 'Agent IA');

        $title = $entry->title ?: 'Publication';
        $caption = $entry->caption ?: '';
        $hashtags = $entry->hashtags ?: '';
        $visualPrompt = $entry->visual_prompt ?: 'Visuel à définir';

        $text = trim($title . "\n\n" . $caption . ($hashtags !== '' ? "\n\n" . $hashtags : ''));

        return response()->json([
            'success' => true,
            'message' => 'Publication générée.',
            'publication' => [
                'entry_id' => $entry->id,
                'agent' => $agentName,
                'platform' => $entry->platform,
                'pillar' => $entry->pillar,
                'published_at' => $entry->published_at->format('Y-m-d\TH:i:s'),
                'title' => $title,
                'caption' => $caption,
                'hashtags' => $hashtags,
                'visual_prompt' => $visualPrompt,
                'cta' => 'En savoir plus',
                'text' => $text,
                'status' => 'draft',
            ],
        ]);
    }

    public function generateVisual(UserAgentCalendarEntry $entry): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        abort_unless($userAgentIds->contains($entry->user_agent_id), 403);
        abort_unless($entry->status === 'validated', 422, 'Cette publication doit être validée avant génération du visuel.');

        $entry->load(['userAgent.agent', 'userAgent.identity']);

        $agent = $entry->userAgent;
        $identity = $agent->identity;
        $theme = $this->monthlyTheme($entry->published_at);
        $prompt = $this->buildVisualPrompt($entry, $agent, $identity, $theme);
        $negative = (string) config('services.comfyui.negative_prompt', '');

        $existing = $entry->visuals()
            ->whereIn('status', ['pending', 'generated'])
            ->first();

        if ($existing && $existing->status === 'pending') {
            return response()->json([
                'success' => true,
                'message' => 'Génération déjà en cours.',
                'visual' => $this->serializeVisual($existing),
                'status' => 'pending',
            ], 202);
        }

        if ($existing && $existing->status === 'generated') {
            return response()->json([
                'success' => true,
                'message' => 'Visuel déjà généré.',
                'visual' => $this->serializeVisual($existing),
                'status' => 'generated',
            ]);
        }

        try {
            $result = $this->comfyui->submit($prompt, $negative);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 502);
        }

        $visual = $entry->visuals()->create([
            'prompt' => $prompt,
            'negative_prompt' => $negative,
            'image_path' => null,
            'image_name' => null,
            'model' => $result['model'] ?? config('services.comfyui.checkpoint'),
            'prompt_id' => $result['prompt_id'],
            'client_id' => $result['client_id'] ?? null,
            'theme' => $theme['label'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Génération ComfyUI soumise.',
            'visual' => $this->serializeVisual($visual),
            'status' => 'pending',
        ], 202);
    }

    public function visualStatus(UserAgentPublicationVisual $visual): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        abort_unless($userAgentIds->contains($visual->entry->user_agent_id), 403);

        if ($visual->status === 'generated') {
            return response()->json([
                'success' => true,
                'status' => 'generated',
                'visual' => $this->serializeVisual($visual),
            ]);
        }

        if ($visual->status === 'failed') {
            return response()->json([
                'success' => true,
                'status' => 'failed',
                'visual' => $this->serializeVisual($visual),
                'message' => $visual->error ?: 'Génération échouée.',
            ]);
        }

        if (empty($visual->prompt_id)) {
            $visual->update(['status' => 'failed', 'error' => 'Identifiant ComfyUI manquant.']);

            return response()->json([
                'success' => true,
                'status' => 'failed',
                'visual' => $this->serializeVisual($visual->refresh()),
                'message' => 'Identifiant ComfyUI manquant.',
            ]);
        }

        try {
            $image = $this->comfyui->fetchImage($visual->prompt_id, (string) $visual->entry->user_agent_id);
        } catch (\Throwable $e) {
            report($e);

            $visual->update(['status' => 'failed', 'error' => $e->getMessage()]);

            return response()->json([
                'success' => true,
                'status' => 'failed',
                'visual' => $this->serializeVisual($visual->refresh()),
                'message' => $e->getMessage(),
            ]);
        }

        if ($image === null) {
            return response()->json([
                'success' => true,
                'status' => 'pending',
                'visual' => $this->serializeVisual($visual),
            ]);
        }

        $visual->update([
            'status' => 'generated',
            'image_path' => $image['path'],
            'image_name' => $image['filename'],
        ]);

        return response()->json([
            'success' => true,
            'status' => 'generated',
            'visual' => $this->serializeVisual($visual->refresh()),
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

    public function publish(UserAgentCalendarEntry $entry): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        abort_unless($userAgentIds->contains($entry->user_agent_id), 403);
        abort_unless(empty($entry->platform_post_id), 422, 'Cette publication est déjà publiée.');

        $platform = strtolower((string) $entry->platform);
        abort_unless(in_array($platform, ['facebook', 'instagram'], true), 422, 'La publication automatique n’est pas disponible pour cette plateforme.');

        $account = $this->resolveAccount($entry, $platform);

        $message = trim((string) ($entry->generated_content ?: $this->buildPublishText($entry)));
        abort_if($message === '', 422, 'Aucun contenu à publier. Générez d’abord la publication.');

        $visual = $entry->visuals()->where('status', 'generated')->first();

        if ($platform === 'instagram') {
            abort_if(! $visual || ! $visual->image_path, 422, 'Instagram nécessite un visuel généré avant publication.');
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
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 502);
        }

        $entry->update([
            'status' => 'published',
            'meta_account_id' => $account->id,
            'platform_post_id' => $result['id'],
            'published_permalink' => $result['permalink'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Publication publiée sur ' . ($platform === 'instagram' ? 'Instagram' : 'Facebook') . '.',
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

    private function monthlyTheme(Carbon $date): array
    {
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
        $agentName = $agent->custom_name ?: ($agent->agent?->name ?? 'Agent IA');
        $platform = strtolower((string) $entry->platform);
        $visual = rtrim($entry->visual_prompt ?: 'visuel professionnel', " .");
        $palette = implode(', ', array_filter(array_column($identity?->palette ?? [], 'hex')));
        $keywords = $theme['keywords'];

        $parts = [
            $visual,
            "pour {$agentName} ({$platform})",
            $palette !== '' ? "palette {$palette}" : '',
            "ambiance {$keywords}",
            'moderne, professionnel, haute qualité',
            'sans texte, sans logo, sans watermark',
        ];

        return trim(preg_replace('/\s+/', ' ', implode('. ', array_filter($parts)))) . '.';
    }

    private function serializeEntry(UserAgentCalendarEntry $entry): array
    {
        $agentName = $entry->userAgent->custom_name
            ?: ($entry->userAgent->agent?->name ?? 'Agent IA');
        $visual = $entry->visuals->first();

        return [
            'id' => $entry->id,
            'user_agent_id' => $entry->user_agent_id,
            'agent_name' => $agentName,
            'published_at' => $entry->published_at->format('Y-m-d\TH:i:s'),
            'platform' => $entry->platform,
            'pillar' => $entry->pillar,
            'title' => $entry->title,
            'caption' => $entry->caption,
            'generated_content' => $entry->generated_content,
            'visual_prompt' => $entry->visual_prompt,
            'visual_prompt_used' => $visual?->prompt,
            'hashtags' => $entry->hashtags,
            'status' => $entry->status,
            'platform_post_id' => $entry->platform_post_id,
            'published_permalink' => $entry->published_permalink,
            'is_published' => ! empty($entry->platform_post_id),
            'visual_id' => $visual?->id,
            'visual_url' => ($visual && $visual->image_path) ? url('storage/' . $visual->image_path) : null,
            'visual_theme' => $visual?->theme,
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
            'created_at' => $visual->created_at->toIso8601String(),
        ];
    }
}