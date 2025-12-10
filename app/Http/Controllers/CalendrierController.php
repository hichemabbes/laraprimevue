<?php

namespace App\Http\Controllers;

use App\Models\Annees\ReposFormation;
use App\Models\Calendrier;
use App\Models\Liste;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CalendrierController extends Controller
{
    public function indexCalendrier(Request $request)
    {
        $query = Calendrier::query();

        if ($request->has('start') && $request->has('end')) {
            $query->where('date_debut', '>=', $request->input('start'))
                ->where('date_debut', '<=', $request->input('end'));
        }

        $calendriers = $query->get()->map(function ($calendrier) {
            return $this->formatEvent($calendrier);
        });

        return response()->json($calendriers, 200);
    }

    public function indexReposFormations()
    {
        try {
            // Charger la liste "Vacances et JF"
            $liste = Liste::where('nom_fr', 'Vacances et JF')->where('statut', 'Actif')->first();
            if (! $liste || empty($liste->options)) {
                Log::warning('Liste "Vacances et JF" non trouvée ou vide.');
                $options = [];
            } else {
                $options = collect($liste->options)->keyBy('nom_fr');
            }

            // Charger les périodes de repos
            $repos = ReposFormation::select(
                'id',
                'date_debut',
                'date_fin',
                'nombre_jour',
                'description',
                'type_repos_formation_fr'
            )
                ->whereNull('deleted_at')
                ->get()
                ->map(function ($repo) use ($options) {
                    // Calculer date_fin si NULL
                    $dateDebut = Carbon::parse($repo->date_debut);
                    $dateFin = $repo->date_fin
                        ? Carbon::parse($repo->date_fin)
                        : $dateDebut->copy()->addDays(max(1, $repo->nombre_jour ?? 1) - 1);

                    // Pour FullCalendar, ajouter un jour à date_fin (exclusivité)
                    $dateFinCalendar = $dateFin->copy()->addDay();

                    // Mapper le type_repos_formation_fr à la couleur de la liste
                    $type = $repo->type_repos_formation_fr;
                    $couleur = $options[$type]['couleur'] ?? '#3b82f6';

                    $event = [
                        'id' => $repo->id,
                        'nom_fr' => $type ?: 'Période sans nom',
                        'date_debut' => $dateDebut->toIso8601String(),
                        'date_fin' => $dateFinCalendar->toIso8601String(),
                        'description_fr' => $repo->description ?: '',
                        'couleur' => $couleur,
                    ];

                    Log::debug('Événement repos_formation formaté:', [
                        'id' => $repo->id,
                        'date_debut' => $event['date_debut'],
                        'date_fin' => $event['date_fin'],
                        'nombre_jour' => $repo->nombre_jour,
                        'type' => $type,
                        'couleur' => $couleur,
                    ]);

                    return $event;
                });

            Log::info('Périodes repos_formations renvoyées:', ['count' => $repos->count(), 'events' => $repos->toArray()]);

            return response()->json($repos, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des repos_formations:', ['message' => $e->getMessage()]);

            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    public function show(Calendrier $calendrier)
    {
        return response()->json($this->formatEvent($calendrier), 200);
    }

    private function validateRequest(Request $request, $id = null)
    {
        return $request->validate([
            'titre' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'description' => 'nullable|string',
            'observation' => 'nullable|string',
            'type' => 'nullable|string|in:événement,tâche,note,repos_formation',
            'color' => 'nullable|string|regex:/^#?[0-9A-Fa-f]{6}$/',
        ]);
    }

    public function storeCalendrier(Request $request)
    {
        try {
            $validated = $this->validateRequest($request);
            Log::info('Store Calendrier - Données validées : ', $validated);

            if (isset($validated['color']) && ! str_starts_with($validated['color'], '#')) {
                $validated['color'] = '#'.$validated['color'];
            }

            $calendrier = Calendrier::create($validated);
            Log::info('Store Calendrier - Événement créé : ', ['id' => $calendrier->id, 'color' => $calendrier->color]);

            return response()->json($this->formatEvent($calendrier), 201);
        } catch (ValidationException $e) {
            Log::error('Store Calendrier - Erreur de validation : ', $e->errors());

            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function updateCalendrier(Request $request, Calendrier $calendrier)
    {
        try {
            $validated = $this->validateRequest($request, $calendrier->id);
            Log::info('Update Calendrier - Données validées : ', $validated);

            if (isset($validated['color']) && ! str_starts_with($validated['color'], '#')) {
                $validated['color'] = '#'.$validated['color'];
            }

            $calendrier->update($validated);
            Log::info('Update Calendrier - Événement mis à jour : ', ['id' => $calendrier->id, 'color' => $calendrier->color]);

            return response()->json($this->formatEvent($calendrier));
        } catch (ValidationException $e) {
            Log::error('Update Calendrier - Erreur de validation : ', $e->errors());

            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroyCalendrier(Calendrier $calendrier)
    {
        $calendrier->delete();
        Log::info('Destroy Calendrier - Événement supprimé : ', ['id' => $calendrier->id]);

        return response()->json(null, 204);
    }

    private function formatEvent(Calendrier $calendrier): array
    {
        $event = [
            'id' => $calendrier->id,
            'title' => $calendrier->titre,
            'start' => $calendrier->date_debut->toIso8601String(),
            'end' => $calendrier->date_fin?->toIso8601String(),
            'description' => $calendrier->description,
            'backgroundColor' => $calendrier->color ?? '#3b82f6',
            'borderColor' => $calendrier->color ?? '#3b82f6',
            'textColor' => $this->getContrastColor($calendrier->color ?? '#3b82f6'),
            'extendedProps' => [
                'type' => $calendrier->type ?? 'événement',
                'description' => $calendrier->description,
                'observation' => $calendrier->observation,
            ],
        ];
        Log::info('Format Event - Événement formaté : ', ['id' => $calendrier->id, 'backgroundColor' => $event['backgroundColor']]);

        return $event;
    }

    private function getContrastColor($hexColor)
    {
        $hexColor = ltrim($hexColor, '#');
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return $brightness > 128 ? '#000000' : '#ffffff';
    }
}
