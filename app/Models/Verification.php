<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    protected $table = 'verifications';

    protected $fillable = [
        'dateVérification',
        'statut',
        'resultat',
        'texteOcrBrut',
        'donneesAnalyseIa',
        'scoreFinal',
        'diplome_id',
        'base_reference_id',
    ];

    protected $casts = [
        'dateVérification'  => 'date',
        'donneesAnalyseIa'  => 'array',
    ];

    public function diplome()
    {
        return $this->belongsTo(Diplome::class, 'diplome_id');
    }

    public function baseReference()
    {
        return $this->belongsTo(BaseReference::class, 'base_reference_id');
    }
}
