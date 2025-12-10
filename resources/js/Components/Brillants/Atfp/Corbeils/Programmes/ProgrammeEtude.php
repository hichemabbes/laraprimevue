<?php

namespace Atfp\Corbeils\Programmes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgrammeEtude extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'specialite_id',
        'programme_id',
        'memoire_mg_id',
        'version',
        'date_debut',
        'date_fin',
        'description',
        'observation',
        'statut',
    ];

    public function specialite()
    {
        return $this->belongsTo(\App\Models\brillants\Specialites\Specialite::class, 'specialite_id');
    }

    public function programme()
    {
        return $this->belongsTo(\App\Models\brillants\Specialites\Programmes\Programme::class, 'programme_id');
    }

    public function memoire()
    {
        return $this->belongsTo(\Atfp\Corbeils\Programmes\MemoireMg::class, 'memoire_mg_id');
    }
}
