<?php

namespace App\Services;

use App\Models\BaseReference;

class ComparaisonService
{
    public function comparer(array $donneesExtraites): array
    {
        $numero = $donneesExtraites['numeroDiplome'] ?? null;
        $anomalieVisuelle = $donneesExtraites['anomalieVisuelleDetectee'] ?? false;
        $indicesVisuels = $donneesExtraites['indicesAnomalieVisuelle'] ?? [];
        $confianceVisuelle = $donneesExtraites['niveauConfianceVisuelle'] ?? 100;

        if (!$numero) {
            return [
                'statut' => 'ambigu',
                'resultat' => "Aucun numéro de diplôme n'a pu être extrait. Vérification impossible.",
                'reference_id' => null,
                'scoreFinal' => 0,
            ];
        }

        $reference = BaseReference::where('numeroDiplome', $numero)->first();

        if (!$reference) {
            return [
                'statut' => 'ambigu',
                'resultat' => "Numéro de diplôme {$numero} introuvable dans la base de référence officielle.",
                'reference_id' => null,
                'scoreFinal' => 0,
            ];
        }

        $champs = [
            'typeDiplome' => $reference->typeDiplome,
            'etablissement' => $reference->institution,
        ];

        $poidsParChamp = 70 / count($champs);
        $scoreTextuel = 0;
        $anomalies = [];

        foreach ($champs as $cle => $valeurReference) {
            if ($this->correspond($donneesExtraites[$cle] ?? '', $valeurReference)) {
                $scoreTextuel += $poidsParChamp;
            } else {
                $anomalies[] = "Le champ '{$cle}' ne correspond pas (extrait : '{$donneesExtraites[$cle]}', référence : '{$valeurReference}').";
            }
        }

        $scoreVisuel = ($confianceVisuelle / 100) * 30;
        $score = round($scoreTextuel + $scoreVisuel, 2);

        $messages = [];
        if (!empty($anomalies)) {
            $messages[] = "Anomalies textuelles :\n- " . implode("\n- ", $anomalies);
        }
        if ($anomalieVisuelle && !empty($indicesVisuels)) {
            $messages[] = "Anomalies visuelles détectées :\n- " . implode("\n- ", $indicesVisuels);
        }

        if ($score >= 90 && !$anomalieVisuelle) {
            $statut = 'authentique';
            if (empty($messages)) {
                $messages[] = "Diplôme confirmé. Toutes les informations et l'analyse visuelle sont cohérentes avec la base de référence officielle.";
            }
        } elseif ($score >= 40) {
            $statut = 'suspect';
        } else {
            $statut = 'ambigu';
        }

        return [
            'statut' => $statut,
            'resultat' => implode("\n\n", $messages) ?: "Analyse effectuée.",
            'reference_id' => $reference->id,
            'scoreFinal' => $score,
        ];
    }

    protected function correspond(string $a, string $b): bool
    {
        $normaliser = function (string $texte) {
            $texte = mb_strtolower(trim($texte));
            $texte = iconv('UTF-8', 'ASCII//TRANSLIT', $texte);
            return preg_replace('/[^a-z0-9]/', '', $texte);
        };

        return $normaliser($a) === $normaliser($b);
    }
}