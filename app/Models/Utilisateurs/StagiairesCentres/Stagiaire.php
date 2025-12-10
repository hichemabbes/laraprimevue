<?php
namespace App\Models\Utilisateurs\StagiairesCentres;

use App\Models\ATFP\Utilisateurs\StagiairesCentre\AffectationStagiaire;
use App\Models\User;
use App\Models\Utilisateurs\StagiairesCentres\DonneesStagiaires\DocStagiaire;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Stagiaire extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'stagiaires';

    protected $fillable = [
        'user_id',
        'num_extrait_naissance',
        'niveau_etude_fr',
        'niveau_etude_ar',
        'section_etude_fr',
        'section_etude_ar',
        'specialite_diplome_fr',
        'specialite_diplome_ar',
        'date_obtention_diplome',
        'etablissement_diplome_fr',
        'etablissement_diplome_ar',
        'statut_etablissement_fr',
        'statut_etablissement_ar',
        'num_inscription',
        'date_inscription',
        'dossier_complet',
        'nom_responsable_fr',
        'nom_responsable_ar',
        'prenom_responsable_fr',
        'prenom_responsable_ar',
        'role_responsable_fr',
        'role_responsable_ar',
        'cin_responsable',
        'lieu_cin_responsable_fr',
        'lieu_cin_responsable_ar',
        'date_cin_responsable',
        'nationalite_responsable_fr',
        'nationalite_responsable_ar',
        'profession_responsable_fr',
        'profession_responsable_ar',
        'adresse_responsable_fr',
        'adresse_responsable_ar',
        'telephone_responsable',
        'email_responsable',
        'statut',
        'observation_fr',
        'observation_ar',
        'updated_by',
    ];

    protected $casts = [
        'date_obtention_diplome' => 'date',
        'date_inscription' => 'date',
        'dossier_complet' => 'boolean',
        'date_cin_responsable' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function affectations()
    {
        return $this->hasMany(AffectationStagiaire::class, 'stagiaire_id');
    }

    public function documents()
    {
        return $this->hasMany(DocStagiaire::class, 'stagiaire_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnly(['deleted_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
