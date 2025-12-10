<?php

namespace App\Models\brillants\Specialites;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentSpecialite extends Model
{
    use SoftDeletes;

    protected $table = 'documents_specialites';

    protected $fillable = [
        'specialite_id',
        'version',
        'type_doc_fr',
        'type_doc_ar',
        'fichier',
        'date_debut',
        'date_fin',
        'statut_fr',
        'statut_ar',
        'observation_fr',
        'observation_ar',
    ];

    protected $dates = ['deleted_at', 'date_debut', 'date_fin'];

    /**
     * Relation avec la spécialité.
     */
    public function specialite()
    {
        return $this->belongsTo(Specialite::class, 'specialite_id', 'id');
    }

    /**
     * Supprimer tout scope global qui pourrait filtrer les documents.
     */
    public static function boot()
    {
        parent::boot();

        // Assurez-vous qu'aucun scope global ne filtre les documents
        static::addGlobalScope('withoutSoftDeletes', function ($builder) {
            $builder->whereNull('deleted_at');
        });
    }
}
