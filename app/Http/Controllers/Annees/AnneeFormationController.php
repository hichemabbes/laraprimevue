<?php

namespace App\Http\Controllers\Annees;

use App\Http\Controllers\Controller;
use App\Models\Annees\AnneeFormation;
use App\Models\Annees\ReposFormation;
use App\Models\DeletePassword;
use App\Models\Liste;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AnneeFormationController extends Controller
{
    private function verifyDeletePassword(Request $request)
    {
        $passwordRecord = DeletePassword::first();
        if (! $passwordRecord) {
            Log::warning('Aucun mot de passe de suppression défini');
            throw new \Exception('Aucun mot de passe de suppression défini', 400);
        }
        if (! $passwordRecord->verifyPassword($request->password)) {
            Log::warning('Mot de passe incorrect pour suppression', ['input' => $request->password]);
            throw new \Exception('Mot de passe incorrect', 401);
        }
    }

    private function calculateReposFormationAttributes($dateDebut, $dateFin)
    {
        $dateDebut = Carbon::parse($dateDebut);
        $dateFin = $dateFin ? Carbon::parse($dateFin) : null;
        $nombreJour = $dateFin ? $dateDebut->diffInDays($dateFin) + 1 : 1;
        $today = Carbon::today();

        $statut = 'annule';
        if ($dateDebut->gt($today)) {
            $statut = 'a_venir';
        } elseif ($dateFin && $today->between($dateDebut, $dateFin)) {
            $statut = 'en_cours';
        } elseif ($dateFin && $dateFin->lt($today)) {
            $statut = 'termine';
        } elseif (! $dateFin && $today->equalTo($dateDebut)) {
            $statut = 'en_cours';
        }

        return ['nombre_jour' => $nombreJour, 'statut' => $statut];
    }

    public function indexWithOptions(Request $request)
    {
        try {
            $annees = AnneeFormation::with(['reposFormations'])->paginate($request->per_page ?? 10);
            $lists = Liste::whereIn('nom_fr', ['Statuts Années', 'Vacances et JF'])
                ->where('statut', 'Actif')
                ->get()
                ->pluck('options', 'nom_fr');

            if (! isset($lists['Vacances et JF']) || empty($lists['Vacances et JF'])) {
                Log::warning('Aucune option trouvée pour la liste "Vacances et JF" dans indexWithOptions.');
                $lists['Vacances et JF'] = [];
            }

            Log::info('AnneesFormation avec options récupérées:', [
                'annees_count' => $annees->count(),
                'lists' => $lists->keys()->toArray(),
            ]);

            return response()->json([
                'annees' => $annees,
                'lists' => $lists,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des années avec options:', ['message' => $e->getMessage()]);

            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    public function indexAnneeFormation()
    {
        $annees = AnneeFormation::with(['reposFormations'])->get();
        Log::info('AnneesFormation récupérées:', ['count' => $annees->count()]);

        return response()->json($annees, 200);
    }

    public function show($id)
    {
        $annee = AnneeFormation::with(['reposFormations'])->findOrFail($id);
        $lists = Liste::whereIn('nom_fr', ['Statuts Années', 'Vacances et JF'])
            ->where('statut', 'Actif')
            ->get()
            ->pluck('options', 'nom_fr');

        if (! isset($lists['Vacances et JF']) || empty($lists['Vacances et JF'])) {
            Log::warning('Aucune option trouvée pour la liste "Vacances et JF" dans show.', ['annee_id' => $id]);
            $lists['Vacances et JF'] = [];
        }

        Log::info('AnneeFormation avec options récupérée:', ['id' => $id]);

        return response()->json([
            'annee' => $annee,
            'lists' => $lists,
        ], 200);
    }

    private function validateRequest(Request $request, $id = null)
    {
        $rules = [
            'intitule' => [
                'required',
                'max:255',
                function ($attribute, $value, $fail) use ($id) {
                    $exists = AnneeFormation::where('intitule', $value)
                        ->whereNull('deleted_at')
                        ->when($id, fn ($query) => $query->where('id', '!=', $id))
                        ->exists();
                    if ($exists) {
                        $fail("L'intitulé '$value' existe déjà.");
                    }
                },
            ],
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'observation' => 'nullable|string',
        ];

        $validated = $request->validate($rules);

        return $validated;
    }

    public function storeAnneeFormation(Request $request)
    {
        try {
            $validated = $this->validateRequest($request);
            $anneeFormation = AnneeFormation::create($validated);
            Log::info('AnneeFormation créée:', $anneeFormation->toArray());

            return response()->json($anneeFormation, 201);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de la création de AnnéeFormation:', $e->errors());

            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur serveur lors de la création de AnnéeFormation:', ['message' => $e->getMessage()]);

            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    public function updateAnneeFormation(Request $request, $id)
    {
        try {
            $anneeFormation = AnneeFormation::findOrFail($id);
            $validated = $this->validateRequest($request, $id);
            $anneeFormation->update($validated);
            Log::info('AnneeFormation mise à jour:', $anneeFormation->toArray());

            return response()->json($anneeFormation, 200);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de la mise à jour de AnnéeFormation:', $e->errors());

            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur serveur lors de la mise à jour de AnnéeFormation:', ['message' => $e->getMessage()]);

            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    public function destroyAnneeFormation($id, Request $request)
    {
        try {
            $this->verifyDeletePassword($request);
            $annee = AnneeFormation::findOrFail($id);
            $annee->delete();

            return response()->json(['message' => 'Année de formation supprimée avec succès']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de AnnéeFormation: ', ['error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function getActiveAnneeFormation()
    {
        $activeAnnee = AnneeFormation::where('date_debut', '<=', now())
            ->where('date_fin', '>=', now())
            ->first();
        Log::info('AnneeFormation active:', $activeAnnee ? $activeAnnee->toArray() : null);

        return response()->json($activeAnnee ?: [], 200);
    }

    private function validatePeriodeRequest(Request $request)
    {
        return $request->validate([
            'annee_formation_id' => [
                'required',
                'exists:annees_formations,id',
                function ($attribute, $value, $fail) {
                    if (! AnneeFormation::find($value)) {
                        $fail('L\'année de formation n\'existe pas.');
                    }
                },
            ],
            'type_repos_formation_fr' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $liste = Liste::where('nom_fr', 'Vacances et JF')->where('statut', 'Actif')->first();
                    if (! $liste) {
                        Log::error('Liste "Vacances et JF" non trouvée lors de la validation.');
                        $fail('La liste des types de repos est introuvable.');

                        return;
                    }
                    if (! collect($liste->options)->pluck('nom_fr')->contains($value)) {
                        Log::warning('Type de repos invalide.', ['type' => $value]);
                        $fail("Le type de repos '$value' est invalide.");
                    }
                },
            ],
            'date_debut' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    $annee = AnneeFormation::find($request->annee_formation_id);
                    if (! $annee) {
                        $fail('L\'année de formation n\'existe pas.');

                        return;
                    }
                    $dateDebut = Carbon::parse($value);
                    $anneeDebut = Carbon::parse($annee->date_debut);
                    $anneeFin = Carbon::parse($annee->date_fin);
                    if ($dateDebut->lt($anneeDebut) || $dateDebut->gt($anneeFin)) {
                        $fail("La date de début doit être entre {$anneeDebut->toDateString()} et {$anneeFin->toDateString()}.");
                    }
                },
            ],
            'date_fin' => [
                'nullable',
                'date',
                'after_or_equal:date_debut',
                function ($attribute, $value, $fail) use ($request) {
                    if (! $value) {
                        return;
                    }
                    $annee = AnneeFormation::find($request->annee_formation_id);
                    if (! $annee) {
                        $fail('L\'année de formation n\'existe pas.');

                        return;
                    }
                    $dateFin = Carbon::parse($value);
                    $anneeDebut = Carbon::parse($annee->date_debut);
                    $anneeFin = Carbon::parse($annee->date_fin);
                    if ($dateFin->lt($anneeDebut) || $dateFin->gt($anneeFin)) {
                        $fail("La date de fin doit être entre {$anneeDebut->toDateString()} et {$anneeFin->toDateString()}.");
                    }
                },
            ],
            'description' => 'nullable|string',
        ]);
    }

    public function storeReposFormation(Request $request)
    {
        try {
            $validated = $this->validatePeriodeRequest($request);
            $attributes = $this->calculateReposFormationAttributes($validated['date_debut'], $validated['date_fin']);

            $periode = new ReposFormation($validated);
            $liste = Liste::where('nom_fr', 'Vacances et JF')->where('statut', 'Actif')->first();
            if (! $liste) {
                Log::error('Liste "Vacances et JF" non trouvée lors de la création de ReposFormation.');

                return response()->json([
                    'errors' => ['type_repos_formation_fr' => 'Liste des types de repos introuvable'],
                ], 422);
            }

            $option = collect($liste->options)->firstWhere('nom_fr', $validated['type_repos_formation_fr']);
            if (! $option) {
                Log::error('Échec du mapping de type_repos_formation_ar pour type_repos_formation_fr:', [
                    'type_repos_formation_fr' => $validated['type_repos_formation_fr'],
                ]);

                return response()->json([
                    'errors' => ['type_repos_formation_fr' => 'Type de repos invalide'],
                ], 422);
            }

            $periode->type_repos_formation_ar = $option['nom_ar'];
            $periode->fill($attributes);
            $periode->save();

            Log::info('ReposFormation créé:', $periode->toArray());

            return response()->json($periode->load(['anneeFormation']), 201);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de la création de ReposFormation:', $e->errors());

            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur serveur lors de la création de ReposFormation:', ['message' => $e->getMessage()]);

            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    public function updateReposFormation(Request $request, $id)
    {
        try {
            $periode = ReposFormation::findOrFail($id);
            $validated = $this->validatePeriodeRequest($request);
            $attributes = $this->calculateReposFormationAttributes($validated['date_debut'], $validated['date_fin']);

            $periode->fill($validated);
            $liste = Liste::where('nom_fr', 'Vacances et JF')->where('statut', 'Actif')->first();
            if (! $liste) {
                Log::error('Liste "Vacances et JF" non trouvée lors de la mise à jour de ReposFormation.');

                return response()->json([
                    'errors' => ['type_repos_formation_fr' => 'Liste des types de repos introuvable'],
                ], 422);
            }

            $option = collect($liste->options)->firstWhere('nom_fr', $validated['type_repos_formation_fr']);
            if (! $option) {
                Log::error('Échec du mapping de type_repos_formation_ar pour type_repos_formation_fr:', [
                    'type_repos_formation_fr' => $validated['type_repos_formation_fr'],
                ]);

                return response()->json([
                    'errors' => ['type_repos_formation_fr' => 'Type de repos invalide'],
                ], 422);
            }

            $periode->type_repos_formation_ar = $option['nom_ar'];
            $periode->fill($attributes);
            $periode->save();

            Log::info('ReposFormation mis à jour:', $periode->toArray());

            return response()->json($periode->load(['anneeFormation']), 200);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de la mise à jour de ReposFormation:', $e->errors());

            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur serveur lors de la mise à jour de ReposFormation:', ['message' => $e->getMessage()]);

            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    public function destroyReposFormation(Request $request, $id)
    {
        try {
            $request->validate([
                'password' => 'required|string',
            ]);

            $this->verifyDeletePassword($request);

            $periode = ReposFormation::findOrFail($id);
            $periode->statut = 'annule';
            $periode->save();
            Log::info('ReposFormation annulé:', ['id' => $id]);

            return response()->json(['message' => 'Période de repos annulée avec succès']);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de l\'annulation de ReposFormation:', $e->errors());

            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation de ReposFormation:', ['error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
