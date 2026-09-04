<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ------------------------------------------------------------
    // INSCRIPTION — POST /api/register
    // ------------------------------------------------------------
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // ------------------------------------------------------------
    // CONNEXION — POST /api/login
    // ------------------------------------------------------------
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Identifiants incorrects',
            ], 401);
        }

        if ($user->statut === 'bloque') {
            return response()->json([
                'message' => 'Votre compte a été bloqué. Contactez l\'administrateur.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token,
        ]);
    }

    // ------------------------------------------------------------
    // DÉCONNEXION — POST /api/logout
    // ------------------------------------------------------------
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }

    // ------------------------------------------------------------
    // UTILISATEUR CONNECTÉ — GET /api/user
    // ------------------------------------------------------------
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    // ------------------------------------------------------------
    // MODIFIER SES INFORMATIONS — PUT /api/user
    // Appelée depuis l'écran Profil (Flutter) : modifie le nom et
    // l'email de l'utilisateur connecté. Le mot de passe n'est PAS
    // géré ici (garder ça séparé, avec confirmation de l'ancien
    // mot de passe, pour la sécurité).
    // ------------------------------------------------------------
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user->fresh(),
        ]);
    }
}