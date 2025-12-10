<?php
namespace App\Models\Utilisateurs\PersonnelsCentres\DonneesPersonnels;

use App\Models\User;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class GradePersonnel extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'grades_personnels';

    protected $fillable = [
        'personnel_centre_id',
        'filiere_fr',
        'filiere_ar',
        'grade_fr',
        'grade_ar',
        'poste_fr',
        'poste_ar',
        'categorie',
        'echelle',
        'echelon',
        'date_debut',
        'date_fin',
        'statut',
        'for_cons_fr',
        'for_cons_ar',
        'observation_fr',
        'observation_ar',
        'updated_by',
    ];

    protected $casts = [
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
