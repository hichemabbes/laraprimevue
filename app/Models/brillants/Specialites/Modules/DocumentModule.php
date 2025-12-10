<?php

namespace App\Models\brillants\Specialites\Modules;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentModule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documents_modules';

    protected $fillable = [
        'module_id',
        'titre',
        'type_document_fr',
        'type_document_ar',
        'fichier',
        'statut',
        'description',
        'observation',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}
