<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffreAbonnement extends Model
{
    protected $table = 'offre_abonnements';

    protected $fillable = [
        'nom',
        'type',
        'montant',
        'devise',
        'duree_jours',
        'limite_verifications',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];
}