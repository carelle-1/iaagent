<?php

namespace App\Http\Controllers;

use App\Models\UserAgent;
use App\Models\UserAgentCalendarEntry;
use App\Models\UserAgentPublicationVisual;
use App\Services\ComfyUIClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserAgentPublicationController extends Controller
{
    public function __construct(private ComfyUIClient $comfyui)
    {
    }

    public function index(): JsonResponse
    {
        $userAgentIds = UserAgent::where('user_id', Auth::id())->pluck('id');

        $entries = UserAgentCalendarEntry::with(['userAgent.agent', 'visuals'])
            ->whereIn('user_agent_id', $userAgentIds)
            ->where('status', 'validated')
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
        $objective = mb_substr((string) ($identity?->objective ?? ''), 0, 180) ?: 'mission de communication';
        $charter = mb_substr((string) ($identity?->charter ?? ''), 0, 180) ?: 'style professionnel';
        $palette = implode(', ', array_filter(array_column($identity?->palette ?? [], 'hex')));
        $paletteText = $palette !== '' ? "Palette de marque: {$palette}." : '';
        $inspirations = $agent->inspiringAccounts()->take(3)->get()
            ->map(fn ($account) => '@' . $account->handle)
            ->implode(', ');
        $inspirationText = $inspirations !== '' ? "Références inspirantes: {$inspirations}." : '';
        $platform = strtoupper((string) $entry->platform);
        $title = $entry->title ?: 'publication';
        $pillar = $entry->pillar ?: 'thème éditorial';
        $visual = $entry->visual_prompt ?: 'visuel professionnel';
        $domain = $agent->domain ?: 'secteur professionnel';
        $tone = $agent->tone ?: 'ton professionnel';
        $description = mb_substr((string) ($agent->agent?->description ?? ''), 0, 180);
        $descriptionText = $description !== '' ? "Description de l'agent: {$description}." : '';
        $month = $theme['label'];
        $keywords = $theme['keywords'];

        return "Visuel de publication réseaux sociaux pour {$agentName}, {$platform}. "
            . "Secteur: {$domain}. Ton: {$tone}. "
            . ($descriptionText !== '' ? $descriptionText . ' ' : '')
            . "Objectif de l'agent: {$objective}. "
            . "Thème éditorial: {$pillar}. Titre: {$title}. "
            . "Prompt visuel: {$visual}. "
            . "Ambiance mensuelle {$month} ({$theme['month']}): {$keywords}. "
            . trim($paletteText . ' ' . $inspirationText) . ' '
            . "Style: moderne, professionnel, haute qualité, composition claire, cohérent avec la charte: {$charter}. "
            . 'Sans texte, sans logo, sans watermark.';
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
            'visual_prompt' => $entry->visual_prompt,
            'hashtags' => $entry->hashtags,
            'status' => $entry->status,
            'visual_id' => $visual?->id,
            'visual_url' => ($visual && $visual->image_path) ? Storage::disk('public')->url($visual->image_path) : null,
            'visual_theme' => $visual?->theme,
        ];
    }

    private function serializeVisual(UserAgentPublicationVisual $visual): array
    {
        return [
            'id' => $visual->id,
            'url' => $visual->image_path ? Storage::disk('public')->url($visual->image_path) : null,
            'prompt' => $visual->prompt,
            'negative_prompt' => $visual->negative_prompt,
            'theme' => $visual->theme,
            'model' => $visual->model,
            'status' => $visual->status,
            'created_at' => $visual->created_at->toIso8601String(),
        ];
    }
}