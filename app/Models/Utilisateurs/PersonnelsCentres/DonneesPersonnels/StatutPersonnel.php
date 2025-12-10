<?php
namespace App\Models\Utilisateurs\PersonnelsCentres\DonneesPersonnels;

use App\Models\User;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StatutPersonnel extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'statuts_personnels';

    protected $fillable = [
        'personnel_centre_id',
        'qualite_fr',
        'qualite_ar',
        'date_recrutement',
        'etat_personnel_fr',
        'etat_personnel_ar',
        'date_debut',
        'date_fin',
        'etablissement_origine_fr',
        'etablissement_origine_ar',
        'mission_fr',
        'mission_ar',
        'statut',
        'observation_fr',
        'observation_ar',
        'updated_by',
    ];

    protected $casts = [
        'date_recrutement' => 'date',
        'date_debut' => 'date',
        'date_fin' => 'date',
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
