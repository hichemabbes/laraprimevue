<?php
namespace App\Models;

use App\Models\Centre\Centre;
use App\Models\Utilisateurs\UserCentre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes, LogsActivity;

    protected $fillable = [
        'nom_fr',
        'prenom_fr',
        'nom_ar',
        'prenom_ar',
        'date_naissance',
        'lieu_naissance_fr',
        'lieu_naissance_ar',
        'nationalite_fr',
        'nationalite_ar',
        'genre_fr',
        'genre_ar',
        'statut',
        'adresse_fr',
        'adresse_ar',
        'gouvernorat_fr',
        'gouvernorat_ar',
        'delegation_fr',
        'delegation_ar',
        'telephone_1',
        'telephone_2',
        'observation_fr',
        'observation_ar',
        'photo',
        'ajouter_par',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'type_user_fr',
        'type_user_ar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_naissance' => 'date',
        'statut' => 'string',
        'ajouter_par' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept(['photo'])
            ->logOnly(['deleted_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $description = $eventName === 'created' ? 'Utilisateur créé' : 'Utilisateur mis à jour';
                if ($eventName === 'created' && $this->photo) {
                    $description .= ' (Photo ajoutée)';
                } elseif ($eventName === 'updated') {
                    $oldPhoto = $this->getOriginal('photo');
                    $newPhoto = $this->photo;
                    if ($newPhoto && !$oldPhoto) {
                        $description .= ' (Photo ajoutée)';
                    } elseif ($newPhoto && $oldPhoto && $newPhoto !== $oldPhoto) {
                        $description .= ' (Photo modifiée)';
                    } elseif (!$newPhoto && $oldPhoto) {
                        $description .= ' (Photo supprimée)';
                    }
                }
                return $description;
            });
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'ajouter_par');
    }

    public function centres()
    {
        return $this->belongsToMany(Centre::class, 'users_centres', 'user_id', 'centre_id')
            ->withPivot([
                'centre_statut_fr',
                'centre_statut_ar',
                'type_mouvement_fr',
                'type_mouvement_ar',
                'date_debut',
                'date_fin',
                'statut',
                'observation_fr',
                'observation_ar',
                'updated_by'
            ])
            ->withTimestamps();
    }

    public function userCentres()
    {
        return $this->hasMany(UserCentre::class, 'user_id');
    }

    public function getNameAttribute()
    {
        return trim($this->nom_fr . ' ' . $this->prenom_fr);
    }

    public function getNameArAttribute()
    {
        return trim($this->nom_ar . ' ' . $this->prenom_ar);
    }
}
