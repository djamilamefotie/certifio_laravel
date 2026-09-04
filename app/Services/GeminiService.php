<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected string $modele = 'gemini-3.6-flash';

    public function structurerDiplome(string $texteOcrBrut, string $cheminImage): array
    {
        $imagePreparee = $this->preparerImage($cheminImage);
        $imageData = $imagePreparee['data'];
        $mimeType = $imagePreparee['mime'];

        $dateDuJour = now()->translatedFormat('d F Y'); // ex: "01 September 2026"

        $prompt = <<<PROMPT
Nous sommes actuellement le {$dateDuJour}. Utilise TOUJOURS cette
date comme référence pour évaluer si une date figurant sur le
document est passée, présente ou future. Ne te base JAMAIS sur ta
propre connaissance interne de la date actuelle : celle-ci peut
être obsolète. Une date de délivrance ou d'obtention antérieure au
{$dateDuJour} est une date PASSÉE et donc parfaitement normale —
ce n'est PAS une anomalie.

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
- des dates réellement incohérentes (par exemple une date de
  délivrance ANTÉRIEURE à la date d'obtention, ou une date
  POSTÉRIEURE au {$dateDuJour}) — mais uniquement dans ce cas précis

Ta tâche : extraire les informations suivantes et répondre
STRICTEMENT en JSON valide, sans aucun texte avant ou après, avec
exactement ces clés :

{
  "numeroDiplome": "UNIQUEMENT le numéro du diplôme lui-même (ex: '25/011600061420'), SANS aucun code administratif qui le suivrait sur la même ligne (ex: ne PAS inclure 'MINESUP/SG/DCAA/SDDA' ou tout sigle similaire, même s'il est collé au numéro ou séparé par un espace). Si un tel code est présent juste après le numéro, ignore-le entièrement et ne garde que le numéro. Ou null si absent",
  "typeDiplome": "le type de diplôme, EXCLUSIVEMENT une de ces valeurs courtes exactes : 'CEP', 'BEPC', 'Baccalauréat', 'BT', 'BTS', 'DUT', 'HND', 'Licence', 'Master', 'Doctorat' (choisis la plus proche du document, ne renvoie jamais de version longue), ou null",
  "institution": "l'organisme superviseur national qui délivre ce type de diplôme, EXCLUSIVEMENT une de ces valeurs exactes : 'MINEDUB' (pour CEP), 'MINESEC' (pour BEPC), 'OBC' (pour Baccalauréat), 'MINESUP' (pour diplômes universitaires), ou null si tu ne peux pas déterminer avec certitude",
  "nomTitulaire": "le nom complet de la personne titulaire, lu attentivement sur l'image, ou null",
  "dateNaissance": "la date de naissance du titulaire, au format AAAA-MM-JJ, ou null si absente du document",
  "matricule": "le matricule ou identifiant scolaire du titulaire (différent du numéro de diplôme), ou null si absent",
  "serieOuFiliere": "la série, filière ou secteur d'étude suivi par le titulaire. Pour un Baccalauréat : cherche la 'Série' (ex: 'A4 : LETTRES-PHILOSOPHIE (LVII ESPAGNOL)', 'C', 'D'...). Pour un diplôme universitaire ou HND (BTS, DUT, HND, Licence, Master...) : cherche un champ intitulé 'Sector', 'Field', 'Filière', 'Domain', 'Spécialité' ou équivalent (ex: 'Animal Production Technology', 'Génie Informatique'). Si PLUSIEURS champs de ce type existent sur le document (ex: à la fois 'Domain' et 'Sector'), privilégie le plus précis/spécifique (généralement 'Sector' ou 'Spécialité', pas la catégorie large 'Domain'/'Field'). Ou null si absente",
  "dateObtention": "la date à laquelle l'examen/diplôme a été OBTENU par le titulaire (généralement liée à la session d'examen, ex: 'Session de Juin AAAA'), au format AAAA-MM-JJ. ATTENTION : ne pas confondre avec la date de délivrance/signature du document (souvent une phrase du type 'Fait à [ville] le [date]'), qui est une date différente et plus tardive. Si tu ne trouves qu'une date de session (ex: 'Juin 2021'), utilise le 01 comme jour : '2021-06-01'. Ou null",
  "dateDelivrance": "la date à laquelle le document physique a été signé/délivré (phrase du type 'Fait à [ville] le [date]'), au format AAAA-MM-JJ, ou null",
  "etablissement": "le nom de l'établissement scolaire précis (école, lycée, université) fréquenté par le titulaire — PAS le nom du ministère ni d'une autorité nationale. Cherche typiquement le nom mentionné dans le procès-verbal ou la mention de la commission d'examen, ou null",
  "centreExamen": "le centre d'examen où s'est déroulé l'examen (souvent mentionné près du procès-verbal, ex: 'Yaoundé Lycée Nkolndongo'), ou null si absent",
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

        $reponse = $this->appellerGeminiAvecAlternance($prompt, $imageData, $mimeType);

        $texteJson = $reponse->json('candidates.0.content.parts.0.text');
        $donnees = json_decode($texteJson, true);

        if (!is_array($donnees)) {
            return [
                'numeroDiplome' => null,
                'typeDiplome' => null,
                'institution' => null,
                'nomTitulaire' => null,
                'dateNaissance' => null,
                'matricule' => null,
                'serieOuFiliere' => null,
                'dateObtention' => null,
                'dateDelivrance' => null,
                'etablissement' => null,
                'centreExamen' => null,
                'mention' => null,
                'anomalieVisuelleDetectee' => false,
                'indicesAnomalieVisuelle' => [],
                'niveauConfianceVisuelle' => null,
            ];
        }

        return $donnees;
    }

    // ------------------------------------------------------------
    // Appelle l'API Gemini avec la clé principale. Si la réponse
    // indique un quota dépassé (429), retente automatiquement avec
    // la clé secondaire (projet Google Cloud séparé, donc quota
    // indépendant). Si les deux échouent, lève une exception.
    // ------------------------------------------------------------
    protected function appellerGeminiAvecAlternance(string $prompt, string $imageData, string $mimeType)
    {
        $clePrincipale = config('services.gemini.key');
        $cleSecondaire = config('services.gemini.key_secours');

        $reponse = $this->appellerGemini($clePrincipale, $prompt, $imageData, $mimeType);

        if ($reponse->status() === 429 && $cleSecondaire) {
            \Log::warning('Quota Gemini (clé principale) dépassé, bascule sur la clé de secours.');
            $reponse = $this->appellerGemini($cleSecondaire, $prompt, $imageData, $mimeType);
        }

        if (!$reponse->successful()) {
            throw new \Exception('Erreur API Gemini : ' . $reponse->body());
        }

        return $reponse;
    }

    protected function appellerGemini(string $cle, string $prompt, string $imageData, string $mimeType)
    {
        return Http::timeout(120)->post(
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