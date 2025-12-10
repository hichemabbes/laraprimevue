<?php

namespace App\Http\Controllers\Annees;

use App\Http\Controllers\Controller;
use App\Models\Annees\AnneeAdministrative;
use App\Models\Annees\ReposFormation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AnneeCalculController extends Controller
{
    public function index()
    {
        Log::info('Appel de AnneeCalculController::index', [
            'user_id' => auth()->id() ?? 'non authentifié',
        ]);

        try {
            $annees = AnneeAdministrative::all();
            Log::info('Annees récupérées', ['count' => $annees->count()]);

            $annees->each(function ($annee) {
                Log::debug('Traitement de l\'année', [
                    'id' => $annee->id,
                    'intitule' => $annee->intitule,
                    'date_debut' => $annee->date_debut,
                    'date_fin' => $annee->date_fin,
                ]);

                // Vérifier les dates
                if (! $annee->date_debut || ! $annee->date_fin) {
                    Log::warning('Dates manquantes pour l\'année', ['id' => $annee->id]);
                    $annee->date_debut = $annee->date_debut ?? now()->startOfYear()->toDateString();
                    $annee->date_fin = $annee->date_fin ?? now()->endOfYear()->toDateString();
                }

                $dateDebut = Carbon::parse($annee->date_debut);
                $dateFin = Carbon::parse($annee->date_fin);

                // Récupérer les repos formations inclus dans la période de l'année administrative
                $reposFormations = ReposFormation::where(function ($query) use ($dateDebut, $dateFin) {
                    $query->whereBetween('date_debut', [$dateDebut, $dateFin])
                        ->orWhereBetween('date_fin', [$dateDebut, $dateFin])
                        ->orWhere(function ($q) use ($dateDebut, $dateFin) {
                            $q->where('date_debut', '<=', $dateDebut)
                                ->where('date_fin', '>=', $dateFin);
                        });
                })
                    ->where('statut', '!=', 'annule')
                    ->whereNull('deleted_at')
                    ->get()
                    ->map(function ($repos) use ($dateDebut, $dateFin) {
                        $reposStart = Carbon::parse($repos->date_debut);
                        $reposEnd = $repos->date_fin ? Carbon::parse($repos->date_fin) : $reposStart;

                        // Ajuster les dates pour l'année administrative
                        $effectiveStart = $reposStart->max($dateDebut);
                        $effectiveEnd = $reposEnd->min($dateFin);

                        if ($effectiveStart <= $effectiveEnd) {
                            $jours = $effectiveStart->diffInDays($effectiveEnd) + 1;
                            Log::debug('Période de repos formation traitée', [
                                'id' => $repos->id,
                                'type_fr' => $repos->type_repos_formation_fr,
                                'type_ar' => $repos->type_repos_formation_ar,
                                'date_debut' => $effectiveStart->toDateString(),
                                'date_fin' => $effectiveEnd->toDateString(),
                                'nombre_jour' => $jours,
                                'statut' => $repos->statut,
                            ]);

                            return [
                                'id' => $repos->id,
                                'type_repos_formation_fr' => $repos->type_repos_formation_fr,
                                'type_repos_formation_ar' => $repos->type_repos_formation_ar,
                                'date_debut' => $effectiveStart->toDateString(),
                                'date_fin' => $effectiveEnd->toDateString(),
                                'nombre_jour' => $jours,
                                'statut' => $repos->statut,
                            ];
                        }
                        Log::debug('Période de repos formation ignorée', [
                            'id' => $repos->id,
                            'type_fr' => $repos->type_repos_formation_fr,
                            'date_debut' => $repos->date_debut,
                            'date_fin' => $repos->date_fin,
                        ]);

                        return null;
                    })
                    ->filter()
                    ->values()
                    ->toArray();

                Log::debug('Repos formations trouvés', [
                    'annee_id' => $annee->id,
                    'count' => count($reposFormations),
                    'repos_formations' => array_map(function ($repos) {
                        return [
                            'type_fr' => $repos['type_repos_formation_fr'],
                            'nombre_jour' => $repos['nombre_jour'],
                            'date_debut' => $repos['date_debut'],
                            'date_fin' => $repos['date_fin'],
                        ];
                    }, $reposFormations),
                ]);

                // Calculer le total des jours de repos à partir de repos_formations
                $totalVacationDays = array_sum(array_column($reposFormations, 'nombre_jour'));

                // Calculer les semaines de repos avec la nouvelle règle d'arrondi
                $weeks = $totalVacationDays / 7;
                $vacationWeeks = $weeks - floor($weeks) <= 0.5 ? floor($weeks) : ceil($weeks);

                // Calculer les semaines totales
                $totalWeeks = $dateDebut->diffInWeeks($dateFin) + 1;

                // Calculer les semaines de travail
                $formationWeeks = $totalWeeks - $vacationWeeks;

                Log::debug('Calcul des semaines', [
                    'annee_id' => $annee->id,
                    'total_weeks' => $totalWeeks,
                    'total_vacation_days' => $totalVacationDays,
                    'weeks_raw' => $weeks,
                    'vacation_weeks' => $vacationWeeks,
                    'formation_weeks' => $formationWeeks,
                ]);

                // Stocker les données dans des attributs temporaires pour éviter les accesseurs
                $annee->calculated_vacation_weeks = $vacationWeeks;
                $annee->calculated_formation_weeks = max(0, $formationWeeks);
                $annee->repos_formations = $reposFormations;

                // Forcer les valeurs dans la réponse JSON
                $annee->setAttribute('vacation_weeks', $vacationWeeks);
                $annee->setAttribute('formation_weeks', max(0, $formationWeeks));
            });

            // Transformer les années pour utiliser les attributs calculés
            $annees = $annees->map(function ($annee) {
                return [
                    'id' => $annee->id,
                    'intitule' => $annee->intitule,
                    'date_debut' => $annee->date_debut,
                    'date_fin' => $annee->date_fin,
                    'statut' => $annee->statut,
                    'vacation_weeks' => $annee->calculated_vacation_weeks,
                    'formation_weeks' => $annee->calculated_formation_weeks,
                    'repos_formations' => $annee->repos_formations,
                    'repos_administratifs' => $annee->repos_administratifs,
                    'created_at' => $annee->created_at,
                    'updated_at' => $annee->updated_at,
                    'deleted_at' => $annee->deleted_at,
                    'observation' => $annee->observation,
                ];
            });

            Log::info('AnneesCalcul complétées', ['count' => $annees->count()]);

            return response()->json($annees, 200);
        } catch (\Exception $e) {
            Log::error('Erreur dans AnneeCalculController::index:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['message' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }
}
