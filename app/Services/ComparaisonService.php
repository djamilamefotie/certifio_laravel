<?php

namespace App\Services;

use App\Models\BaseReference;
use Illuminate\Support\Facades\DB;

class ComparaisonService
{
    public function comparer(array $donneesExtraites): array
    {
        $numero = $donneesExtraites['numeroDiplome'] ?? null;
        $typeDiplome = $donneesExtraites['typeDiplome'] ?? null;
        $institution = $donneesExtraites['institution'] ?? null;
        $anomalieVisuelle = $donneesExtraites['anomalieVisuelleDetectee'] ?? false;
        $indicesVisuels = $donneesExtraites['indicesAnomalieVisuelle'] ?? [];
        $confianceVisuelle = $donneesExtraites['niveauConfianceVisuelle'] ?? 100;

        if (!$numero || !$typeDiplome || !$institution) {
            return [
                'statut' => 'ambigu',
                'resultat' => "Numéro de diplôme, type de diplôme ou institution manquant. Vérification impossible.",
                'reference_id' => null,
                'scoreFinal' => 50,
            ];
        }

        $reference = BaseReference::where('numeroDiplome', $numero)
            ->where('typeDiplome', $typeDiplome)
            ->where('institution', $institution)
            ->first();

        if (!$reference) {
            return [
                'statut' => 'ambigu',
                'resultat' => "Diplôme {$numero} ({$typeDiplome}, {$institution}) introuvable dans la base de référence officielle.",
                'reference_id' => null,
                'scoreFinal' => 50,
            ];
        }

        $regle = DB::table('regles_verification')
            ->where('institution', $reference->institution)
            ->where('typeDiplome', $reference->typeDiplome)
            ->first();

        if (!$regle) {
            return [
                'statut' => 'ambigu',
                'resultat' => "Aucune règle de vérification définie pour {$typeDiplome} / {$institution}.",
                'reference_id' => $reference->id,
                'scoreFinal' => 50,
            ];
        }

        $champsObligatoires = json_decode($regle->champsObligatoires, true);

        $anomalies = [];
        foreach ($champsObligatoires as $champ) {
            $valeurExtraite = (string) ($donneesExtraites[$champ] ?? '');
            $valeurReference = (string) ($reference->$champ ?? '');

            if (!$this->correspond($valeurExtraite, $valeurReference)) {
                $anomalies[] = "Le champ '{$champ}' ne correspond pas (extrait : '{$valeurExtraite}', référence : '{$valeurReference}').";
            }
        }

        $messages = [];
        if (!empty($anomalies)) {
            $messages[] = "Anomalies textuelles :\n- " . implode("\n- ", $anomalies);
        }
        if ($anomalieVisuelle && !empty($indicesVisuels)) {
            $messages[] = "Anomalies visuelles détectées :\n- " . implode("\n- ", $indicesVisuels);
        }

        if (empty($anomalies) && !$anomalieVisuelle) {
            $statut = 'authentique';
            $score = round(70 + ($confianceVisuelle / 100) * 30, 2);
            if (empty($messages)) {
                $messages[] = "Diplôme confirmé. Toutes les informations et l'analyse visuelle sont cohérentes avec la base de référence officielle.";
            }
        } else {
            $statut = 'suspect';
            $nbChampsErreur = count($anomalies);
            $score = round(max(0, 70 - ($nbChampsErreur * (70 / count($champsObligatoires)))) + ($confianceVisuelle / 100) * 30, 2);
        }

        return [
            'statut' => $statut,
            'resultat' => implode("\n\n", $messages) ?: "Analyse effectuée.",
            'reference_id' => $reference->id,
            'scoreFinal' => $score,
        ];
    }

    protected function correspond(string $a, string $b, float $seuilSimilarite = 88.0): bool
    {
        $normaliser = function (string $texte) {
            $texte = mb_strtolower(trim($texte));
            $texte = iconv('UTF-8', 'ASCII//TRANSLIT', $texte);
            return preg_replace('/[^a-z0-9]/', '', $texte);
        };

        $na = $normaliser($a);
        $nb = $normaliser($b);

        // Deux chaînes vides ne sont pas considérées comme correspondantes
        if ($na === '' || $nb === '') {
            return $na === $nb;
        }

        // Égalité stricte après normalisation (cas le plus courant)
        if ($na === $nb) {
            return true;
        }

        // Tolérance aux petites variations : sigles/abréviations (ex: "IUES" vs "UES"),
        // coquilles OCR, légers écarts de frappe entre l'extraction IA et la référence.
        similar_text($na, $nb, $pourcentage);

        return $pourcentage >= $seuilSimilarite;
    }
}