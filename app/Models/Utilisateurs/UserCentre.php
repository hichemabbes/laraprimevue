<?php
namespace App\Models\Utilisateurs;

use App\Models\Centre\Centre;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class UserCentre extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'users_centres';

    protected $fillable = [
        'user_id',
        'centre_id',
        'centre_statut_fr',
        'centre_statut_ar',
        'type_mouvement_fr',
        'type_mouvement_ar',
        'date_debut',
        'date_fin',
        'statut',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
