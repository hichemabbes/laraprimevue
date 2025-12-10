<?php
namespace App\Models\Utilisateurs\DirecteursCentres;

use App\Models\Centre\Centre;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DirecteurCentre extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'directeurs_centres';

    protected $fillable = [
        'user_id',
        'centre_id',
        'type_nomination_fr',
        'type_nomination_ar',
        'date_debut_nomination',
        'date_fin_nomination',
        'type_affectation_fr',
        'type_affectation_ar',
        'contrat_objectif',
        'statut',
        'observation_fr',
        'observation_ar',
    ];

    protected $casts = [
        'date_debut_nomination' => 'date',
        'date_fin_nomination' => 'date',
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

    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }
}
