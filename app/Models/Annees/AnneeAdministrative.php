<?php

namespace App\Models\Annees;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class AnneeAdministrative extends Model
{
    use SoftDeletes;

    protected $table = 'annees_administratives';

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

    protected $appends = ['repos_administratifs', 'formation_weeks', 'vacation_weeks'];

    const STATUTS = ['Archivée', 'De Base', 'En cours', 'Prochaine'];

    public function reposAdministratifs()
    {
        return $this->hasMany(ReposAdministratif::class, 'annee_administrative_id');
    }

    public function getReposAdministratifsAttribute()
    {
        return $this->reposAdministratifs()->get();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Automatisation des dates en fonction de l'intitulé
            if ($model->intitule && preg_match('/^\d{4}$/', $model->intitule)) {
                $year = (int) $model->intitule;
                $model->date_debut = Carbon::create($year, 1, 1);
                $model->date_fin = Carbon::create($year, 12, 31);
            }
        });

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
        $currentYear = $today->year;
        $dateDebut = $this->date_debut ? Carbon::parse($this->date_debut) : null;
        $dateFin = $this->date_fin ? Carbon::parse($this->date_fin) : null;

        if (! $dateDebut || ! $dateFin) {
            $this->statut = 'Archivée';
            \Log::info("Année {$this->intitule}: Statut 'Archivée' (dates manquantes)");

            return;
        }

        $anneeYear = $dateDebut->year;

        if ($anneeYear == $currentYear) {
            $this->statut = 'En cours';
            \Log::info("Année {$this->intitule}: Statut 'En cours' (année courante)");
        } elseif ($anneeYear == $currentYear - 1) {
            $this->statut = 'De Base';
            \Log::info("Année {$this->intitule}: Statut 'De Base' (année précédente)");
        } elseif ($anneeYear < $currentYear - 1) {
            $this->statut = 'Archivée';
            \Log::info("Année {$this->intitule}: Statut 'Archivée' (année antérieure à l'année précédente)");
        } else {
            $this->statut = 'Prochaine';
            \Log::info("Année {$this->intitule}: Statut 'Prochaine' (année future)");
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
                    'date_debut' => "Une autre année administrative est déjà 'En cours'.",
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

        foreach ($this->reposAdministratifs as $periode) {
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
}
