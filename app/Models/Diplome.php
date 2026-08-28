<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// ============================================================
// MODÈLE : Diplome
// ------------------------------------------------------------
// Correspond à la table "diplomes" et à la classe "Diplomes"
// du diagramme de classes.
// ============================================================
class Diplome extends Model
{
    // $fillable : liste des champs qu'on autorise à remplir
    // via Diplome::create([...]) — sécurité contre l'injection
    // de champs non prévus.
    protected $fillable = [
        'numeroDiplome',
        'typeDiplome',
        'nomTitulaire',
        'mention',
        'dateObtention',
        'etablissement',
        'fichier',
        'client_id',
        'institution_id',
    ];

    // Cast automatique : dateObtention sera manipulable comme
    // un vrai objet date en PHP (pas juste une chaîne de texte).
    protected $casts = [
        'dateObtention' => 'date',
    ];

    // ------------------------------------------------------------
    // Relations (correspondent aux flèches du diagramme de classes)
    // ------------------------------------------------------------

    // Le Client qui a soumis ce diplôme.
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // L'Institution qui a délivré ce diplôme (si connue).
    public function institution()
    {
        return $this->belongsTo(User::class, 'institution_id');
    }

    // Un diplôme "possède" plusieurs Vérifications (relation du
    // diagramme : Diplomes "1..1" --- possède ---> "0..*" Vérification).
    public function verifications()
    {
        return $this->hasMany(Verification::class, 'diplome_id');
    }
}