<?php

namespace App\Http\Controllers;

use App\Models\UserAgent;
use App\Models\UserAgentIdentity;
use App\Models\UserAgentIdentityImage;
use App\Models\UserAgentInspiringAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class UserAgentIdentityController extends Controller
{
    public function show(UserAgent $userAgent)
    {
        $this->authorizeAgent($userAgent);

        return response()->json([
            'success' => true,
            'identity' => $userAgent->identity ? $this->serializeIdentity($userAgent->identity) : null,
            'accounts' => $userAgent->inspiringAccounts()->get(),
            'images' => $this->serializeImages($userAgent->identityImages),
        ]);
    }

    public function store(Request $request, UserAgent $userAgent)
    {
        $this->authorizeAgent($userAgent);

        $validated = $request->validate([
            'objective' => 'nullable|string|max:5000',
            'palette' => 'nullable|string',
            'charter' => 'nullable|string|max:5000',
            'logo' => 'nullable|image|max:2048',
            'remove_logo' => 'nullable|boolean',
        ]);

        $identity = $userAgent->identity ?: new UserAgentIdentity(['user_agent_id' => $userAgent->id]);

        $identity->objective = ($validated['objective'] ?? '') !== '' ? $validated['objective'] : null;
        $identity->palette = $this->decodePalette($request->input('palette'));
        $identity->charter = ($validated['charter'] ?? '') !== '' ? $validated['charter'] : null;

        if ($request->hasFile('logo')) {
            if ($identity->logo_path) {
                Storage::disk('public')->delete($identity->logo_path);
            }
            $extension = $request->file('logo')->getClientOriginalExtension() ?: 'png';
            $identity->logo_path = $request->file('logo')->storeAs(
                'user-agent-logos',
                $userAgent->id . '-' . Str::uuid() . '.' . $extension,
                'public'
            );
        } elseif ($request->boolean('remove_logo')) {
            if ($identity->logo_path) {
                Storage::disk('public')->delete($identity->logo_path);
            }
            $identity->logo_path = null;
        }

        $identity->save();

        return response()->json([
            'success' => true,
            'message' => 'Identité enregistrée.',
            'identity' => $this->serializeIdentity($identity->fresh()),
        ]);
    }

    public function storeAccount(Request $request, UserAgent $userAgent)
    {
        $this->authorizeAgent($userAgent);

        $data = $this->validateAccount($request);

        $account = $userAgent->inspiringAccounts()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Compte inspirant ajouté.',
            'account' => $account,
        ], 201);
    }

    public function updateAccount(Request $request, UserAgent $userAgent, UserAgentInspiringAccount $account)
    {
        $this->authorizeAgent($userAgent);
        $this->authorizeAccount($userAgent, $account);

        $account->update($this->validateAccount($request));

        return response()->json([
            'success' => true,
            'message' => 'Compte inspirant mis à jour.',
            'account' => $account->fresh(),
        ]);
    }

    public function destroyAccount(UserAgent $userAgent, UserAgentInspiringAccount $account)
    {
        $this->authorizeAgent($userAgent);
        $this->authorizeAccount($userAgent, $account);

        $account->delete();

        return response()->json(['success' => true, 'message' => 'Compte inspirant supprimé.']);
    }

    public function serveLogo(string $filename): Response
    {
        $filename = basename($filename);
        $path = 'user-agent-logos/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response(Storage::disk('public')->get($path), 200, [
            'Content-Type' => Storage::disk('public')->mimeType($path),
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    public function uploadImages(Request $request, UserAgent $userAgent)
    {
        $this->authorizeAgent($userAgent);

        $validated = $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'image|max:5120',
        ]);

        $files = $request->file('images', []);
        $start = (int) ($userAgent->identityImages()->max('sort_order') ?? 0);

        foreach ($files as $index => $file) {
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $path = $file->storeAs(
                'user-agent-identity-images',
                $userAgent->id . '-' . Str::uuid() . '.' . $extension,
                'public'
            );

            $userAgent->identityImages()->create([
                'path' => $path,
                'sort_order' => $start + $index + 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => count($files) . ' image(s) ajoutée(s).',
            'images' => $this->serializeImages($userAgent->identityImages()->get()),
        ], 201);
    }

    public function destroyImage(UserAgent $userAgent, UserAgentIdentityImage $image)
    {
        $this->authorizeAgent($userAgent);
        abort_unless($image->user_agent_id === $userAgent->id, 403);

        if ($image->path) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image supprimée.',
            'images' => $this->serializeImages($userAgent->identityImages()->get()),
        ]);
    }

    public function serveImage(string $filename): Response
    {
        $filename = basename($filename);
        $path = 'user-agent-identity-images/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response(Storage::disk('public')->get($path), 200, [
            'Content-Type' => Storage::disk('public')->mimeType($path),
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    private function validateAccount(Request $request): array
    {
        return $request->validate([
            'platform' => 'required|string|in:instagram,facebook,tiktok,linkedin,youtube,x,other',
            'handle' => 'required|string|max:255',
            'url' => 'nullable|url|max:500',
            'notes' => 'nullable|string|max:2000',
        ]);
    }

    private function decodePalette($raw): array
    {
        if (empty($raw)) {
            return [];
        }

        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $colors = [];
        foreach ($decoded as $item) {
            if (is_array($item)) {
                $name = trim((string) ($item['name'] ?? ''));
                $hex = trim((string) ($item['hex'] ?? ''));
            } else {
                $name = '';
                $hex = trim((string) $item);
            }

            if ($hex === '' || !preg_match('/^#[0-9a-fA-F]{6}$/', $hex)) {
                continue;
            }

            $colors[] = [
                'name' => $name !== '' ? $name : $hex,
                'hex' => strtoupper($hex),
            ];
        }

        return $colors;
    }

    private function serializeIdentity(UserAgentIdentity $identity): array
    {
        return [
            'id' => $identity->id,
            'user_agent_id' => $identity->user_agent_id,
            'objective' => $identity->objective,
            'palette' => $identity->palette ?? [],
            'charter' => $identity->charter,
            'logo_path' => $identity->logo_path,
            'logo_url' => $identity->logo_path
                ? '/user/agents/logo/' . basename($identity->logo_path)
                : null,
        ];
    }

    private function serializeImages($images): array
    {
        return $images->map(function (UserAgentIdentityImage $image) {
            return [
                'id' => $image->id,
                'path' => $image->path,
                'caption' => $image->caption,
                'sort_order' => $image->sort_order,
                'url' => $image->path
                    ? '/user/agents/identity-image/' . basename($image->path)
                    : null,
            ];
        })->values()->all();
    }

    private function authorizeAgent(UserAgent $userAgent): void
    {
        abort_unless($userAgent->user_id === Auth::id(), 403);
    }

    private function authorizeAccount(UserAgent $userAgent, UserAgentInspiringAccount $account): void
    {
        abort_unless($account->user_agent_id === $userAgent->id, 403);
    }
}