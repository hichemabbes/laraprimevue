<?php
namespace App\Models\Utilisateurs\PersonnelsCentres\DonneesPersonnels;

use App\Models\User;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContratPersonnel extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contrats_personnels';

    protected $fillable = [
        'personnel_centre_id',
        'type_contrat_fr',
        'type_contrat_ar',
        'num_contrat',
        'num_decision',
        'date_decision',
        'date_debut',
        'date_fin',
        'statut',
        'heures_travail',
        'heures_enseignement_max',
        'date_resiliation',
        'motif_resiliation',
        'document_path',
        'description_fr',
        'description_ar',
        'observation_fr',
        'observation_ar',
        'updated_by',
    ];

    protected $casts = [
        'date_decision' => 'date',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_resiliation' => 'date',
        'heures_travail' => 'integer',
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

    public function personnelCentre()
    {
        return $this->belongsTo(PersonnelCentre::class, 'personnel_centre_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
