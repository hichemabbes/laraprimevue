<?php

namespace App\Models\Formations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModuleSpecialite extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'modules_specialites';

    protected $fillable = [
        'programme_specialite_id',
        'code',
        'titre_module_fr',
        'titre_module_ar',
        'type_module_fr',
        'type_module_ar',
        'heures_theoriques',
        'heures_pratiques',
        'heures_evaluation',
        'description_fr',
        'description_ar',
        'observation_fr',
        'observation_ar',
        'statut',
    ];

    protected $casts = [
        'heures_theoriques' => 'integer',
        'heures_pratiques'  => 'integer',
        'heures_evaluation' => 'integer',
    ];

    public function programmeSpecialite()
    {
        return $this->belongsTo(ProgrammeSpecialite::class, 'programme_specialite_id');
    }

    public function documentsModulesSpecialites()
    {
        return $this->hasMany(DocumentModuleSpecialite::class, 'module_specialite_id');
    }
}
