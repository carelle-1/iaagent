<?php

namespace App\Http\Controllers;

use App\Models\MetaAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class MetaAccountController extends Controller
{
    public function index()
    {
        $accounts = Auth::user()->metaAccounts()
            ->latest()
            ->get(['id', 'name', 'platform', 'meta_id', 'picture_url', 'status', 'category', 'notes', 'created_at', 'updated_at']);

        return response()->json(['success' => true, 'accounts' => $accounts]);
    }

    public function show(MetaAccount $metaAccount)
    {
        $this->authorizeOwner($metaAccount);
        return response()->json([
            'success' => true,
            'account' => $metaAccount->only(['id', 'name', 'platform', 'meta_id', 'picture_url', 'status', 'category', 'notes', 'created_at', 'updated_at'])
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'meta_id' => 'required|string|max:255',
            'platform' => 'nullable|string|in:facebook,instagram',
            'status' => 'nullable|string|in:connected,disconnected,pending',
            'category' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'access_token' => 'nullable|string',
        ]);

        $metaId = trim($data['meta_id']);
        $profile = $this->fetchMetaProfile($metaId, $data['access_token'] ?? null);

        $name = $profile['name'] ?? null;
        if (!$name) {
            $detail = $profile['error'] ? (' Détail Meta : ' . $profile['error']) : '';
            return response()->json([
                'success' => false,
                'message' => "Impossible de récupérer le nom/photo depuis Meta." . $detail . " Vérifiez l'ID de la page, que l'app Meta est en mode En ligne/Live si nécessaire, et les permissions. Vous pouvez aussi fournir un jeton d'accès de page."
            ], 422);
        }

        $account = Auth::user()->metaAccounts()->create([
            'name' => $name,
            'platform' => $data['platform'] ?? 'facebook',
            'meta_id' => $profile['id'] ?? $metaId,
            'picture_url' => $profile['picture_url'] ?? null,
            'access_token' => $data['access_token'] ?? null,
            'status' => $data['status'] ?? 'connected',
            'category' => $data['category'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Compte Meta ajouté.',
            'account' => $account->only(['id', 'name', 'platform', 'meta_id', 'picture_url', 'status', 'category', 'notes', 'created_at', 'updated_at'])
        ], 201);
    }

    public function update(Request $request, MetaAccount $metaAccount)
    {
        $this->authorizeOwner($metaAccount);
        $data = $request->validate([
            'meta_id' => 'required|string|max:255',
            'platform' => 'nullable|string|in:facebook,instagram',
            'status' => 'nullable|string|in:connected,disconnected,pending',
            'category' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'access_token' => 'nullable|string',
        ]);

        $metaId = trim($data['meta_id']);
        $updates = [
            'platform' => $data['platform'] ?? $metaAccount->platform,
            'meta_id' => $metaId,
            'status' => $data['status'] ?? $metaAccount->status,
            'category' => $data['category'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
        if (array_key_exists('access_token', $data) && $data['access_token']) {
            $updates['access_token'] = $data['access_token'];
        }

        $tokenForFetch = $updates['access_token'] ?? $metaAccount->access_token;
        $reconnecting = $metaAccount->status === 'disconnected' && ($updates['status'] ?? null) === 'connected';
        if ($metaId !== $metaAccount->meta_id || empty($metaAccount->name) || empty($metaAccount->picture_url) || !empty($data['access_token']) || $reconnecting) {
            $profile = $this->fetchMetaProfile($metaId, $tokenForFetch);
            if (!empty($profile['name'])) $updates['name'] = $profile['name'];
            if (!empty($profile['picture_url'])) $updates['picture_url'] = $profile['picture_url'];
            if (!empty($profile['id'])) $updates['meta_id'] = $profile['id'];
        }

        $metaAccount->update($updates);

        return response()->json([
            'success' => true,
            'message' => 'Compte Meta mis à jour.',
            'account' => $metaAccount->fresh()->only(['id', 'name', 'platform', 'meta_id', 'picture_url', 'status', 'category', 'notes', 'created_at', 'updated_at'])
        ]);
    }

    public function destroy(MetaAccount $metaAccount)
    {
        $this->authorizeOwner($metaAccount);
        $metaAccount->delete();
        return response()->json(['success' => true, 'message' => 'Compte Meta supprimé.']);
    }

    public function disconnect(MetaAccount $metaAccount)
    {
        $this->authorizeOwner($metaAccount);
        $metaAccount->update(['status' => 'disconnected', 'access_token' => null]);
        return response()->json([
            'success' => true,
            'message' => 'Compte Meta déconnecté.',
            'account' => $metaAccount->only(['id', 'name', 'platform', 'meta_id', 'picture_url', 'status', 'category', 'notes', 'created_at', 'updated_at'])
        ]);
    }

    public function reconnect(MetaAccount $metaAccount)
    {
        $this->authorizeOwner($metaAccount);
        $updates = ['status' => 'connected'];
        $profile = $this->fetchMetaProfile($metaAccount->meta_id, null);
        if (!empty($profile['name'])) $updates['name'] = $profile['name'];
        if (!empty($profile['picture_url'])) $updates['picture_url'] = $profile['picture_url'];
        if (!empty($profile['id'])) $updates['meta_id'] = $profile['id'];

        $metaAccount->update($updates);

        $message = !empty($profile['name'])
            ? 'Compte Meta reconnecté.'
            : 'Compte Meta reconnecté. Si le nom/photo ne se met pas à jour, ouvrez Modifier et renseignez un jeton d’accès de page.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'account' => $metaAccount->fresh()->only(['id', 'name', 'platform', 'meta_id', 'picture_url', 'status', 'category', 'notes', 'created_at', 'updated_at'])
        ]);
    }

    private function fetchMetaProfile(string $metaId, ?string $accessToken = null): array
    {
        $appId = config('services.meta.app_id');
        $appSecret = config('services.meta.app_secret');
        $version = config('services.meta.graph_version', 'v21.0');
        $base = "https://graph.facebook.com/{$version}";

        $tokens = [];
        if ($accessToken) $tokens[] = $accessToken;
        if ($appId && $appSecret) {
            $tokens[] = $appId . '|' . $appSecret;
            $appToken = $this->fetchAppAccessToken($base, $appId, $appSecret);
            if ($appToken) $tokens[] = $appToken;
        }
        $tokens = array_values(array_unique(array_filter($tokens)));
        if (empty($tokens)) {
            return ['error' => "Aucun jeton disponible (META_APP_ID/META_APP_SECRET manquants)."];
        }

        $lastError = null;
        foreach ($tokens as $token) {
            try {
                $response = Http::acceptJson()->timeout(12)->get($base . '/' . rawurlencode($metaId), [
                    'fields' => 'id,name',
                    'access_token' => $token,
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $name = $json['name'] ?? null;
                    $id = $json['id'] ?? $metaId;
                    if ($name) {
                        $picture = $this->fetchMetaPictureUrl($base, $id, $token);
                        if (!$picture) {
                            $picture = "https://graph.facebook.com/{$version}/" . rawurlencode($id) . "/picture?type=large";
                        }
                        return [
                            'id' => $id,
                            'name' => $name,
                            'picture_url' => $picture,
                        ];
                    }
                    $lastError = "Réponse Meta sans nom (ID inaccessible ou non public).";
                } else {
                    $lastError = $response->json('error.message') ?: ('HTTP ' . $response->status());
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
            }
        }

        return ['error' => $lastError ?: 'Erreur inconnue lors de l\'appel à Meta.'];
    }

    private function fetchAppAccessToken(string $base, string $appId, string $appSecret): ?string
    {
        try {
            $res = Http::acceptJson()->timeout(10)->get($base . '/oauth/access_token', [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'grant_type' => 'client_credentials',
            ]);
            if ($res->successful()) {
                return $res->json('access_token');
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return null;
    }

    private function fetchMetaPictureUrl(string $base, string $metaId, string $token): ?string
    {
        try {
            $res = Http::acceptJson()->timeout(10)->get($base . '/' . rawurlencode($metaId) . '/picture', [
                'redirect' => 'false',
                'type' => 'large',
                'access_token' => $token,
            ]);
            if ($res->successful()) {
                return data_get($res->json(), 'data.url');
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return null;
    }

    private function authorizeOwner(MetaAccount $metaAccount): void
    {
        abort_unless($metaAccount->user_id === Auth::id(), 403);
    }
}
