<?php

namespace App\Models\brillants\Specialites\Modules;

use App\Models\brillants\Specialites\Programmes\Programme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'modules';

    protected $fillable = [
        'programme_id',
        'code',
        'titre_module',
        'type_module_fr', // Ajouté pour correspondre à la migration
        'type_module_ar', // Ajouté pour correspondre à la migration
        'heures_theoriques',
        'heures_pratiques',
        'heures_evaluation',
        'description',
        'observation',
        'statut',
    ];

    /**
     * Relation avec le modèle Programme
     */
    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programme_id');
    }

    /**
     * Relation avec le modèle SessionFormationController
     */
    public function documents()
    {
        return $this->hasMany(DocumentModule::class, 'module_id');
    }
}
