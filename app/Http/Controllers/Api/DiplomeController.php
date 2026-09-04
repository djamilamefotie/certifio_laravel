<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Diplome;
use App\Models\Verification;
use App\Services\OcrService;
use App\Services\GeminiService;
use App\Services\ComparaisonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DiplomeController extends Controller
{
    public function verifier(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        if ($request->user()->statut === 'bloque') {
            return response()->json([
                'message' => 'Votre compte a été bloqué. Contactez le support pour plus d\'informations.',
            ], 403);
        }

            // ------------------------------------------------------------
    // VÉRIFICATION DE LA LIMITE D'ABONNEMENT
    // ------------------------------------------------------------
    $user = $request->user();
    $offre = \App\Models\OffreAbonnement::where('type', $user->abonnement)
        ->where('actif', true)
        ->first();

    if ($offre && $offre->limite_verifications !== null) {
        // Détermine le début de la période en cours pour cet utilisateur
        $debutPeriode = $user->abonnement === 'premium'
            ? $user->abonnement_expire_le?->subDays($offre->duree_jours)
            : $user->periode_gratuite_debut;

        $periodeExpiree = !$debutPeriode
            || now()->diffInDays($debutPeriode) >= $offre->duree_jours;

        if ($periodeExpiree) {
            // Nouvelle période : on redémarre le compteur
            $debutPeriode = now();
            if ($user->abonnement !== 'premium') {
                $user->update(['periode_gratuite_debut' => $debutPeriode]);
            }
        }

        $nombreVerifications = \App\Models\Verification::whereHas('diplome', function ($q) use ($user) {
            $q->where('client_id', $user->id);
        })->where('created_at', '>=', $debutPeriode)->count();

        if ($nombreVerifications >= $offre->limite_verifications) {
            return response()->json([
                'message' => "Vous avez atteint la limite de {$offre->limite_verifications} vérifications pour votre offre \"{$offre->nom}\". Passez à un abonnement supérieur pour continuer.",
            ], 403);
        }
    }

        $fichier = $request->file('image');
        $cheminFichier = $fichier->store('diplomes', 'public');
        $cheminAbsolu = Storage::disk('public')->path($cheminFichier);

        $diplome = Diplome::create([
            'fichier' => $cheminFichier,
            'client_id' => $request->user()->id,
            'numeroDiplome' => 'à_traiter',
            'typeDiplome' => 'à_traiter',
            'nomTitulaire' => 'à_traiter',
            'dateObtention' => now(),
            'etablissement' => 'à_traiter',
        ]);

        try {
            $texteOcr = (new OcrService())->extraireTexte($cheminAbsolu);
            $donneesExtraites = (new GeminiService())->structurerDiplome($texteOcr, $cheminAbsolu);
            $resultatComparaison = (new ComparaisonService())->comparer($donneesExtraites);

            $diplome->update([
                'numeroDiplome' => $donneesExtraites['numeroDiplome'] ?? 'inconnu',
                'typeDiplome' => $donneesExtraites['typeDiplome'] ?? 'inconnu',
                'nomTitulaire' => $donneesExtraites['nomTitulaire'] ?? 'inconnu',
                'dateObtention' => $donneesExtraites['dateObtention'] ?? null,
                'etablissement' => $donneesExtraites['etablissement'] ?? 'inconnu',
                'mention' => $donneesExtraites['mention'] ?? null,
            ]);

            $verification = Verification::create([
                'diplome_id' => $diplome->id,
                'base_reference_id' => $resultatComparaison['reference_id'],
                'dateVérification' => now(),
                'statut' => $resultatComparaison['statut'],
                'resultat' => $resultatComparaison['resultat'],
                'texteOcrBrut' => $texteOcr,
                'donneesAnalyseIa' => $donneesExtraites,
                'scoreFinal' => $resultatComparaison['scoreFinal'],
            ]);

            return response()->json([
                'message' => 'Diplôme analysé et vérifié avec succès.',
                'diplome' => $diplome->fresh(),
                'verification' => $verification,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Le diplôme a été reçu, mais son analyse a échoué.',
                'diplome' => $diplome,
                'erreur' => $e->getMessage(),
            ], 500);
        }
    }

    // ------------------------------------------------------------
    // POST /api/diplomes/verifier-test
    // ------------------------------------------------------------
    // Route de TEST : reçoit directement les données déjà "extraites"
    // en JSON, sans image, sans OCR, sans Gemini. Sert uniquement à
    // tester la logique de comparaison avec base_references.
    // ------------------------------------------------------------
    public function verifierTest(Request $request)
    {
        $request->validate([
            'numeroDiplome' => 'required|string',
            'typeDiplome' => 'required|string',
            'institution' => 'required|string',
            'nomTitulaire' => 'nullable|string',
            'dateNaissance' => 'nullable|date',
            'lieuNaissance' => 'nullable|string',
            'etablissement' => 'nullable|string',
            'centreExamen' => 'nullable|string',
            'dateObtention' => 'nullable|date',
            'matricule' => 'nullable|string',
            'mention' => 'nullable|string',
            'serieOuFiliere' => 'nullable|string',
        ]);

        $donneesExtraites = $request->all();
        $resultatComparaison = (new ComparaisonService())->comparer($donneesExtraites);

        return response()->json([
            'message' => 'Test de comparaison effectué (sans OCR/Gemini).',
            'donneesEnvoyees' => $donneesExtraites,
            'resultat' => $resultatComparaison,
        ], 200);
    }

    // ------------------------------------------------------------
    // GET /api/diplomes/historique
    // ------------------------------------------------------------
    // Renvoie toutes les vérifications de l'utilisateur connecté,
    // les plus récentes en premier, avec les infos du diplôme
    // associé (nom, type, etc.) chargées en même temps.
    // ------------------------------------------------------------
    public function historique(Request $request)
    {
        if ($request->user()->statut === 'bloque') {
            return response()->json([
                'message' => 'Votre compte a été bloqué. Contactez le support pour plus d\'informations.',
            ], 403);
        }

        $verifications = Verification::whereHas('diplome', function ($requete) use ($request) {
                $requete->where('client_id', $request->user()->id);
            })
            ->with('diplome')
            ->orderBy('dateVérification', 'desc')
            ->get();

        return response()->json([
            'verifications' => $verifications,
        ], 200);
    }
}