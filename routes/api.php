<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ------------------------------------------------------------
// ROUTES PUBLIQUES (pas besoin d'être connecté)
// ------------------------------------------------------------
// Ce sont les 2 routes qu'appelle l'app Flutter AVANT que
// l'utilisateur ait un token : inscription et connexion.
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ------------------------------------------------------------
// ROUTES PROTÉGÉES (l'utilisateur doit envoyer un token valide
// dans l'en-tête "Authorization: Bearer {token}")
// ------------------------------------------------------------
// Le middleware "auth:sanctum" vérifie automatiquement le token
// avant de laisser passer la requête. Si le token est invalide
// ou absent, Laravel renvoie une erreur 401 tout seul (pas besoin
// de coder cette vérification nous-mêmes).
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});