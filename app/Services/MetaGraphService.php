<?php

namespace App\Services;

use App\Models\MetaAccount;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MetaGraphService
{
    private function base(): string
    {
        $version = config('services.meta.graph_version', 'v21.0');

        return "https://graph.facebook.com/{$version}";
    }

    private function resolveToken(MetaAccount $account): string
    {
        if (!empty($account->access_token)) {
            return (string) $account->access_token;
        }

        $appId = config('services.meta.app_id');
        $appSecret = config('services.meta.app_secret');

        if ($appId && $appSecret) {
            return $appId . '|' . $appSecret;
        }

        throw new RuntimeException('Aucun jeton Meta disponible pour ce compte.');
    }

    private function handle(Response $response): array
    {
        if (! $response->successful()) {
            $message = data_get($response->json(), 'error.message')
                ?: ('Erreur Meta (HTTP ' . $response->status() . ').');

            throw new RuntimeException($message);
        }

        return $response->json() ?: [];
    }

    public function publishFacebook(MetaAccount $account, string $message, ?string $imagePath = null, ?string $imageName = null): array
    {
        $token = $this->resolveToken($account);
        $pageId = (string) $account->meta_id;
        $base = $this->base();

        $absolute = $imagePath ? storage_path('app/public/' . $imagePath) : null;

        if ($absolute && is_file($absolute)) {
            $response = Http::acceptJson()
                ->attach('source', file_get_contents($absolute), $imageName ?: basename($absolute))
                ->timeout(60)
                ->post($base . '/' . rawurlencode($pageId) . '/photos', [
                    'caption' => $message,
                    'access_token' => $token,
                ]);
        } else {
            $response = Http::acceptJson()
                ->asForm()
                ->timeout(30)
                ->post($base . '/' . rawurlencode($pageId) . '/feed', [
                    'message' => $message,
                    'access_token' => $token,
                ]);
        }

        $json = $this->handle($response);
        $id = $json['id'] ?? null;

        if (! $id) {
            throw new RuntimeException('Réponse Meta sans identifiant de publication.');
        }

        return [
            'id' => $id,
            'permalink' => $json['permalink_url'] ?? $this->tryPermalink($base, $id, $token),
        ];
    }

    public function publishInstagram(MetaAccount $account, string $caption, string $imageUrl): array
    {
        $token = $this->resolveToken($account);
        $instagramId = (string) $account->meta_id;
        $base = $this->base();

        $host = strtolower((string) parse_url($imageUrl, PHP_URL_HOST));
        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            throw new RuntimeException("Instagram ne peut pas récupérer une image servie en local. Définissez APP_URL sur une URL publique accessible.");
        }

        $create = $this->handle(Http::acceptJson()->asForm()->timeout(30)->post($base . '/' . rawurlencode($instagramId) . '/media', [
            'image_url' => $imageUrl,
            'caption' => $caption,
            'access_token' => $token,
        ]));

        $creationId = $create['id'] ?? null;

        if (! $creationId) {
            throw new RuntimeException('Réponse Meta sans identifiant de conteneur Instagram.');
        }

        $published = $this->handle(Http::acceptJson()->asForm()->timeout(30)->post($base . '/' . rawurlencode($instagramId) . '/media_publish', [
            'creation_id' => $creationId,
            'access_token' => $token,
        ]));

        $id = $published['id'] ?? null;

        if (! $id) {
            throw new RuntimeException('Réponse Meta sans identifiant de média Instagram.');
        }

        return [
            'id' => $id,
            'permalink' => $published['permalink'] ?? $this->tryPermalink($base, $id, $token),
        ];
    }

    public function getInsights(MetaAccount $account, string $postId, string $platform): array
    {
        $token = $this->resolveToken($account);
        $base = $this->base();

        if (strtolower($platform) === 'instagram') {
            $json = $this->handle(Http::acceptJson()->timeout(20)->get($base . '/' . rawurlencode($postId), [
                'fields' => 'like_count,comments_count',
                'access_token' => $token,
            ]));

            return [
                'likes' => (int) ($json['like_count'] ?? 0),
                'comments' => (int) ($json['comments_count'] ?? 0),
                'shares' => null,
            ];
        }

        $json = $this->handle(Http::acceptJson()->timeout(20)->get($base . '/' . rawurlencode($postId), [
            'fields' => 'likes.summary(true),comments.summary(true),shares',
            'access_token' => $token,
        ]));

        return [
            'likes' => (int) data_get($json, 'likes.summary.total_count', 0),
            'comments' => (int) data_get($json, 'comments.summary.total_count', 0),
            'shares' => isset($json['shares']['count']) ? (int) $json['shares']['count'] : null,
        ];
    }

    public function getComments(MetaAccount $account, string $postId, string $platform): array
    {
        $token = $this->resolveToken($account);
        $base = $this->base();

        if (strtolower($platform) === 'instagram') {
            $json = $this->handle(Http::acceptJson()->timeout(20)->get($base . '/' . rawurlencode($postId) . '/comments', [
                'fields' => 'id,text,username,created_time',
                'access_token' => $token,
            ]));

            return array_map(fn (array $comment) => [
                'id' => $comment['id'] ?? null,
                'author' => $comment['username'] ?? 'Utilisateur',
                'message' => $comment['text'] ?? '',
                'created_at' => $comment['created_time'] ?? null,
                'likes' => 0,
            ], $json['data'] ?? []);
        }

        $json = $this->handle(Http::acceptJson()->timeout(20)->get($base . '/' . rawurlencode($postId) . '/comments', [
            'fields' => 'id,message,from,created_time,like_count',
            'access_token' => $token,
        ]));

        return array_map(fn (array $comment) => [
            'id' => $comment['id'] ?? null,
            'author' => data_get($comment, 'from.name', 'Utilisateur'),
            'message' => $comment['message'] ?? '',
            'created_at' => $comment['created_time'] ?? null,
            'likes' => (int) ($comment['like_count'] ?? 0),
        ], $json['data'] ?? []);
    }

    public function replyToComment(MetaAccount $account, string $commentId, string $message, string $platform): array
    {
        $token = $this->resolveToken($account);
        $base = $this->base();
        $endpoint = strtolower($platform) === 'instagram' ? 'replies' : 'comments';

        $json = $this->handle(Http::acceptJson()->asForm()->timeout(20)->post($base . '/' . rawurlencode($commentId) . '/' . $endpoint, [
            'message' => $message,
            'access_token' => $token,
        ]));

        return ['id' => $json['id'] ?? null];
    }

    private function tryPermalink(string $base, string $postId, string $token): ?string
    {
        try {
            $response = Http::acceptJson()->timeout(10)->get($base . '/' . rawurlencode($postId), [
                'fields' => 'permalink_url',
                'access_token' => $token,
            ]);

            return $response->successful() ? ($response->json('permalink_url') ?: null) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}