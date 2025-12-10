<?php

namespace App\Models\brillants\Specialites;

use App\Models\Annees\AnneeFormation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InfoSpecialite extends Model
{
    use SoftDeletes;

    protected $table = 'infos_specialites';

    protected $fillable = [
        'specialite_id',
        'annee_formation_id',
        'homologation_fr',
        'homologation_ar',
        'heures_et',
        'heures_eg',
        'duree_formation',
        'statut',
        'observation',
    ];

    protected $appends = ['heures_totales'];

    public function specialite()
    {
        return $this->belongsTo(Specialite::class, 'specialite_id');
    }

    public function annee()
    {
        return $this->belongsTo(AnneeFormation::class, 'annee_formation_id');
    }

    public function getHeuresTotalesAttribute()
    {
        return ($this->heures_et ?? 0) + ($this->heures_eg ?? 0);
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $homologationMapping = [
                'Homologuée' => 'منظر',
                'Non Homologuée' => 'غير منظر',
            ];

            if ($model->homologation_fr && isset($homologationMapping[$model->homologation_fr])) {
                $model->homologation_ar = $homologationMapping[$model->homologation_fr];
            }

            $rules = [
                'specialite_id' => 'required|exists:specialites,id',
                'annee_formation_id' => 'required|exists:annees_formations,id',
                'homologation_fr' => 'nullable|in:Homologuée,Non Homologuée',
                'homologation_ar' => 'nullable|in:منظر,غير منظر',
                'heures_et' => 'nullable|integer|min:0',
                'heures_eg' => 'nullable|integer|min:0',
                'duree_formation' => 'nullable|numeric|min:0|max:9.9|regex:/^\d+(\.\d{1})?$/',
                'statut' => 'nullable|in:Active,Non Active,Annulée,Remplacée',
                'observation' => 'nullable|string',
            ];

            $validator = Validator::make($model->getAttributes(), $rules);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        });
    }
}
