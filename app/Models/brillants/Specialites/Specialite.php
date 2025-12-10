<?php

namespace App\Models\brillants\Specialites;

use App\Models\brillants\Specialites\Programmes\Programme;
use App\Models\brillants\Specialites\Secteurs\Secteur;
use App\Models\brillants\Specialites\SousSecteurs\SousSecteur;
use App\Models\Centre\Centre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Specialite extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'specialites';

    protected $fillable = [
        'secteur_id',
        'sous_secteur_id',
        'code',
        'nom_ar',
        'nom_fr',
        'standardisation_ar',
        'standardisation_fr',
        'diplome_fr',
        'diplome_ar',
        'statut',
        'date_creation',
        'date_annulation',
        'observation',
    ];

    protected $casts = [
        'date_creation' => 'date',
        'date_annulation' => 'date',
    ];

    public function infos_specialites()
    {
        return $this->hasMany(InfoSpecialite::class, 'specialite_id');
    }

    public function centres()
    {
        return $this->belongsToMany(Centre::class, 'specialites_centres')
            ->withPivot('statut', 'observation', 'date_debut', 'date_fin')
            ->withTimestamps()
            ->whereNull('specialites_centres.deleted_at'); // Ajout pour cohérence avec soft deletes
    }

    public function programmes()
    {
        return $this->hasMany(Programme::class, 'specialite_id');
    }

    public function secteur()
    {
        return $this->belongsTo(Secteur::class);
    }

    public function sousSecteur()
    {
        return $this->belongsTo(SousSecteur::class, 'sous_secteur_id');
    }

    public function centresParAnnee()
    {
        return $this->belongsToMany(Centre::class, 'centre_specialite_annee')
            ->withPivot('annee_formation_id', 'statut', 'date_debut', 'date_fin', 'observation')
            ->withTimestamps();
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $exists = self::where('code', $model->code)
                ->whereNull('deleted_at')
                ->where('id', '!=', $model->id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'code' => "Le code '{$model->code}' existe déjà pour une spécialité active.",
                ]);
            }
        });
    }
}
