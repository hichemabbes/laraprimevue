<?php

namespace Atfp\Corbeils\Programmes;

use Atfp\Corbeils\Programmes\MemoireMg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleGeneral extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_id',
        'code',
        'titre_module',
        'heures_theoriques',
        'heures_pratiques',
        'heures_evaluation',
        'manuels',
        'supports_cours',
        'exercices',
        'contenu_pdf',
        'description',
        'observation',
        'statut',
    ];

    protected $casts = [
        'manuels' => 'array',
        'supports_cours' => 'array',
        'exercices' => 'array',
    ];

    public function memoire()
    {
        return $this->belongsTo(MemoireMg::class, 'reference_id');
    }
}
