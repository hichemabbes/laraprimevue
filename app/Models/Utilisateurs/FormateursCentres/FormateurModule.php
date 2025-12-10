<?php
namespace App\Models\Utilisateurs\FormateursCentres;

use App\Models\brillants\Specialites\Modules\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FormateurModule extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'formateurs_modules';

    protected $fillable = [
        'formateur_specialite_id',
        'module_id',
        'statut',
        'date_debut',
        'date_fin',
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

    public function formateurSpecialite()
    {
        return $this->belongsTo(FormateurSpecialite::class, 'formateur_specialite_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
