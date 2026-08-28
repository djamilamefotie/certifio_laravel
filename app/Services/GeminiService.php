<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected string $modele = 'gemini-3.6-flash';

    public function structurerDiplome(string $texteOcrBrut, string $cheminImage): array
    {
        $cle = config('services.gemini.key');

        $imagePreparee = $this->preparerImage($cheminImage);
        $imageData = $imagePreparee['data'];
        $mimeType = $imagePreparee['mime'];

        $prompt = <<<PROMPT
Voici le texte brut extrait par OCR (Tesseract) d'un diplôme, ET
l'image originale du diplôme.

Le texte OCR contient BEAUCOUP de bruit et peut avoir mal lu
certains mots (noms propres en police stylisée, numéros...).
UTILISE L'IMAGE pour vérifier et corriger le texte OCR quand ils
ne correspondent pas, en particulier pour le nom du titulaire.

Tu dois aussi ANALYSER VISUELLEMENT l'image pour détecter tout
signe possible de falsification ou de montage, par exemple :
- une police, une taille de texte ou un alignement incohérent
  par rapport au reste du document
- un tampon ou une signature flou(e), mal positionné(e), ou absent(e)
- des traces de montage numérique (bords nets suspects,
  pixellisation localisée, différences de qualité d'image entre
  zones du document)
- un fond, papier, ou texture qui semble modifié ou incohérent

Ta tâche : extraire les informations suivantes et répondre
STRICTEMENT en JSON valide, sans aucun texte avant ou après, avec
exactement ces clés :

{
  "numeroDiplome": "le numéro/matricule du diplôme, ou null si absent",
  "typeDiplome": "le type de diplôme (ex: Brevet d'Études du Premier Cycle), ou null",
  "nomTitulaire": "le nom complet de la personne titulaire, lu attentivement sur l'image, ou null",
  "dateObtention": "la date d'obtention au format AAAA-MM-JJ, ou null",
   "etablissement": "le nom de l'établissement scolaire précis (école, lycée, université) fréquenté par le titulaire — PAS le nom du ministère ni d'une autorité nationale. Cherche typiquement le nom mentionné dans le procès-verbal ou la mention de la commission d'examen, ou null",
  "mention": "la mention obtenue (ex: Passable, Assez Bien...), ou null",
  "anomalieVisuelleDetectee": true ou false,
  "indicesAnomalieVisuelle": ["liste de phrases courtes décrivant chaque indice suspect trouvé, vide si aucune anomalie"],
  "niveauConfianceVisuelle": "un nombre entre 0 et 100 représentant ta confiance que le document est visuellement authentique (100 = aucun doute, 0 = falsification quasi certaine)"
}

Sois rigoureux mais mesuré : un document de mauvaise qualité de
scan/photo (flou général, luminosité faible) n'est PAS en soi un
signe de falsification. Ne signale une anomalie que si elle est
localisée ou spécifique (une zone qui contraste avec le reste du
document).

Texte OCR brut (aide, peut contenir des erreurs) :
---
{$texteOcrBrut}
---
PROMPT;

        $reponse = Http::timeout(120)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$this->modele}:generateContent?key={$cle}",
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $imageData,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ],
            ]
        );

        if (!$reponse->successful()) {
            throw new \Exception('Erreur API Gemini : ' . $reponse->body());
        }

        $texteJson = $reponse->json('candidates.0.content.parts.0.text');
        $donnees = json_decode($texteJson, true);

        if (!is_array($donnees)) {
            return [
                'numeroDiplome' => null,
                'typeDiplome' => null,
                'nomTitulaire' => null,
                'dateObtention' => null,
                'etablissement' => null,
                'mention' => null,
                'anomalieVisuelleDetectee' => false,
                'indicesAnomalieVisuelle' => [],
                'niveauConfianceVisuelle' => null,
            ];
        }

        return $donnees;
    }

    protected function preparerImage(string $cheminImage): array
    {
        $image = imagecreatefromjpeg($cheminImage);
        $largeurOriginale = imagesx($image);
        $hauteurOriginale = imagesy($image);

        $largeurMax = 1500;
        if ($largeurOriginale > $largeurMax) {
            $ratio = $largeurMax / $largeurOriginale;
            $nouvelleLargeur = $largeurMax;
            $nouvelleHauteur = (int) ($hauteurOriginale * $ratio);

            $imageRedimensionnee = imagecreatetruecolor($nouvelleLargeur, $nouvelleHauteur);
            imagecopyresampled(
                $imageRedimensionnee, $image,
                0, 0, 0, 0,
                $nouvelleLargeur, $nouvelleHauteur,
                $largeurOriginale, $hauteurOriginale
            );
            $image = $imageRedimensionnee;
        }

        ob_start();
        imagejpeg($image, null, 80);
        $donneesImage = ob_get_clean();

        return [
            'data' => base64_encode($donneesImage),
            'mime' => 'image/jpeg',
        ];
    }
}