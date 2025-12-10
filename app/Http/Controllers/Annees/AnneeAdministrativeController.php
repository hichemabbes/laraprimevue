<?php

namespace App\Http\Controllers\Annees;

use App\Http\Controllers\Controller;
use App\Models\Annees\AnneeAdministrative;
use App\Models\Annees\ReposAdministratif;
use App\Models\DeletePassword;
use App\Models\Liste;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AnneeAdministrativeController extends Controller
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

    private function calculateReposAdministratifAttributes($dateDebut, $dateFin)
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

    public function indexAnneeAdministrative()
    {
        $annees = AnneeAdministrative::with(['reposAdministratifs'])->get();
        Log::info('AnneesAdministrative récupérées:', ['count' => $annees->count()]);

        return response()->json($annees, 200);
    }

    public function show($id)
    {
        $annee = AnneeAdministrative::with(['reposAdministratifs'])->findOrFail($id);
        $lists = Liste::where('nom_fr', 'Nature repos administratif')
            ->where('statut', 'Actif')
            ->get()
            ->pluck('options', 'nom_fr');

        if (! isset($lists['Nature repos administratif']) || empty($lists['Nature repos administratif'])) {
            Log::warning('Aucune option trouvée pour la liste "Nature repos administratif" dans show.', ['annee_id' => $id]);
            $lists['Nature repos administratif'] = [];
        }

        Log::info('AnneeAdministrative avec options récupérée:', ['id' => $id]);

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
                    $exists = AnneeAdministrative::where('intitule', $value)
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

        // Vérifier que les dates correspondent à l'année de l'intitulé
        if (preg_match('/^\d{4}$/', $validated['intitule'])) {
            $year = (int) $validated['intitule'];
            $expectedStart = Carbon::create($year, 1, 1)->toDateString();
            $expectedEnd = Carbon::create($year, 12, 31)->toDateString();

            if ($validated['date_debut'] !== $expectedStart || $validated['date_fin'] !== $expectedEnd) {
                throw ValidationException::withMessages([
                    'date_debut' => "La date de début doit être le 01/01/{$year}.",
                    'date_fin' => "La date de fin doit être le 31/12/{$year}.",
                ]);
            }
        }

        return $validated;
    }

    public function storeAnneeAdministrative(Request $request)
    {
        try {
            $validated = $this->validateRequest($request);
            $anneeAdministrative = AnneeAdministrative::create($validated);
            Log::info('AnneeAdministrative créée:', $anneeAdministrative->toArray());

            return response()->json($anneeAdministrative, 201);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de la création de AnnéeAdministrative:', $e->errors());

            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur serveur lors de la création de AnnéeAdministrative:', ['message' => $e->getMessage()]);

            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    public function updateAnneeAdministrative(Request $request, $id)
    {
        try {
            $anneeAdministrative = AnneeAdministrative::findOrFail($id);
            $validated = $this->validateRequest($request, $id);
            $anneeAdministrative->update($validated);
            Log::info('AnneeAdministrative mise à jour:', $anneeAdministrative->toArray());

            return response()->json($anneeAdministrative, 200);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de la mise à jour de AnnéeAdministrative:', $e->errors());

            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur serveur lors de la mise à jour de AnnéeAdministrative:', ['message' => $e->getMessage()]);

            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    public function destroyAnneeAdministrative($id, Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|string',
            ]);
            $this->verifyDeletePassword($request);
            $annee = AnneeAdministrative::findOrFail($id);
            $annee->delete();

            return response()->json(['message' => 'Année administrative supprimée avec succès']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de AnnéeAdministrative: ', ['error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function getActiveAnneeAdministrative()
    {
        $activeAnnee = AnneeAdministrative::where('date_debut', '<=', now())
            ->where('date_fin', '>=', now())
            ->first();
        Log::info('AnneeAdministrative active:', $activeAnnee ? $activeAnnee->toArray() : null);

        return response()->json($activeAnnee ?: [], 200);
    }

    private function validatePeriodeRequest(Request $request)
    {
        return $request->validate([
            'annee_administrative_id' => [
                'required',
                'exists:annees_administratives,id',
                function ($attribute, $value, $fail) {
                    if (! AnneeAdministrative::find($value)) {
                        $fail("L'année administrative n'existe pas.");
                    }
                },
            ],
            'type_repos_administratif_fr' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $liste = Liste::where('nom_fr', 'Nature repos administratif')->where('statut', 'Actif')->first();
                    if (! $liste) {
                        Log::error('Liste "Nature repos administratif" non trouvée lors de la validation.');
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
                    $annee = AnneeAdministrative::find($request->annee_administrative_id);
                    if (! $annee) {
                        $fail("L'année administrative n'existe pas.");

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
                    $annee = AnneeAdministrative::find($request->annee_administrative_id);
                    if (! $annee) {
                        $fail("L'année administrative n'existe pas.");

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

    public function storeReposAdministratif(Request $request)
    {
        try {
            $validated = $this->validatePeriodeRequest($request);
            $attributes = $this->calculateReposAdministratifAttributes($validated['date_debut'], $validated['date_fin']);

            $periode = new ReposAdministratif($validated);
            $liste = Liste::where('nom_fr', 'Nature repos administratif')->where('statut', 'Actif')->first();
            if (! $liste) {
                Log::error('Liste "Nature repos administratif" non trouvée lors de la création de ReposAdministratif.');

                return response()->json([
                    'errors' => ['type_repos_administratif_fr' => 'Liste des types de repos introuvable'],
                ], 422);
            }

            $option = collect($liste->options)->firstWhere('nom_fr', $validated['type_repos_administratif_fr']);
            if (! $option) {
                Log::error('Échec du mapping de type_repos_administratif_ar pour type_repos_administratif_fr:', [
                    'type_repos_administratif_fr' => $validated['type_repos_administratif_fr'],
                ]);

                return response()->json([
                    'errors' => ['type_repos_administratif_fr' => 'Type de repos invalide'],
                ], 422);
            }

            $periode->type_repos_administratif_ar = $option['nom_ar'];
            $periode->fill($attributes);
            $periode->save();

            Log::info('ReposAdministratif créé:', $periode->toArray());

            return response()->json($periode->load(['anneeAdministrative']), 201);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de la création de ReposAdministratif:', $e->errors());

            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur serveur lors de la création de ReposAdministratif:', ['message' => $e->getMessage()]);

            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    public function updateReposAdministratif(Request $request, $id)
    {
        try {
            $periode = ReposAdministratif::findOrFail($id);
            $validated = $this->validatePeriodeRequest($request);
            $attributes = $this->calculateReposAdministratifAttributes($validated['date_debut'], $validated['date_fin']);

            $periode->fill($validated);
            $liste = Liste::where('nom_fr', 'Nature repos administratif')->where('statut', 'Actif')->first();
            if (! $liste) {
                Log::error('Liste "Nature repos administratif" non trouvée lors de la mise à jour de ReposAdministratif.');

                return response()->json([
                    'errors' => ['type_repos_administratif_fr' => 'Liste des types de repos introuvable'],
                ], 422);
            }

            $option = collect($liste->options)->firstWhere('nom_fr', $validated['type_repos_administratif_fr']);
            if (! $option) {
                Log::error('Échec du mapping de type_repos_administratif_ar pour type_repos_administratif_fr:', [
                    'type_repos_administratif_fr' => $validated['type_repos_administratif_fr'],
                ]);

                return response()->json([
                    'errors' => ['type_repos_administratif_fr' => 'Type de repos invalide'],
                ], 422);
            }

            $periode->type_repos_administratif_ar = $option['nom_ar'];
            $periode->fill($attributes);
            $periode->save();

            Log::info('ReposAdministratif mis à jour:', $periode->toArray());

            return response()->json($periode->load(['anneeAdministrative']), 200);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de la mise à jour de ReposAdministratif:', $e->errors());

            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur serveur lors de la mise à jour de ReposAdministratif:', ['message' => $e->getMessage()]);

            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    public function destroyReposAdministratif(Request $request, $id)
    {
        try {
            $request->validate([
                'password' => 'required|string',
            ]);

            $this->verifyDeletePassword($request);

            $periode = ReposAdministratif::findOrFail($id);
            $periode->statut = 'annule';
            $periode->save();
            Log::info('ReposAdministratif annulé:', ['id' => $id]);

            return response()->json(['message' => 'Période de repos annulée avec succès']);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de l\'annulation de ReposAdministratif:', $e->errors());

            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation de ReposAdministratif:', ['error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function getPeriodesRepos($id)
    {
        $annee = AnneeAdministrative::with(['reposAdministratifs'])->findOrFail($id);

        return response()->json($annee->reposAdministratifs, 200);
    }
}
