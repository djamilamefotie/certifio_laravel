<?php

namespace App\Services;

use App\Models\Paiement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// ============================================================
// SERVICE DE PAIEMENT — CERTIFIO
// ------------------------------------------------------------
// Gère toute la logique liée à Monetbil :
//   1. initierPaiement() : crée un enregistrement Paiement en
//      base et appelle l'API Monetbil pour obtenir une URL de
//      paiement (ouverte ensuite dans une WebView côté Flutter).
//   2. traiterNotificationWebhook() : reçoit la confirmation de
//      Monetbil (POST vers notify_url), met à jour le paiement
//      et active l'abonnement premium si le paiement est réussi.
//
// IMPORTANT : le format exact des champs envoyés par Monetbil
// dans le webhook n'est pas garanti à 100% tant qu'on n'a pas
// reçu une vraie notification de test. Cette méthode logue tout
// le payload brut (voir donnees_webhook en base + logs Laravel)
// pour permettre d'ajuster facilement le parsing si besoin.
// ============================================================
class PaiementService
{
    /**
     * Démarre un paiement Monetbil pour passer un utilisateur en Premium.
     *
     * @return array{succes: bool, message: string, payment_url: ?string, reference: ?string}
     */
    public function initierPaiement(User $user, string $telephone, ?string $operateur = null): array
    {
        $reference = (string) Str::uuid();
        $montant = (int) config('services.monetbil.abonnement_montant');

        $paiement = Paiement::create([
            'user_id' => $user->id,
            'reference' => $reference,
            'article_ref' => 'abonnement_premium',
            'montant' => $montant,
            'devise' => config('services.monetbil.devise'),
            'telephone' => $telephone,
            'operateur' => $operateur,
            'statut' => 'en_attente',
        ]);

        try {
            $serviceKey = config('services.monetbil.service_key');

            $reponse = Http::asForm()->post(
                config('services.monetbil.api_url') . '/' . $serviceKey,
                [
                    'amount' => $montant,
                    'phone' => $telephone,
                    'phone_lock' => false,
                    'locale' => 'fr',
                    'operator' => $operateur,
                    'country' => config('services.monetbil.pays'),
                    'currency' => config('services.monetbil.devise'),
                    'item_ref' => $paiement->article_ref,
                    'payment_ref' => $reference,
                    'user' => $user->id,
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'return_url' => config('services.monetbil.return_url'),
                    'notify_url' => config('services.monetbil.notify_url'),
                ]
            );

            $donnees = $reponse->json();

            Log::info('Monetbil - réponse initiation paiement', [
                'reference' => $reference,
                'statut_http' => $reponse->status(),
                'donnees' => $donnees,
            ]);

            // La Widget API v2.1 de Monetbil renvoie normalement soit une
            // URL de paiement directement, soit un champ "payment_url".
            // On couvre les deux cas possibles.
            $paymentUrl = $donnees['payment_url']
                ?? (is_string($donnees) ? $donnees : null)
                ?? null;

            if (!$reponse->successful() || !$paymentUrl) {
                $paiement->update(['statut' => 'echoue']);

                return [
                    'succes' => false,
                    'message' => $donnees['message'] ?? 'Impossible de démarrer le paiement. Réessayez.',
                    'payment_url' => null,
                    'reference' => $reference,
                ];
            }

            $paiement->update(['payment_url' => $paymentUrl]);

            return [
                'succes' => true,
                'message' => 'Paiement initié avec succès.',
                'payment_url' => $paymentUrl,
                'reference' => $reference,
            ];
        } catch (\Throwable $e) {
            Log::error('Monetbil - erreur initiation paiement', [
                'reference' => $reference,
                'erreur' => $e->getMessage(),
            ]);

            $paiement->update(['statut' => 'echoue']);

            return [
                'succes' => false,
                'message' => 'Impossible de contacter le service de paiement. Vérifiez votre connexion.',
                'payment_url' => null,
                'reference' => $reference,
            ];
        }
    }

    /**
     * Traite la notification envoyée par Monetbil sur notify_url une fois
     * le paiement terminé (réussi ou échoué côté opérateur mobile).
     *
     * @param array<string, mixed> $payload Contenu brut envoyé par Monetbil
     */
    public function traiterNotificationWebhook(array $payload): array
    {
        Log::info('Monetbil - webhook reçu', ['payload' => $payload]);

        // Monetbil renvoie normalement "payment_ref" = la référence qu'on
        // avait envoyée à l'initiation.
        $reference = $payload['payment_ref'] ?? $payload['reference'] ?? null;

        if (!$reference) {
            Log::warning('Monetbil - webhook sans référence de paiement', $payload);
            return ['succes' => false, 'message' => 'Référence de paiement manquante.'];
        }

        $paiement = Paiement::where('reference', $reference)->first();

        if (!$paiement) {
            Log::warning('Monetbil - webhook pour un paiement inconnu', ['reference' => $reference]);
            return ['succes' => false, 'message' => 'Paiement introuvable.'];
        }

        // On stocke toujours le payload brut, même si le parsing du statut
        // ci-dessous doit être ajusté plus tard.
        $paiement->donnees_webhook = $payload;

        // Le champ de statut exact peut varier ("status", "state"...).
        // On vérifie plusieurs variantes courantes.
        $statutBrut = strtolower((string) (
            $payload['status'] ?? $payload['state'] ?? ''
        ));

        $estReussi = in_array($statutBrut, ['success', 'successful', 'completed', '1', 'true'], true);

        if (isset($payload['transaction_id'])) {
            $paiement->transaction_id_monetbil = $payload['transaction_id'];
        }

        if ($estReussi) {
            $paiement->statut = 'reussi';
            $paiement->save();

            $this->activerAbonnementPremium($paiement->user);

            return ['succes' => true, 'message' => 'Paiement confirmé, abonnement activé.'];
        }

        $paiement->statut = 'echoue';
        $paiement->save();

        return ['succes' => true, 'message' => 'Paiement marqué comme échoué.'];
    }

    /**
     * Passe l'utilisateur en Premium et fixe la date d'expiration.
     */
    private function activerAbonnementPremium(User $user): void
    {
        $dureeJours = (int) config('services.monetbil.abonnement_duree_jours');

        // Si l'utilisateur a déjà un abonnement premium en cours, on
        // prolonge à partir de la date d'expiration actuelle plutôt que
        // de repartir d'aujourd'hui (évite de "perdre" des jours payés).
        $dateDepart = ($user->abonnement === 'premium' && $user->abonnement_expire_le?->isFuture())
            ? $user->abonnement_expire_le
            : Carbon::now();

        $user->update([
            'abonnement' => 'premium',
            'abonnement_expire_le' => $dateDepart->addDays($dureeJours),
        ]);

        Log::info('Abonnement premium activé', [
            'user_id' => $user->id,
            'expire_le' => $user->abonnement_expire_le,
        ]);
    }
}