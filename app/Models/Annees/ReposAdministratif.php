<?php

namespace App\Models\Annees;

use App\Models\Liste;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReposAdministratif extends Model
{
    use SoftDeletes;

    protected $table = 'repos_administratifs';

    protected $fillable = [
        'annee_administrative_id',
        'type_repos_administratif_fr_id',
        'type_repos_administratif_ar',
        'date_debut',
        'nombre_jour',
        'date_fin',
        'description',
        'statut',
        'ordre',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function anneeAdministrative()
    {
        return $this->belongsTo(AnneeAdministrative::class, 'annee_administrative_id');
    }

    public function optionReposAdministratifFr()
    {
        return $this->belongsTo(Liste::class, 'type_repos_administratif_fr_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $dateDebut = Carbon::parse($model->date_debut);
            $dateFin = $model->date_fin ? Carbon::parse($model->date_fin) : null;
            $model->nombre_jour = $dateFin ? $dateDebut->diffInDays($dateFin) + 1 : 1;

            $today = Carbon::today();
            $model->statut = 'Inactif';
            if ($dateFin) {
                if ($today->between($dateDebut, $dateFin)) {
                    $model->statut = 'Actif';
                }
            } else {
                if ($today->equalTo($dateDebut)) {
                    $model->statut = 'Actif';
                }
            }
        });
    }
}
