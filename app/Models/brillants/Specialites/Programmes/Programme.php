<?php

namespace App\Models\brillants\Specialites\Programmes;

use App\Models\brillants\Specialites\Modules\Module;
use App\Models\brillants\Specialites\Specialite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Programme extends Model
{
    use SoftDeletes;

    protected $table = 'programmes_etudes';

    protected $fillable = [
        'specialite_id',
        'version',
        'date_debut',
        'date_fin',
        'description',
        'observation',
        'statut',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function specialite()
    {
        return $this->belongsTo(Specialite::class, 'specialite_id');
    }

    public function modules()
    {
        return $this->hasMany(Module::class, 'programme_id');
    }

    public function documents()
    {
        return $this->hasMany(DocumentProgramme::class, 'programme_id');
    }
}
