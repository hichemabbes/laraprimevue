<?php
namespace App\Models\Utilisateurs\PersonnelsCentres\DonneesPersonnels;

use App\Models\Annees\AnneeAdministrative;
use App\Models\Annees\AnneeFormation;
use App\Models\User;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RegimePersonnel extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'regimes_personnels';

    protected $fillable = [
        'annee_formation_id',
        'annee_administrative_id',
        'personnel_centre_id',
        'regime_horaire',
        'rabattement',
        'heures_enseignement_max',
        'observation_fr',
        'observation_ar',
        'statut',
        'updated_by',
    ];

    protected $casts = [
        'regime_horaire' => 'integer',
        'rabattement' => 'integer',
        'heures_enseignement_max' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnly(['deleted_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function anneeFormation()
    {
        return $this->belongsTo(AnneeFormation::class, 'annee_formation_id');
    }

    public function anneeAdministrative()
    {
        return $this->belongsTo(AnneeAdministrative::class, 'annee_administrative_id');
    }

    public function personnelCentre()
    {
        return $this->belongsTo(PersonnelCentre::class, 'personnel_centre_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
