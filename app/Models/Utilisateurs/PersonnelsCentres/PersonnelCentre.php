<?php
namespace App\Models\Utilisateurs\PersonnelsCentres;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PersonnelCentre extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'personnels_centres';

    protected $fillable = [
        'user_id',
        'matricule',
        'cin',
        'date_cin',
        'lieu_cin_fr',
        'lieu_cin_ar',
        'etat_civil_fr',
        'etat_civil_ar',
        'date_fin_service',
        'cause_inactivite_fr',
        'cause_inactivite_ar',
        'observation_fr',
        'observation_ar',
    ];

    protected $casts = [
        'date_cin' => 'date',
        'date_fin_service' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnly(['deleted_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function statuts()
    {
        return $this->hasMany(\App\Models\Utilisateurs\PersonnelsCentres\DonneesPersonnels\StatutPersonnel::class, 'personnel_centre_id');
    }

    public function grades()
    {
        return $this->hasMany(\App\Models\Utilisateurs\PersonnelsCentres\DonneesPersonnels\GradePersonnel::class, 'personnel_centre_id');
    }

    public function diplomes()
    {
        return $this->hasMany(\App\Models\Utilisateurs\PersonnelsCentres\DonneesPersonnels\DiplomePersonnel::class, 'personnel_centre_id');
    }

    public function documents()
    {
        return $this->hasMany(\App\Models\Utilisateurs\PersonnelsCentres\DonneesPersonnels\DocPersonnel::class, 'personnel_centre_id');
    }

    public function contrats()
    {
        return $this->hasMany(\App\Models\Utilisateurs\PersonnelsCentres\DonneesPersonnels\ContratPersonnel::class, 'personnel_centre_id');
    }

    public function regimes()
    {
        return $this->hasMany(\App\Models\Utilisateurs\PersonnelsCentres\DonneesPersonnels\RegimePersonnel::class, 'personnel_centre_id');
    }

    public function formateurSpecialites()
    {
        return $this->hasMany(\App\Models\Utilisateurs\FormateursCentres\FormateurSpecialite::class, 'personnel_centre_id');
    }

    public function chefFiliere()
    {
        return $this->hasOne(\App\Models\Utilisateurs\PersonnelsCentres\ChefFiliere::class, 'personnel_centre_id');
    }
}
