<?php

namespace App\Models\Formations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentModuleSpecialite extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documents_modules_specialites';

    protected $fillable = [
        'module_specialite_id',
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

    public function moduleSpecialite()
    {
        return $this->belongsTo(ModuleSpecialite::class, 'module_specialite_id');
    }
}
