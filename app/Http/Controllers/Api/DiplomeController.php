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
    // ------------------------------------------------------------
    // POST /api/diplomes/verifier
    // ------------------------------------------------------------
    // Pipeline complet :
    //   A. Réception + sauvegarde de l'image
    //   B. OCR (Tesseract) : extraction du texte brut
    //   C. Gemini : structuration des données (texte + image)
    //   D. Comparaison avec base_references
    //   E. Mise à jour du diplôme + création de la vérification
    // ------------------------------------------------------------
    public function verifier(Request $request)
    {
        // --- ÉTAPE A : réception et sauvegarde de l'image ---
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        $fichier = $request->file('image');
        $cheminFichier = $fichier->store('diplomes', 'public');

        // Chemin absolu sur le disque, nécessaire pour Tesseract/Gemini
        $cheminAbsolu = Storage::disk('public')->path($cheminFichier);

        // Ligne créée avec valeurs temporaires
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
            // --- ÉTAPE B : OCR ---
            $texteOcr = (new OcrService())->extraireTexte($cheminAbsolu);

            // --- ÉTAPE C : Gemini ---
            $donneesExtraites = (new GeminiService())->structurerDiplome($texteOcr, $cheminAbsolu);

            // --- ÉTAPE D : Comparaison avec base_references ---
            $resultatComparaison = (new ComparaisonService())->comparer($donneesExtraites);

            // --- ÉTAPE E : mise à jour du diplôme avec les vraies données ---
            $diplome->update([
                'numeroDiplome' => $donneesExtraites['numeroDiplome'] ?? 'inconnu',
                'typeDiplome' => $donneesExtraites['typeDiplome'] ?? 'inconnu',
                'nomTitulaire' => $donneesExtraites['nomTitulaire'] ?? 'inconnu',
                'dateObtention' => $donneesExtraites['dateObtention'] ?? null,
                'etablissement' => $donneesExtraites['etablissement'] ?? 'inconnu',
                'mention' => $donneesExtraites['mention'] ?? null,
            ]);

          // Création de la vérification liée
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
            // Si l'analyse échoue (Gemini indisponible, OCR échoue...),
            // le diplôme reste enregistré mais marqué en erreur.
            return response()->json([
                'message' => 'Le diplôme a été reçu, mais son analyse a échoué.',
                'diplome' => $diplome,
                'erreur' => $e->getMessage(),
            ], 500);
        }
    }
}