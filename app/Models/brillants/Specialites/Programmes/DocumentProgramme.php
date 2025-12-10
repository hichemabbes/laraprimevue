<?php

namespace App\Models\brillants\Specialites\Programmes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentProgramme extends Model
{
    use SoftDeletes;

    protected $table = 'documents_programmes';

    protected $fillable = [
        'programme_id',
        'titre',
        'type_document_fr',
        'type_document_ar',
        'fichier',
        'statut',
        'description',
        'observation',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programme_id');
    }
}
