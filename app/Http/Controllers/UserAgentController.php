<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\UserAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class UserAgentController extends Controller
{
    public function index()
    {
        $userAgents = Auth::user()->userAgents()
            ->with('agent')
            ->latest()
            ->get();

        return response()->json(['success' => true, 'userAgents' => $userAgents]);
    }

    public function adminAgents()
    {
        $agents = Agent::active()->orderBy('name')->get(['id', 'name', 'description']);
        return response()->json(['success' => true, 'agents' => $agents]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'custom_name' => 'nullable|string|max:255',
            'domain' => 'nullable|string|max:255',
            'tone' => 'nullable|string|max:100',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')->store('user-agents', 'public');
        }

        $userAgent = Auth::user()->userAgents()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Agent créé avec succès.',
            'userAgent' => $userAgent->load('agent')
        ], 201);
    }

    public function update(Request $request, UserAgent $userAgent)
    {
        if ($userAgent->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'agent_id' => 'sometimes|required|exists:agents,id',
            'custom_name' => 'nullable|string|max:255',
            'domain' => 'nullable|string|max:255',
            'tone' => 'nullable|string|max:100',
            'profile_photo' => 'nullable|image|max:2048',
            'remove_photo' => 'nullable|boolean',
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($userAgent->profile_photo) {
                Storage::disk('public')->delete($userAgent->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('user-agents', 'public');
        } elseif ($request->boolean('remove_photo')) {
            if ($userAgent->profile_photo) {
                Storage::disk('public')->delete($userAgent->profile_photo);
            }
            $data['profile_photo'] = null;
        }

        $userAgent->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Agent mis à jour avec succès.',
            'userAgent' => $userAgent->load('agent')
        ]);
    }

    public function destroy(UserAgent $userAgent)
    {
        if ($userAgent->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisé.'], 403);
        }

        if ($userAgent->profile_photo) {
            Storage::disk('public')->delete($userAgent->profile_photo);
        }

        if ($userAgent->identity && $userAgent->identity->logo_path) {
            Storage::disk('public')->delete($userAgent->identity->logo_path);
        }

        $userAgent->delete();

        return response()->json(['success' => true, 'message' => 'Agent supprimé.']);
    }

    public function servePhoto($filename)
    {
        $path = "user-agents/{$filename}";

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $file = Storage::disk('public')->get($path);
        $mime = Storage::disk('public')->mimeType($path);

        return response($file, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}