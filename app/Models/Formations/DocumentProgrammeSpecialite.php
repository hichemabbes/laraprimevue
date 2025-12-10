<?php

namespace App\Models\Formations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentProgrammeSpecialite extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documents_programmes_specialites';

    protected $fillable = [
        'programme_specialite_id',
        'titre_fr',
        'titre_ar',
        'type_document_fr',
        'type_document_ar',
        'fichier',
        'statut',
        'description_fr',
        'description_ar',
        'observation_fr',
        'observation_ar',
    ];

    public function programmeSpecialite()
    {
        return $this->belongsTo(ProgrammeSpecialite::class, 'programme_specialite_id');
    }
}
