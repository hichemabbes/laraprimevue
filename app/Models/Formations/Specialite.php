<?php

namespace App\Models\Formations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Specialite extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'specialites';

    protected $fillable = [
        'code', 'nom_fr', 'nom_ar', 'niveau_fr', 'niveau_ar',
        'diplome_fr', 'diplome_ar', 'duree_formation',
        'heures_et', 'heures_eg', 'heures_total',
        'est_homologue', 'reference_homologation',
        'description_fr', 'description_ar',
        'criteres_admission_fr', 'criteres_admission_ar',
        'statut', 'ordre'
    ];

    protected $casts = [
        'duree_formation' => 'decimal:1',
        'heures_et' => 'integer',
        'heures_eg' => 'integer',
        'heures_total' => 'integer',
        'est_homologue' => 'boolean',
        'criteres_admission_fr' => 'array',
        'criteres_admission_ar' => 'array',
        'ordre' => 'integer',
    ];

    public function formations()
    {
        return $this->hasMany(Formation::class, 'specialite_id');
    }

    public function programmesSpecialites()
    {
        return $this->hasMany(ProgrammeSpecialite::class, 'specialite_id');
    }
}
