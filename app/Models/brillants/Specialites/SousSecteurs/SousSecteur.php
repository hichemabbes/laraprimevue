<?php

namespace App\Models\brillants\Specialites\SousSecteurs;

use App\Models\brillants\Specialites\Secteurs\Secteur;
use App\Models\brillants\Specialites\Specialite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class SousSecteur extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'nom_fr',
        'nom_ar',
        'secteur_id',
        'statut',
        'date_creation',
        'date_annulation',
        'observation',
    ];

    protected $casts = [
        'date_creation' => 'date',
        'date_annulation' => 'date',
        'statut' => 'string',
    ];

    /**
     * Relationship with secteur.
     */
    public function secteur()
    {
        return $this->belongsTo(Secteur::class);
    }

    /**
     * Relationship with specialites.
     */
    public function specialites()
    {
        return $this->hasMany(Specialite::class);
    }

    /**
     * Boot method to add model validation.
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validate unique code only for creation or if code has changed
            if ($model->isDirty('code') || $model->getKey() === null) {
                $exists = self::where('code', $model->code)
                    ->whereNull('deleted_at')
                    ->where('id', '!=', $model->id)
                    ->whereHas('secteur', function ($query) use ($model) {
                        $secteur = Secteur::find($model->secteur_id);
                        if ($secteur) {
                            $query->where('standardisation_ar', $secteur->standardisation_ar);
                        }
                    })
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'code' => "Le code '{$model->code}' existe déjà pour un sous-secteur actif de la même standardisation.",
                    ]);
                }
            }

            // Validate statut
            $statutValues = ['Actif', 'Inactif'];
            if ($model->statut && ! in_array($model->statut, $statutValues)) {
                throw ValidationException::withMessages([
                    'statut' => 'Le statut doit être l\'un des suivants : '.implode(', ', $statutValues),
                ]);
            }
        });
    }
}
