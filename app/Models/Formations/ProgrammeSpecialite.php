<?php

namespace App\Models\Formations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProgrammeSpecialite extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'programmes_specialites';

    protected $fillable = [
        'specialite_id',
        'version',
        'date_debut',
        'date_fin',
        'description_fr',
        'description_ar',
        'observation_fr',
        'observation_ar',
        'statut',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    // Relations
    public function specialite()
    {
        return $this->belongsTo(\App\Models\Formations\Specialite::class, 'specialite_id');
    }

    public function modulesSpecialites()
    {
        return $this->hasMany(ModuleSpecialite::class, 'programme_specialite_id');
    }

    public function documentsProgrammesSpecialites()
    {
        return $this->hasMany(DocumentProgrammeSpecialite::class, 'programme_specialite_id');
    }
}
