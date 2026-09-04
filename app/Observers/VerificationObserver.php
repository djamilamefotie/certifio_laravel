<?php

namespace App\Observers;

use App\Models\Verification;
use App\Services\FraudeNotificationService;

class VerificationObserver
{
    public function __construct(private FraudeNotificationService $notifier) {}

    public function created(Verification $verification)
    {
        if ($verification->statut === 'ambigu') {
            return;
        }

        $this->envoyerNotification($verification);
    }

    public function updated(Verification $verification)
    {
        // Notifie seulement quand l'admin vient de résoudre une
        // vérification qui était ambiguë (statut passe de "ambigu"
        // à "authentique" ou "suspect").
        if ($verification->wasChanged('statut')
            && $verification->getOriginal('statut') === 'ambigu'
            && $verification->statut !== 'ambigu') {
            $this->envoyerNotification($verification);
        }
    }

    private function envoyerNotification(Verification $verification): void
    {
        $client = $verification->diplome->client;

        $libelles = [
            'authentique' => 'authentique ✅',
            'suspect' => 'suspect ⚠️',
        ];

        $libelle = $libelles[$verification->statut] ?? $verification->statut;

        $this->notifier->alerterFraude(
            $client,
            (string) $verification->id,
            "Résultat disponible : votre diplôme a été jugé {$libelle}."
        );
    }
}
