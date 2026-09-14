<?php

namespace App\Http\Controllers;

use App\Services\PaiementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaiementController extends Controller
{
    public function __construct(
        private readonly PaiementService $paiementService
    ) {
    }

    /**
     * POST /api/paiements/initier
     * Protégée par auth:sanctum — démarre un paiement pour l'utilisateur connecté.
     */
    public function initier(Request $request)
    {
        $donnees = $request->validate([
            'telephone' => ['required', 'string', 'min:9', 'max:15'],
            'operateur' => ['nullable', 'string'], // ex: CM_MTNMOBILEMONEY, CM_ORANGEMONEY
        ]);

        $resultat = $this->paiementService->initierPaiement(
            $request->user(),
            $donnees['telephone'],
            $donnees['operateur'] ?? null
        );

        return response()->json($resultat, $resultat['succes'] ? 200 : 422);
    }

    /**
     * POST /api/paiements/webhook
     * Route PUBLIQUE (pas d'auth) — appelée directement par les serveurs
     * Monetbil pour confirmer un paiement. Ne jamais protéger par
     * auth:sanctum, Monetbil n'a pas de token utilisateur.
     */
    public function webhook(Request $request)
    {
        $resultat = $this->paiementService->traiterNotificationWebhook($request->all());

        // On répond toujours 200 à Monetbil pour accuser réception,
        // même en cas d'erreur de notre côté (sinon Monetbil peut
        // renvoyer la notification en boucle).
        return response()->json($resultat, 200);
    }

    /**
     * GET /api/paiements/retour
     * Page de retour après paiement (redirection navigateur/WebView).
     * Simple accusé de réception ; le vrai statut est mis à jour via webhook.
     */
    public function retour(Request $request)
    {
        return response()->json([
            'message' => 'Merci, votre paiement est en cours de traitement. Retournez sur l\'application Certifio.',
        ]);<?php

namespace App\Observers;

use App\Models\Verification;
use App\Services\FraudeNotificationService;

class VerificationObserver
{
    public function __construct(private FraudeNotificationService $notifier) {}

    public function updated(Verification $verification)
    {
        // On notifie seulement quand le statut change ET que le résultat
        // n'est plus "ambigu" (donc le traitement est terminé).
        if ($verification->wasChanged('statut') && $verification->statut !== 'ambigu') {

            $client = $verification->diplome->client;

            $libelles = [
                'authentique' => 'authentique',
                'suspect' => 'suspect',
            ];

            $libelle = $libelles[$verification->statut] ?? $verification->statut;

            $this->notifier->alerterFraude(
                $client,
                (string) $verification->id,
                "Résultat disponible : votre diplôme a été jugé {$libelle}."
            );
        }
    }
}
    }
}