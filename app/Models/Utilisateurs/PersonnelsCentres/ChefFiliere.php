<?php
namespace App\Models\Utilisateurs\PersonnelsCentres;

use App\Models\Annees\AnneeFormation;
use App\Models\brillants\Specialites\Secteurs\Secteur;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ChefFiliere extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'chefs_filieres';

    protected $fillable = [
        'personnel_centre_id',
        'secteur_id',
        'annee_formation_id',
        'statut',
        'heures_administratives',
        'heures_enseignement',
        'observation',
    ];

    protected $casts = [
        'heures_administratives' => 'integer',
        'heures_enseignement' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnly(['deleted_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function personnelCentre()
    {
        return $this->belongsTo(PersonnelCentre::class, 'personnel_centre_id');
    }

    public function secteur()
    {
        return $this->belongsTo(Secteur::class, 'secteur_id');
    }

    public function anneeFormation()
    {
        return $this->belongsTo(AnneeFormation::class, 'annee_formation_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
