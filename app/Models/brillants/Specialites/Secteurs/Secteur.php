<?php

namespace App\Models\brillants\Specialites\Secteurs;

use App\Models\brillants\Specialites\SousSecteurs\SousSecteur;
use App\Models\brillants\Specialites\Specialite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Secteur extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'nom_fr',
        'nom_ar',
        'standardisation_fr',
        'standardisation_ar',
        'statut',
        'date_creation',
        'date_annulation',
        'observation',
    ];

    protected $casts = [
        'date_creation' => 'date',
        'date_annulation' => 'date',
        'standardisation_ar' => 'string',
        'statut' => 'string',
    ];

    // Relation avec les spécialités
    public function specialites()
    {
        return $this->hasMany(Specialite::class);
    }

    // Relation avec les sous-secteurs
    public function sousSecteurs()
    {
        return $this->hasMany(SousSecteur::class);
    }

    // Méthode boot pour ajouter la validation
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Skip validation if the code and standardisation_ar are unchanged for the same record
            if ($model->exists && $model->code === $model->getOriginal('code') && $model->standardisation_ar === $model->getOriginal('standardisation_ar')) {
                return;
            }

            // Vérifier si un secteur actif avec le même code et standardisation_ar existe déjà
            $exists = self::where('code', $model->code)
                ->where('standardisation_ar', $model->standardisation_ar)
                ->whereNull('deleted_at')
                ->where('id', '!=', $model->id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'code' => "Le code '{$model->code}' existe déjà pour un secteur actif de standardisation '{$model->standardisation_ar}'.",
                ]);
            }
        });
    }
}
