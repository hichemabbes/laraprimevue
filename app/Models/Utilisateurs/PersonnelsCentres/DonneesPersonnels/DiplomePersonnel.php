<?php
namespace App\Models\Utilisateurs\PersonnelsCentres\DonneesPersonnels;

use App\Models\User;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DiplomePersonnel extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'diplomes_personnels';

    protected $fillable = [
        'personnel_centre_id',
        'niveau_etude_fr',
        'niveau_etude_ar',
        'diplome_fr',
        'diplome_ar',
        'date_optention',
        'specialite_diplome_fr',
        'specialite_diplome_ar',
        'code_niveau',
        'statut',
        'observation_fr',
        'observation_ar',
        'updated_by',
    ];

    protected $casts = [
        'date_optention' => 'date',
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
