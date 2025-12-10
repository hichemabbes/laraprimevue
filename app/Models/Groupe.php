<?php

namespace App\Models;

use App\Models\brillants\Specialites\Programmes\Programme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Groupe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code_groupe',
        'nom_fr',
        'nom_ar',
        'programme_id',
        'tuteur_id',
    ];

    protected $dates = ['deleted_at'];

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programme_id');
    }

    public function tuteur()
    {
        return $this->belongsTo(Tuteur::class, 'tuteur_id');
    }
}
