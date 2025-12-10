<?php
namespace App\Models\Utilisateurs\FormateursCentres;

use App\Models\brillants\Specialites\Specialite;
use App\Models\User;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FormateurSpecialite extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'formateurs_specialites';

    protected $fillable = [
        'personnel_centre_id',
        'specialite_id',
        'code_secteur',
        'code_sous_secteur',
        'date_debut',
        'date_fin',
        'statut',
        'correspondance_specialite_fr',
        'correspondance_specialite_ar',
        'observation',
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

    public function specialite()
    {
        return $this->belongsTo(Specialite::class, 'specialite_id');
    }

    public function modules()
    {
        return $this->hasMany(FormateurModule::class, 'formateur_specialite_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
