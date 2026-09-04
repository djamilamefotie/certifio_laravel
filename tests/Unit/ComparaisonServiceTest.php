<?php

use App\Models\BaseReference;
use App\Services\ComparaisonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function creerReferenceEtRegle(array $champsObligatoires): BaseReference
{
    $reference = BaseReference::create([
        'numeroDiplome' => '043287',
        'typeDiplome' => 'Baccalauréat',
        'institution' => 'OBC',
        'nomTitulaire' => 'MENGUE ASSINA MADELEINE DORINE',
        'dateNaissance' => '2003-03-12',
        'matricule' => '40228321',
        'mention' => 'Passable',
        'serieOuFiliere' => 'A4 : LETTRES-PHILOSOPHIE (LVII ESPAGNOL)',
        'dateObtention' => '2021-06-01',
    ]);

    DB::table('regles_verification')->insert([
        'institution' => 'OBC',
        'typeDiplome' => 'Baccalauréat',
        'champsObligatoires' => json_encode($champsObligatoires),
    ]);

    return $reference;
}

test('un diplôme identique à la référence est authentique avec un score de 100', function () {
    creerReferenceEtRegle(['numeroDiplome', 'nomTitulaire', 'mention']);

    $resultat = (new ComparaisonService())->comparer([
        'numeroDiplome' => '043287',
        'typeDiplome' => 'Baccalauréat',
        'institution' => 'OBC',
        'nomTitulaire' => 'MENGUE ASSINA MADELEINE DORINE',
        'mention' => 'Passable',
    ]);

    expect($resultat['statut'])->toBe('authentique');
    expect($resultat['scoreFinal'])->toBe(100.0);
});

test('un champ obligatoire différent rend le diplôme suspect', function () {
    creerReferenceEtRegle(['numeroDiplome', 'nomTitulaire', 'mention']);

    $resultat = (new ComparaisonService())->comparer([
        'numeroDiplome' => '043287',
        'typeDiplome' => 'Baccalauréat',
        'institution' => 'OBC',
        'nomTitulaire' => 'MENGUE ASSINA MADELEINE DORINE',
        'mention' => 'Bien', // différent de "Passable"
    ]);

    expect($resultat['statut'])->toBe('suspect');
    expect($resultat['resultat'])->toContain("Le champ 'mention' ne correspond pas");
});

test('la casse et les accents sont ignorés dans la comparaison', function () {
    creerReferenceEtRegle(['nomTitulaire']);

    $resultat = (new ComparaisonService())->comparer([
        'numeroDiplome' => '043287',
        'typeDiplome' => 'Baccalauréat',
        'institution' => 'OBC',
        'nomTitulaire' => 'mengue assina madeleine dorine', // minuscules, sans accents identiques
    ]);

    expect($resultat['statut'])->toBe('authentique');
});

test('un diplôme introuvable dans la base de référence est ambigu', function () {
    $resultat = (new ComparaisonService())->comparer([
        'numeroDiplome' => '999999',
        'typeDiplome' => 'Baccalauréat',
        'institution' => 'OBC',
    ]);

    expect($resultat['statut'])->toBe('ambigu');
    expect($resultat['reference_id'])->toBeNull();
});

test('des champs clés manquants rendent la vérification impossible', function () {
    $resultat = (new ComparaisonService())->comparer([
        'nomTitulaire' => 'MENGUE ASSINA MADELEINE DORINE',
    ]);

    expect($resultat['statut'])->toBe('ambigu');
    expect($resultat['scoreFinal'])->toBe(0);
});