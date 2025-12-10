<?php
namespace App\Models\Utilisateurs\PersonnelsCentres\DonneesPersonnels;

use App\Models\User;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DocPersonnel extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'docs_personnels';

    protected $fillable = [
        'personnel_centre_id',
        'type_doc_fr',
        'type_doc_ar',
        'document_path',
        'date_depot',
        'validite_doc_fr',
        'validite_doc_ar',
        'type_depot_fr',
        'type_depot_ar',
        'description_fr',
        'description_ar',
        'observation_fr',
        'observation_ar',
        'statut',
        'updated_by',
    ];

    protected $casts = [
        'date_depot' => 'date',
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
