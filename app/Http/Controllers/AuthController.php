<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            return redirect()->route($user->role === 'admin' ? 'admin.dashboard' : 'user.dashboard');
        }
        return view('login-form');
    }

    public function showRegister()
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            return redirect()->route($user->role === 'admin' ? 'admin.dashboard' : 'user.dashboard');
        }
        return view('register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'terms' => 'required|accepted',
        ], [
            'first_name.required' => 'Le prénom est requis.',
            'last_name.required' => 'Le nom est requis.',
            'email.required' => "L'adresse e-mail est requise.",
            'email.email' => "Format d'e-mail invalide.",
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'terms.required' => "Vous devez accepter les conditions d'utilisation.",
            'terms.accepted' => "Vous devez accepter les conditions d'utilisation.",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userCount = User::count();
        $role = $userCount === 0 ? 'admin' : 'user';

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'company' => $request->company,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        Auth::guard('web')->login($user);

        $redirect = $role === 'admin' ? route('admin.dashboard') : route('user.dashboard');

        return response()->json([
            'success' => true,
            'message' => 'Compte créé avec succès. Bienvenue sur IA Agent Studio !',
            'redirect' => $redirect
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => "L'adresse e-mail est requise.",
            'email.email' => "Format d'e-mail invalide.",
            'password.required' => 'Le mot de passe est requis.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::guard('web')->user();
            $redirect = $user->role === 'admin' ? route('admin.dashboard') : route('user.dashboard');

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie. Redirection vers votre tableau de bord...',
                'redirect' => $redirect
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Identifiants invalides.'
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
