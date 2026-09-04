<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BaseReference extends Model
{
    protected $table = 'base_references';
    protected $fillable = [
        'numeroDiplome',
        'typeDiplome',
        'institution',
        'nomTitulaire',
        'dateNaissance',
        'lieuNaissance',
        'mention',
        'serieOuFiliere',
        'dateObtention',
        'session',
        'dateDelivrance',
        'lieuDelivrance',
        'matricule',
        'etablissement',
        'centreExamen',
        'informationReference',
    ];
    public function verifications()
    {
        return $this->hasMany(Verification::class, 'base_reference_id');
    }
}