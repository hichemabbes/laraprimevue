<?php
namespace App\Models\Utilisateurs\PersonnelsDirectionCentrale;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PersonnelDC extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'personnels_direction_centrale';

    protected $fillable = [
        'user_id',
        'matricule',
        'cin',
        'date_cin',
        'lieu_cin_fr',
        'lieu_cin_ar',
        'etat_civil_fr',
        'etat_civil_ar',
        'qualite_fr',
        'qualite_ar',
        'date_recrutement',
        'etablissement_origine_fr',
        'etablissement_origine_ar',
        'mission_fr',
        'mission_ar',
        'date_fin_service',
        'cause_inactivite_fr',
        'cause_inactivite_ar',
        'observation_fr',
        'observation_ar',
    ];

    protected $casts = [
        'date_cin' => 'date',
        'date_recrutement' => 'date',
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
}
