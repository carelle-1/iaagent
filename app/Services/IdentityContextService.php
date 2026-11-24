<?php

namespace App\Services;

use App\Models\UserAgent;
use App\Models\UserAgentIdentity;
use App\Models\UserAgentInspiringAccount;
use App\Models\UserAgentWebsite;
use App\Models\UserAgentContact;
use App\Models\UserAgentIdentityImage;
use Illuminate\Support\Collection;

class IdentityContextService
{
    public function buildContext(UserAgent $userAgent): array
    {
        $identity = $userAgent->identity;
        $accounts = $userAgent->inspiringAccounts()->get();
        $websites = $userAgent->websites()->get();
        $contacts = $userAgent->contacts()->where('is_public', true)->get();
        $images = $userAgent->identityImages()->get();

        return [
            'agent' => [
                'id' => $userAgent->id,
                'name' => $userAgent->custom_name ?: ($userAgent->agent->name ?? 'Agent IA'),
                'model_name' => $userAgent->agent->name ?? null,
                'category' => $userAgent->agent->category->name ?? null,
                'description' => $userAgent->agent->description ?? null,
                'domain' => $userAgent->domain ?? null,
                'tone' => $userAgent->tone ?? null,
            ],
            'identity' => $identity ? [
                'objective' => $identity->objective,
                'secteur_activite' => $identity->secteur_activite,
                'localisation' => $identity->localisation,
                'cible' => $identity->cible,
                'charter' => $identity->charter,
                'palette' => $identity->palette ?? [],
                'logo_path' => $identity->logo_path,
                'logo_url' => $identity->logo_path ? '/user/agents/logo/' . basename($identity->logo_path) : null,
            ] : null,
            'inspiring_accounts' => $accounts->map(fn (UserAgentInspiringAccount $a) => [
                'platform' => $a->platform,
                'handle' => $a->handle,
                'url' => $a->url,
                'notes' => $a->notes,
                'style' => $a->style,
            ])->values()->all(),
            'websites' => $websites->map(fn (UserAgentWebsite $w) => [
                'label' => $w->label,
                'url' => $w->url,
                'notes' => $w->notes,
            ])->values()->all(),
            'public_contacts' => $contacts->map(fn (UserAgentContact $c) => [
                'name' => $c->name,
                'phone' => $c->phone,
                'email' => $c->email,
                'notes' => $c->notes,
            ])->values()->all(),
            'reference_images' => $images->map(fn (UserAgentIdentityImage $img) => [
                'url' => $img->path ? '/user/agents/identity-image/' . basename($img->path) : null,
                'caption' => $img->caption,
            ])->values()->all(),
        ];
    }

    public function buildPromptContext(UserAgent $userAgent): string
    {
        $context = $this->buildContext($userAgent);

        $parts = [];

        $parts[] = '=== CONTEXTE AGENT ===' . PHP_EOL
            . 'Nom: ' . ($context['agent']['name'] ?? 'Non défini') . PHP_EOL
            . 'Modèle: ' . ($context['agent']['model_name'] ?? 'Non défini') . PHP_EOL
            . 'Catégorie: ' . ($context['agent']['category'] ?? 'Non définie') . PHP_EOL
            . 'Domaine: ' . ($context['agent']['domain'] ?? 'Non défini') . PHP_EOL
            . 'Tonalité: ' . ($context['agent']['tone'] ?? 'Non définie');

        if ($context['identity']) {
            $id = $context['identity'];
            $palette = collect($id['palette'] ?? [])
                ->map(fn ($c) => is_array($c) ? ($c['name'] ?? $c['hex'] ?? '') : $c)
                ->filter()
                ->implode(', ');

            $parts[] = '=== IDENTITÉ DE LA MARQUE ===' . PHP_EOL
                . 'Objectif: ' . ($id['objective'] ?? 'Non renseigné') . PHP_EOL
                . 'Secteur d\'activité: ' . ($id['secteur_activite'] ?? 'Non renseigné') . PHP_EOL
                . 'Localisation: ' . ($id['localisation'] ?? 'Non renseignée') . PHP_EOL
                . 'Cible: ' . ($id['cible'] ?? 'Non renseignée') . PHP_EOL
                . 'Charte éditoriale: ' . ($id['charter'] ?? 'Aucune') . PHP_EOL
                . 'Palette: ' . ($palette ?: 'Non définie') . PHP_EOL
                . 'Logo: ' . ($id['logo_url'] ? 'Disponible' : 'Non disponible');
        }

        if (!empty($context['inspiring_accounts'])) {
            $inspiring = collect($context['inspiring_accounts'])
                ->map(fn ($a) => $a['platform'] . ' ' . $a['handle'] . ($a['style'] ? ' — ' . $a['style'] : '') . ($a['notes'] ? ' — ' . $a['notes'] : ''))
                ->implode(' ; ');
            $parts[] = '=== COMPTES INSPIRANTS ===' . PHP_EOL . $inspiring;
        }

        if (!empty($context['websites'])) {
            $websites = collect($context['websites'])
                ->map(fn ($w) => $w['label'] . ' — ' . $w['url'] . ($w['notes'] ? ' (' . $w['notes'] . ')' : ''))
                ->implode(' ; ');
            $parts[] = '=== SITES WEB ===' . PHP_EOL . $websites;
        }

        if (!empty($context['public_contacts'])) {
            $contacts = collect($context['public_contacts'])
                ->map(fn ($c) => $c['name'] . ' — ' . $c['phone'] . ($c['email'] ? ' — ' . $c['email'] : '') . ($c['notes'] ? ' (' . $c['notes'] . ')' : ''))
                ->implode(' ; ');
            $parts[] = '=== CONTACTS PUBLICS ===' . PHP_EOL . $contacts;
        }

        if (!empty($context['reference_images'])) {
            $images = collect($context['reference_images'])
                ->map(fn ($img) => $img['caption'] ?? 'Image de référence')
                ->implode(' ; ');
            $parts[] = '=== IMAGES DE RÉFÉRENCE ===' . PHP_EOL . $images;
        }

        return implode(PHP_EOL . PHP_EOL, $parts);
    }

    public function buildOllamaContext(UserAgent $userAgent): array
    {
        $context = $this->buildContext($userAgent);

        return [
            'agent_name' => $context['agent']['name'] ?? '',
            'objective' => $context['identity']['objective'] ?? '',
            'secteur_activite' => $context['identity']['secteur_activite'] ?? '',
            'localisation' => $context['identity']['localisation'] ?? '',
            'cible' => $context['identity']['cible'] ?? '',
            'charter' => $context['identity']['charter'] ?? '',
            'palette' => collect($context['identity']['palette'] ?? [])
                ->map(fn ($c) => is_array($c) ? ($c['hex'] ?? '') : $c)
                ->filter()
                ->values()
                ->all(),
            'logo_url' => $context['identity']['logo_url'] ?? null,
            'inspiring_accounts' => $context['inspiring_accounts'],
            'websites' => $context['websites'],
            'public_contacts' => $context['public_contacts'],
            'reference_images' => $context['reference_images'],
        ];
    }

    public function buildComfyUIContext(UserAgent $userAgent): array
    {
        $context = $this->buildContext($userAgent);

        $palette = collect($context['identity']['palette'] ?? [])
            ->map(fn ($c) => is_array($c) ? ($c['hex'] ?? '') : $c)
            ->filter()
            ->values()
            ->all();

        return [
            'brand_name' => $context['agent']['name'] ?? '',
            'domain' => $context['agent']['domain'] ?? '',
            'secteur_activite' => $context['identity']['secteur_activite'] ?? '',
            'charter' => $context['identity']['charter'] ?? '',
            'palette' => $palette,
            'primary_color' => $palette[0] ?? '#2BC7E8',
            'secondary_color' => $palette[1] ?? '#11CE03',
            'logo_url' => $context['identity']['logo_url'] ?? null,
            'reference_images' => $context['reference_images'],
        ];
    }
}