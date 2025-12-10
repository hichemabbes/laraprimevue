<?php

namespace App\Models\Annees;

use App\Models\brillants\Specialites\InfoSpecialite;
use App\Models\brillants\Specialites\Specialite;
use App\Models\Centre\CentreSpecialite;
use App\Models\Centre\Personnels\RegimePersonnel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class AnneeFormation extends Model
{
    use SoftDeletes;

    protected $table = 'annees_formations';

    protected $fillable = [
        'intitule',
        'date_debut',
        'date_fin',
        'statut',
        'observation',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['repos_formations', 'formation_weeks', 'vacation_weeks'];

    const STATUTS = ['Archivée', 'De base', 'En cours', 'Prochaine'];

    public function reposFormations()
    {
        return $this->hasMany(ReposFormation::class, 'annee_formation_id');
    }

    public function getReposFormationsAttribute()
    {
        return $this->reposFormations()->get();
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $exists = self::where('intitule', $model->intitule)
                ->whereNull('deleted_at')
                ->where('id', '!=', $model->id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'intitule' => "L'intitulé '{$model->intitule}' existe déjà.",
                ]);
            }

            $model->updateStatuts();
            $model->ensureUniqueEnCours();
        });
    }

    public function updateStatuts()
    {
        $today = Carbon::today();
        $dateDebut = $this->date_debut ? Carbon::parse($this->date_debut) : null;
        $dateFin = $this->date_fin ? Carbon::parse($this->date_fin) : null;

        if (! $dateDebut || ! $dateFin) {
            $this->statut = 'Archivée';

            return;
        }

        $currentYear = $today->year;
        if ($today->month < 9) {
            $currentYear--;
        }
        $baseYearStart = Carbon::create($currentYear - 1, 9, 1);
        $baseYearEnd = Carbon::create($currentYear, 8, 31);

        if ($today->between($dateDebut, $dateFin)) {
            $this->statut = 'En cours';
        } elseif ($dateFin->lt($today)) {
            if ($dateDebut->between($baseYearStart, $baseYearEnd) && $dateFin->between($baseYearStart, $baseYearEnd)) {
                $this->statut = 'De base';
            } else {
                $this->statut = 'Archivée';
            }
        } else {
            $this->statut = 'Prochaine';
        }
    }

    public function ensureUniqueEnCours()
    {
        if ($this->statut === 'En cours') {
            $existing = self::where('id', '!=', $this->id)
                ->where('statut', 'En cours')
                ->whereNull('deleted_at')
                ->exists();

            if ($existing) {
                throw ValidationException::withMessages([
                    'date_debut' => "Une autre année est déjà 'En cours'.",
                ]);
            }
        }
    }

    public function getTotalDays()
    {
        if (! $this->date_debut || ! $this->date_fin) {
            return 0;
        }

        return Carbon::parse($this->date_debut)->diffInDays(Carbon::parse($this->date_fin)) + 1;
    }

    public function getReposDays()
    {
        $days = 0;
        $anneeDebut = $this->date_debut ? Carbon::parse($this->date_debut) : null;
        $anneeFin = $this->date_fin ? Carbon::parse($this->date_fin) : null;

        if (! $anneeDebut || ! $anneeFin) {
            \Log::info("Aucune date de début ou de fin définie pour l'année {$this->id}, repos days = 0");

            return 0;
        }

        foreach ($this->reposFormations as $periode) {
            if ($periode->statut === 'annule') {
                \Log::info("Période {$periode->id} ignorée car annulée");

                continue;
            }

            $periodeDebut = $periode->date_debut ? Carbon::parse($periode->date_debut) : null;
            $periodeFin = $periode->date_fin ? Carbon::parse($periode->date_fin) : $periodeDebut;

            if (! $periodeDebut) {
                \Log::info("Période {$periode->id} ignorée car pas de date de début");

                continue;
            }

            // Ajuster les dates pour qu'elles soient dans l'intervalle de l'année
            $start = $periodeDebut->greaterThanOrEqualTo($anneeDebut) ? $periodeDebut : $anneeDebut;
            $end = $periodeFin && $periodeFin->lessThanOrEqualTo($anneeFin) ? $periodeFin : ($periodeFin ? $anneeFin : $start);

            // Vérifier si la période est valide et incluse dans l'année
            if ($end && $start->lessThanOrEqualTo($end) && $end->greaterThanOrEqualTo($anneeDebut) && $start->lessThanOrEqualTo($anneeFin)) {
                $periodeDays = $start->diffInDays($end) + 1;
                $days += $periodeDays;
                \Log::info("Période {$periode->id}: {$periodeDays} jours ajoutés (du {$start->toDateString()} au {$end->toDateString()})");
            } elseif (! $periodeFin && $start->equalTo($end) && $start->greaterThanOrEqualTo($anneeDebut) && $start->lessThanOrEqualTo($anneeFin)) {
                $days += 1;
                \Log::info("Période {$periode->id}: 1 jour ajouté (le {$start->toDateString()})");
            } else {
                \Log::info("Période {$periode->id} ignorée car hors période de l'année ({$anneeDebut->toDateString()} à {$anneeFin->toDateString()})");
            }
        }

        \Log::info("Repos days calculés pour l'année {$this->id}: {$days}");

        return $days;
    }

    public function getVacationWeeksAttribute()
    {
        $reposDays = $this->getReposDays();
        $weeks = floor($reposDays / 7);
        \Log::info("Vacation weeks calculées pour l'année {$this->id}: {$weeks}");

        return $weeks;
    }

    public function getFormationWeeksAttribute()
    {
        $totalDays = $this->getTotalDays();
        $reposDays = $this->getReposDays();
        $formationDays = max(0, $totalDays - $reposDays);
        $weeks = floor($formationDays / 7);
        \Log::info("Formation weeks calculées pour l'année {$this->id}: {$weeks}");

        return $weeks;
    }

    public function centresSpecialites()
    {
        return $this->hasManyThrough(
            Specialite::class,
            CentreSpecialite::class,
            'annee_formation_id',
            'id',
            'id',
            'specialite_id'
        );
    }

    public function infosSpecialites()
    {
        return $this->hasMany(InfoSpecialite::class, 'annee_formation_id');
    }
    public function regimes()
    {
        return $this->hasMany(RegimePersonnel::class);
    }
}
