<?php

namespace Atfp\Corbeils\Programmes;

use Atfp\Corbeils\Programmes\ModuleGeneral;
use Atfp\Corbeils\Programmes\ProgrammeEtude;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemoireMg extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'diplome_fr_id',
        'reference',
        'numero_memoire',
        'date_memoire',
        'date_debut',
        'date_fin',
        'contenu_pdf',
        'description',
        'observation',
        'statut',
    ];

    public function diplome()
    {
        return $this->belongsTo(\App\Models\OptionListe::class, 'diplome_fr_id');
    }

    public function modulesGeneraux()
    {
        return $this->hasMany(ModuleGeneral::class, 'reference_id');
    }

    public function programmesEtudes()
    {
        return $this->hasMany(ProgrammeEtude::class, 'memoire_mg_id');
    }
}
