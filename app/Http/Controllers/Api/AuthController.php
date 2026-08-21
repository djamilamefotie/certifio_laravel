<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ------------------------------------------------------------
    // INSCRIPTION — POST /api/register
    // Appelée depuis l'écran register_screen.dart (Flutter)
    // ------------------------------------------------------------
    public function register(Request $request)
    {
        // On vérifie que les champs envoyés par Flutter sont corrects.
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed', // "confirmed" attend un champ password_confirmation
        ]);

        // On crée l'utilisateur. On ne met PAS "categorie" ici volontairement :
        // le champ garde sa valeur par défaut de la base ("utilisateur"),
        // pour qu'un visiteur ne puisse jamais s'inscrire en tant
        // qu'administrateur ou institut depuis l'application.
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // On génère un token Sanctum, utilisé par Flutter pour
        // toutes les requêtes suivantes (Authorization: Bearer ...).
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // ------------------------------------------------------------
    // CONNEXION — POST /api/login
    // Appelée depuis l'écran login_screen.dart (Flutter)
    // ------------------------------------------------------------
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Étape 1 : email + mot de passe corrects ?
        // On vérifie ça AVANT le statut, pour ne jamais révéler à
        // quelqu'un qui ne connaît pas le mot de passe qu'un compte
        // "bloqué" existe avec cet email.
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Identifiants incorrects',
            ], 401);
        }

        // Étape 2 : le compte est-il bloqué ?
        if ($user->statut === 'bloque') {
            return response()->json([
                'message' => 'Votre compte a été bloqué. Contactez l\'administrateur.',
            ], 403);
        }

        // Étape 3 : connexion autorisée, on génère le token.
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token,
        ]);
    }

    // ------------------------------------------------------------
    // DÉCONNEXION — POST /api/logout
    // Nécessite d'être connecté (voir middleware auth:sanctum
    // dans routes/api.php)
    // ------------------------------------------------------------
    public function logout(Request $request)
    {
        // Supprime uniquement le token utilisé pour CETTE requête,
        // pas tous les tokens de l'utilisateur (utile s'il est
        // connecté sur plusieurs appareils en même temps).
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }

    // ------------------------------------------------------------
    // UTILISATEUR CONNECTÉ — GET /api/user
    // Utilisée par le splash screen pour vérifier si le token
    // stocké localement est toujours valide.
    // ------------------------------------------------------------
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
