<?php

use App\Models\BaseReference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

// ----------------------------------------------------------
// Même logique que dans ComparaisonServiceTest : crée une
// référence + sa règle de vérification associée.
// ----------------------------------------------------------
function creerReferenceEtRegleDiplome(array $champsObligatoires): BaseReference
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

test('un diplôme identique à la référence est déclaré authentique via l\'API', function () {
    creerReferenceEtRegleDiplome(['numeroDiplome', 'nomTitulaire', 'mention']);

    $reponse = $this->postJson('/api/diplomes/verifier-test', [
        'numeroDiplome' => '043287',
        'typeDiplome' => 'Baccalauréat',
        'institution' => 'OBC',
        'nomTitulaire' => 'MENGUE ASSINA MADELEINE DORINE',
        'mention' => 'Passable',
    ]);

    $reponse->assertStatus(200);
    $reponse->assertJsonPath('resultat.statut', 'authentique');
    $reponse->assertJsonPath('resultat.scoreFinal', 100);
});

test('un diplôme avec un champ different est déclaré suspect via l\'API', function () {
    creerReferenceEtRegleDiplome(['numeroDiplome', 'nomTitulaire', 'mention']);

    $reponse = $this->postJson('/api/diplomes/verifier-test', [
        'numeroDiplome' => '043287',
        'typeDiplome' => 'Baccalauréat',
        'institution' => 'OBC',
        'nomTitulaire' => 'MENGUE ASSINA MADELEINE DORINE',
        'mention' => 'Bien', // différent de "Passable"
    ]);

    $reponse->assertStatus(200);
    $reponse->assertJsonPath('resultat.statut', 'suspect');
});

test('un diplôme introuvable dans la base de référence est déclaré ambigu via l\'API', function () {
    $reponse = $this->postJson('/api/diplomes/verifier-test', [
        'numeroDiplome' => '999999',
        'typeDiplome' => 'Baccalauréat',
        'institution' => 'OBC',
    ]);

    $reponse->assertStatus(200);
    $reponse->assertJsonPath('resultat.statut', 'ambigu');
    $reponse->assertJsonPath('resultat.reference_id', null);
});

test('les champs obligatoires numeroDiplome, typeDiplome et institution sont requis', function () {
    $reponse = $this->postJson('/api/diplomes/verifier-test', [
        'nomTitulaire' => 'MENGUE ASSINA MADELEINE DORINE',
    ]);

    // 422 = erreur de validation Laravel (champs required manquants)
    $reponse->assertStatus(422);
    $reponse->assertJsonValidationErrors(['numeroDiplome', 'typeDiplome', 'institution']);
});
