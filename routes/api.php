<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DiplomeController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\PasswordResetController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\FcmTokenController;

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

     // ... tes routes existantes (user, logout, diplomes/verifier, etc.)

    Route::post('/paiements/initier', [PaiementController::class, 'initier']);    
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Étape A du pipeline : reçoit et sauvegarde l'image du diplôme.
    Route::post('/diplomes/verifier', [DiplomeController::class, 'verifier']);
    Route::get('/diplomes/historique', [DiplomeController::class, 'historique']);
});
Route::middleware('auth:sanctum')->put('/user', [AuthController::class, 'updateProfile']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetCode']);
Route::post('/verify-reset-code', [PasswordResetController::class, 'verifyCode']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/diplomes/verifier-test', [DiplomeController::class, 'verifierTest']);
// ============================================================
// À AJOUTER dans routes/api.php
// ============================================================


// --- Routes PUBLIQUES (en dehors du groupe auth:sanctum, à mettre au
//     même niveau que /register, /login) ---
Route::post('/paiements/webhook', [PaiementController::class, 'webhook']);
Route::get('/paiements/retour', [PaiementController::class, 'retour']);
Route::middleware('auth:sanctum')->post('/fcm-token', [FcmTokenController::class, 'store']);