<?php

namespace App\Http\Controllers;

use App\Models\UserAgent;
use App\Models\UserAgentCalendarEntry;
use App\Services\OllamaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserAgentCalendarController extends Controller
{
    private const PLATFORMS = ['facebook', 'instagram', 'linkedin', 'tiktok', 'youtube', 'x', 'other'];
    private const STATUSES = ['pending', 'validated', 'published', 'draft'];

    public function __construct(private OllamaService $ollama)
    {
    }

    public function models()
    {
        return response()->json([
            'success' => true,
            'models' => $this->ollama->models(),
            'default_model' => config('services.ollama.model'),
        ]);
    }

    public function index(UserAgent $userAgent)
    {
        $this->authorizeAgent($userAgent);

        $entries = $userAgent->calendarEntries()->get();

        return response()->json([
            'success' => true,
            'entries' => $this->serializeEntries($entries),
            'models' => $this->ollama->models(),
            'default_model' => config('services.ollama.model'),
        ]);
    }

    public function generate(Request $request, UserAgent $userAgent)
    {
        @set_time_limit(0);
        $this->authorizeAgent($userAgent);

        $validated = $request->validate([
            'model' => 'nullable|string|max:100',
            'start_date' => 'nullable|date',
            'weeks' => 'nullable|integer|min:1|max:4',
            'posts_per_week' => 'nullable|integer|min:1|max:10',
        ]);

        $availableModels = $this->ollama->models();

        if (empty($availableModels)) {
            return response()->json([
                'success' => false,
                'message' => 'Ollama est injoignable ou aucun modèle n\'est installé. Démarrez Ollama et installez un modèle (ex: ollama pull qwen3:4b).',
            ], 422);
        }

        $model = $validated['model'] ?? config('services.ollama.model') ?? $availableModels[0];

        if (!in_array($model, $availableModels, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Modèle Ollama introuvable : ' . $model . '. Modèles disponibles : ' . implode(', ', $availableModels),
            ], 422);
        }

        $weeks = (int) ($validated['weeks'] ?? 1);
        $postsPerWeek = (int) ($validated['posts_per_week'] ?? 5);
        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : $this->nextMonday();

        $identity = $userAgent->identity;
        $accounts = $userAgent->inspiringAccounts()->get();

        $prompt = $this->buildPrompt($userAgent, $identity, $accounts, $startDate, $weeks, $postsPerWeek);

        $numPredict = min(2200, 350 + ($weeks * $postsPerWeek) * 200);
        $result = $this->ollama->generate($model, $prompt, null, $numPredict);

        if (!($result['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Erreur lors de la génération Ollama.',
            ], 502);
        }

        $posts = $this->parsePosts((string) ($result['raw'] ?? ''));

        if (empty($posts)) {
            \Log::debug('Ollama calendar parse failed', [
                'model' => $model,
                'raw' => $result['raw'] ?? '',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ollama n\'a pas retourné de calendrier exploitable. Réessayez ou choisissez un autre modèle.',
            ], 422);
        }

        $userAgent->calendarEntries()->delete();

        $maxPosts = $weeks * $postsPerWeek;
        $endDate = $startDate->copy()->addDays($weeks * 7 - 1);

        $rows = [];
        foreach ($posts as $index => $post) {
            if (!is_array($post) || count($rows) >= $maxPosts) {
                continue;
            }

            $pillar = ltrim(trim($this->text($post['pillar'] ?? 'Général')), "# ");

            if ($pillar === '') {
                $pillar = 'Général';
            }

            if (mb_strlen($pillar) > 60) {
                $pillar = 'Général';
            }

            $status = $this->text($post['status'] ?? 'pending');

            if (!in_array($status, self::STATUSES, true)) {
                $status = 'pending';
            }

            $agentName = $userAgent->custom_name ?: ($userAgent->agent->name ?? 'Agent IA');
            $fallbackCaption = $identity->objective ?: 'Découvrez notre approche et nos solutions.';
            $fallbackVisual = $identity->charter ?: 'Visuel professionnel cohérent avec la charte de la marque.';

            $title = $this->cleanContent($this->text($post['title'] ?? ''), $agentName);
            $caption = $this->cleanContent($this->text($post['caption'] ?? ''), $agentName);
            $visualPrompt = $this->cleanContent($this->text($post['visual_prompt'] ?? ''), $agentName);
            $hashtags = $this->cleanContent($this->text($post['hashtags'] ?? ''), $agentName);

            if ($title === '') {
                $title = $pillar !== 'Général' ? $pillar : 'Publication';
            }

            if ($caption === '') {
                $caption = $fallbackCaption;
            }

            if ($visualPrompt === '') {
                $visualPrompt = $fallbackVisual;
            }

            if ($hashtags === '') {
                $hashtags = $this->defaultHashtags($pillar, $userAgent->domain);
            }

            $rows[] = [
                'user_agent_id' => $userAgent->id,
                'published_at' => $this->normalizeDate(
                    $post['date'] ?? null,
                    $post['time'] ?? null,
                    $startDate,
                    $endDate,
                    count($rows),
                    $postsPerWeek
                ),
                'platform' => $this->normalizePlatform($post['platform'] ?? null),
                'pillar' => mb_substr($pillar, 0, 100),
                'title' => mb_substr($title, 0, 255),
                'caption' => mb_substr($caption, 0, 1000),
                'visual_prompt' => mb_substr($visualPrompt, 0, 500),
                'hashtags' => mb_substr($hashtags, 0, 300),
                'status' => $status,
                'model' => $model,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($rows)) {
            return response()->json([
                'success' => false,
                'message' => 'Le calendrier retourné par Ollama est vide. Réessayez.',
            ], 422);
        }

        UserAgentCalendarEntry::insert($rows);

        return response()->json([
            'success' => true,
            'message' => count($rows) . ' publication(s) générée(s) avec ' . $model . '.',
            'entries' => $this->serializeEntries($userAgent->calendarEntries()->get()),
        ]);
    }

    public function destroy(UserAgent $userAgent)
    {
        $this->authorizeAgent($userAgent);
        $userAgent->calendarEntries()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Calendrier vidé.',
        ]);
    }

    public function destroyEntry(UserAgent $userAgent, UserAgentCalendarEntry $entry)
    {
        $this->authorizeAgent($userAgent);
        $this->authorizeEntry($userAgent, $entry);

        $entry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Publication supprimée.',
        ]);
    }

    public function update(Request $request, UserAgent $userAgent, UserAgentCalendarEntry $entry)
    {
        $this->authorizeAgent($userAgent);
        $this->authorizeEntry($userAgent, $entry);

        $validated = $request->validate([
            'published_at' => 'required|date',
            'platform' => 'required|string|in:' . implode(',', self::PLATFORMS),
            'pillar' => 'nullable|string|max:100',
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:1000',
            'visual_prompt' => 'nullable|string|max:500',
            'hashtags' => 'nullable|string|max:300',
            'status' => 'nullable|string|in:' . implode(',', self::STATUSES),
        ]);

        $entry->update([
            'published_at' => Carbon::parse($validated['published_at']),
            'platform' => $validated['platform'],
            'pillar' => trim($validated['pillar'] ?? '') ?: 'Général',
            'title' => trim($validated['title'] ?? '') ?: 'Publication',
            'caption' => trim($validated['caption'] ?? ''),
            'visual_prompt' => trim($validated['visual_prompt'] ?? ''),
            'hashtags' => trim($validated['hashtags'] ?? ''),
            'status' => $validated['status'] ?? $entry->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Publication mise à jour.',
            'entry' => $this->serializeEntry($entry->fresh()),
        ]);
    }

    public function updateStatus(Request $request, UserAgent $userAgent, UserAgentCalendarEntry $entry)
    {
        $this->authorizeAgent($userAgent);
        $this->authorizeEntry($userAgent, $entry);

        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', self::STATUSES),
        ]);

        $entry->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour.',
            'entry' => $this->serializeEntry($entry->fresh()),
        ]);
    }

    private function buildPrompt(
        UserAgent $userAgent,
        $identity,
        $accounts,
        Carbon $startDate,
        int $weeks,
        int $postsPerWeek
    ): string {
        $agentModelName = $userAgent->agent->name ?? 'Non défini';
        $agentName = $userAgent->custom_name ?: $agentModelName;
        $domain = $userAgent->domain ?: 'Non défini';
        $tone = $userAgent->tone ?: 'Non défini';
        $objective = $identity->objective ?? 'Non défini';
        $charter = $identity->charter ?? 'Non défini';

        $palette = collect($identity->palette ?? [])
            ->map(function ($color) {
                $name = trim((string) ($color['name'] ?? ''));
                $hex = trim((string) ($color['hex'] ?? ''));

                return trim($name . ' ' . $hex);
            })
            ->filter()
            ->implode(', ');

        $palette = $palette !== '' ? $palette : 'Non définie';

        $inspiring = $accounts
            ->map(function ($account) {
                return trim(($account->platform ?? '') . ' ' . ($account->handle ?? ''));
            })
            ->filter()
            ->implode(', ');

        $inspiring = $inspiring !== '' ? $inspiring : 'Aucun';

        return <<<PROMPT
Tu es un stratège éditorial expert en réseaux sociaux.
Génère un calendrier éditorial détaillé et prêt à publier pour l'agent IA suivant.

Agent: {$agentName}
Modèle d'agent: {$agentModelName}
Domaine d'activité: {$domain}
Tonalité: {$tone}
Objectif: {$objective}
Palette & charte: {$palette}
Charte éditoriale: {$charter}
Comptes inspirants: {$inspiring}

Consignes:
- Génère exactement {$weeks} semaine(s) de calendrier, soit {$postsPerWeek} publication(s) par semaine, à partir du {$startDate->toDateString()}.
- Ne génère pas une publication par plateforme : choisis librement les plateformes les plus pertinentes.
- Varie les piliers éditoriaux (pédagogie, preuve, offre, coulisses, inspiration, etc.).
- Chaque publication doit refléter l'objectif, la charte et la palette.
- Rédige des légendes concises (2 phrases maximum) prêtes à publier, avec 3 à 5 hashtags pertinents.
- Donne un prompt de visuel court (12 mots maximum) cohérent avec la charte et la palette.
- Statut initial: "pending".

Réponds UNIQUEMENT avec un objet JSON valide par ligne (JSON Lines), sans tableau, sans texte autour.
Chaque ligne doit respecter exactement ce format:
{"date":"YYYY-MM-DD","time":"HH:MM","platform":"facebook|instagram|linkedin|tiktok|youtube|x|other","pillar":"Nom du pilier","title":"Titre court","caption":"Légende concise","visual_prompt":"Prompt visuel court","hashtags":"#hashtag1 #hashtag2","status":"pending"}
PROMPT;
    }

    private function safeJsonDecode(string $json): array
    {
        $decoded = json_decode($json, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $repaired = preg_replace('/,\s*([}\]])/', '$1', $json);
        $decoded = json_decode($repaired, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return [];
    }

    private function regexExtractPost(string $json): array
    {
        $patterns = [
            'date' => '/"date"\s*:\s*"([^"]*)"/u',
            'time' => '/"time"\s*:\s*"?([0-9]{1,2}(?::[0-5][0-9])?)"?/u',
            'platform' => '/"platform"\s*:\s*"([^"]*)"/u',
            'pillar' => '/"pillar"\s*:\s*"([^"]*)"/u',
            'title' => '/"title"\s*:\s*"([^"]*)"/u',
            'caption' => '/"caption"\s*:\s*"([^"]*)"/u',
            'visual_prompt' => '/"visual_prompt"\s*:\s*"([^"]*)"/u',
            'hashtags' => '/"hashtags"\s*:\s*"([^"]*)"/u',
            'status' => '/"status"\s*:\s*"([^"]*)"/u',
        ];

        $post = [];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $json, $match)) {
                $post[$key] = $match[1];
            }
        }

        return $post;
    }

    private function parsePosts(string $raw): array
    {
        $raw = trim($raw);

        if ($raw === '') {
            return [];
        }

        if (preg_match('/```(?:json)?\s*(.*?)```/s', $raw, $match)) {
            $raw = trim($match[1]);
        }

        $decoded = $this->safeJsonDecode($raw);

        if (!empty($decoded)) {
            $posts = $this->flattenPosts($decoded);

            if (!empty($posts)) {
                return $posts;
            }
        }

        $start = strpos($raw, '[');
        $end = strrpos($raw, ']');

        if ($start !== false && $end !== false && $end > $start) {
            $decoded = $this->safeJsonDecode(substr($raw, $start, $end - $start + 1));

            if (!empty($decoded)) {
                $posts = $this->flattenPosts($decoded);

                if (!empty($posts)) {
                    return $posts;
                }
            }
        }

        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $decoded = $this->safeJsonDecode(substr($raw, $start, $end - $start + 1));

            if (!empty($decoded)) {
                $posts = $this->flattenPosts($decoded);

                if (!empty($posts)) {
                    return $posts;
                }
            }
        }

        $posts = [];

        foreach ($this->extractPostStrings($raw) as $post) {
            $posts = array_merge($posts, $this->flattenPosts($post));
        }

        if (empty($posts)) {
            foreach ($this->extractJsonObjects($raw) as $object) {
                $posts = array_merge($posts, $this->flattenPosts($object));
            }
        }

        if (empty($posts)) {
            foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
                $line = trim($line);

                if ($line === '' || !str_starts_with($line, '{')) {
                    continue;
                }

                $decoded = $this->safeJsonDecode($line);

                if (!empty($decoded)) {
                    $posts = array_merge($posts, $this->flattenPosts($decoded));
                }
            }
        }

        $unique = [];

        foreach ($posts as $post) {
            if (!is_array($post)) {
                continue;
            }

            $unique[md5(json_encode($post))] = $post;
        }

        return array_values($unique);
    }

    private function extractPostStrings(string $raw): array
    {
        $posts = [];

        if (!preg_match_all('/"((?:\\\\.|[^"\\\\])*)"/s', $raw, $matches)) {
            return [];
        }

        foreach ($matches[1] as $value) {
            $value = preg_replace('/\\\\(["\\\\])/', '$1', $value);

            if (!str_starts_with(trim($value), '{')) {
                continue;
            }

            $decoded = $this->safeJsonDecode($value);

            if (empty($decoded)) {
                $decoded = $this->regexExtractPost($value);
            }

            if (!empty($decoded)) {
                $posts[] = $decoded;
            }
        }

        return $posts;
    }

    private function flattenPosts($decoded): array
    {
        if (!is_array($decoded)) {
            return [];
        }

        foreach (['posts', 'calendar', 'entries'] as $key) {
            if (isset($decoded[$key]) && is_array($decoded[$key])) {
                return $this->flattenPosts($decoded[$key]);
            }
        }

        if (isset($decoded['date']) || isset($decoded['caption']) || isset($decoded['title']) || isset($decoded['platform'])) {
            return [$decoded];
        }

        $posts = [];

        foreach ($decoded as $value) {
            if (is_string($value)) {
                $inner = $this->safeJsonDecode($value);

                if (empty($inner)) {
                    $inner = $this->regexExtractPost($value);
                }

                if (!empty($inner)) {
                    $posts = array_merge($posts, $this->flattenPosts($inner));
                }
            } elseif (is_array($value)) {
                $posts = array_merge($posts, $this->flattenPosts($value));
            }
        }

        return $posts;
    }

    private function normalizeDate($date, $time, Carbon $startDate, Carbon $endDate, int $index, int $postsPerWeek): Carbon
    {
        $date = $this->text($date);
        $time = $this->text($time);
        $computedDate = $startDate->copy()->addDays((int) floor($index * 7 / max(1, $postsPerWeek)));

        if ($computedDate->greaterThan($endDate)) {
            $computedDate = $endDate->copy();
        }

        $parsed = null;

        if ($date) {
            try {
                $candidate = Carbon::parse((string) $date)->startOfDay();

                if ($candidate->greaterThanOrEqualTo($startDate) && $candidate->lessThanOrEqualTo($endDate)) {
                    $parsed = $candidate;
                }
            } catch (\Throwable $e) {
                $parsed = null;
            }
        }

        $parsed = $parsed ?: $computedDate;

        if (is_int($time) || (is_string($time) && ctype_digit(trim($time)))) {
            $hour = (int) $time;

            if ($hour >= 0 && $hour <= 23) {
                $time = sprintf('%02d:00', $hour);
            }
        }

        $time = is_string($time) && preg_match('/^([01]?\d|2[0-3]):[0-5]\d$/', trim($time))
            ? trim($time)
            : ['09:00', '12:30', '18:00'][$index % 3];

        [$hours, $minutes] = array_pad(explode(':', $time), 2, '0');

        return $parsed->setTime((int) $hours, (int) $minutes, 0);
    }

    private function normalizePlatform($platform): string
    {
        $raw = strtolower(trim($this->text($platform)));
        $parts = preg_split('/[|,;\s\/]+/', $raw);

        foreach ($parts as $part) {
            if (in_array($part, self::PLATFORMS, true)) {
                return $part;
            }
        }

        return 'facebook';
    }

    private function cleanContent(string $value, string $agentName): string
    {
        $value = str_replace(
            ['[Nom du marque]', '[Nom de la marque]', '[Marque]', '[marque]'],
            $agentName,
            $value
        );

        return trim($value);
    }

    private function text($value): string
    {
        if (is_array($value)) {
            $parts = [];

            foreach ($value as $item) {
                if (is_scalar($item)) {
                    $item = trim((string) $item);

                    if ($item !== '') {
                        $parts[] = $item;
                    }
                }
            }

            return implode(' ', $parts);
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return '';
    }

    private function defaultHashtags(string $pillar, ?string $domain): string
    {
        $tags = [];

        foreach ([$pillar, $domain] as $value) {
            $tag = preg_replace('/[^a-zA-Z0-9À-ÿ]/u', '', (string) $value);

            if ($tag !== '') {
                $tags[] = '#' . $tag;
            }
        }

        $tags[] = '#IA';

        return implode(' ', array_values(array_unique($tags)));
    }

    private function extractJsonObjects(string $raw): array
    {
        $objects = [];
        $length = strlen($raw);

        for ($i = 0; $i < $length; $i++) {
            if ($raw[$i] !== '{') {
                continue;
            }

            $depth = 0;
            $inString = false;
            $escape = false;

            for ($j = $i; $j < $length; $j++) {
                $char = $raw[$j];

                if ($inString) {
                    if ($escape) {
                        $escape = false;
                    } elseif ($char === '\\') {
                        $escape = true;
                    } elseif ($char === '"') {
                        $inString = false;
                    }

                    continue;
                }

                if ($char === '"') {
                    $inString = true;
                    continue;
                }

                if ($char === '{') {
                    $depth++;
                } elseif ($char === '}') {
                    $depth--;

                    if ($depth === 0) {
                        $candidate = substr($raw, $i, $j - $i + 1);
                        $decoded = $this->safeJsonDecode($candidate);

                        if (!empty($decoded)) {
                            $objects[] = $decoded;
                        }

                        break;
                    }
                }
            }
        }

        return $objects;
    }

    private function serializeEntries($entries): array
    {
        return $entries->map(function (UserAgentCalendarEntry $entry) {
            return $this->serializeEntry($entry);
        })->values()->all();
    }

    private function serializeEntry(UserAgentCalendarEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'published_at' => $entry->published_at->format('Y-m-d\TH:i:s'),
            'platform' => $entry->platform,
            'pillar' => $entry->pillar,
            'title' => $entry->title,
            'caption' => $entry->caption,
            'visual_prompt' => $entry->visual_prompt,
            'hashtags' => $entry->hashtags,
            'status' => $entry->status,
            'model' => $entry->model,
        ];
    }

    private function nextMonday(): Carbon
    {
        $today = Carbon::today();
        $days = (8 - $today->dayOfWeekIso) % 7;

        if ($days === 0) {
            $days = 7;
        }

        return $today->copy()->addDays($days);
    }

    private function authorizeAgent(UserAgent $userAgent): void
    {
        abort_unless($userAgent->user_id === Auth::id(), 403);
    }

    private function authorizeEntry(UserAgent $userAgent, UserAgentCalendarEntry $entry): void
    {
        abort_unless($entry->user_agent_id === $userAgent->id, 403);
    }
}