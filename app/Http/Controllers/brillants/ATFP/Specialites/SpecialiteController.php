<?php

namespace App\Http\Controllers\brillants\ATFP\Specialites;

use App\Http\Controllers\Controller;
use App\Models\brillants\Specialites\Secteurs\Secteur;
use App\Models\brillants\Specialites\SousSecteurs\SousSecteur;
use App\Models\brillants\Specialites\Specialite;
use App\Models\Liste;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpecialiteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $withInfos = $request->query('with_infos', false);
            $perPage = $request->query('per_page', 20);
            $page = $request->query('page', 1);
            $standardisationAr = $request->query('standardisation_ar');
            $all = $request->query('all', false); // Nouveau paramètre pour récupérer toutes les spécialités

            $query = Specialite::with(['secteur', 'sousSecteur'])
                ->when($standardisationAr, function ($q) use ($standardisationAr) {
                    return $q->where('standardisation_ar', $standardisationAr);
                })
                ->whereNull('deleted_at'); // Assurer que les spécialités supprimées sont exclues

            if ($withInfos) {
                $query->with(['infos_specialites' => function ($q) {
                    $q->whereNull('deleted_at');
                }]);
            }

            if ($all) {
                $specialites = $query->get();
                Log::info('Toutes les spécialités récupérées', [
                    'count' => $specialites->count(),
                    'with_infos' => $withInfos,
                    'standardisation_ar' => $standardisationAr,
                    'request' => $request->fullUrl(),
                ]);
                return response()->json(['data' => $specialites], 200);
            }

            $specialites = $query->paginate($perPage, ['*'], 'page', $page);

            Log::info('Spécialités récupérées', [
                'count' => $specialites->total(),
                'page' => $page,
                'per_page' => $perPage,
                'with_infos' => $withInfos,
                'standardisation_ar' => $standardisationAr,
                'request' => $request->fullUrl(),
            ]);

            return response()->json($specialites, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des spécialités: '.$e->getMessage(), [
                'request' => $request->fullUrl(),
            ]);

            return response()->json(['error' => 'Erreur lors de la récupération des spécialités.'], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $anneeId = request()->query('annee_id');
            $specialite = Specialite::with([
                'secteur',
                'sousSecteur',
                'infos_specialites' => function ($query) use ($anneeId) {
                    $query->when($anneeId, function ($q) use ($anneeId) {
                        $q->where('annee_formation_id', $anneeId);
                    })->whereNull('deleted_at');
                },
                'programmes' => function ($query) use ($anneeId) {
                    $query->when($anneeId, function ($q) use ($anneeId) {
                        $q->whereHas('specialite.infos_specialites', function ($subQ) use ($anneeId) {
                            $subQ->where('annee_formation_id', $anneeId);
                        });
                    })
                        ->with(['modules' => function ($q) {
                            $q->whereNull('deleted_at');
                        }])
                        ->whereNull('deleted_at');
                },
            ])->findOrFail($id);

            Log::info('Spécialité récupérée', [
                'id' => $id,
                'annee_id' => $anneeId,
                'request' => request()->fullUrl(),
            ]);

            return response()->json($specialite, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la spécialité: '.$e->getMessage(), [
                'id' => $id,
                'request' => request()->fullUrl(),
            ]);

            return response()->json(['error' => 'Erreur lors de la récupération de la spécialité.'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $diplomas = Liste::where('nom_fr', 'Diplomes')->first()->options ?? [];
            $standardisationMap = [
                'جديد' => 'Nouveau',
                'مقيس' => 'Standardisé',
                'غير مقيس' => 'Non Standardisé',
            ];

            $rules = [
                'code' => 'required|string|max:20',
                'standardisation_ar' => 'required|in:جديد,مقيس,غير مقيس',
                'nom_ar' => 'required|string|max:255',
                'nom_fr' => 'nullable|string|max:255',
                'diplome_fr' => 'required|string',
                'statut' => 'required|in:Active,Annulée,Remplacée',
                'secteur_id' => 'required_unless:standardisation_ar,جديد|nullable|exists:secteurs,id',
                'sous_secteur_id' => 'required_if:standardisation_ar,مقيس|nullable|exists:sous_secteurs,id',
                'observation' => 'nullable|string|max:1000',
                'date_creation' => 'nullable|date',
                'date_annulation' => 'nullable|date',
            ];

            $validated = $request->validate($rules);

            $data = $request->all();
            $data['standardisation_fr'] = $standardisationMap[$data['standardisation_ar']] ?? null;
            if (! empty($data['diplome_fr'])) {
                $diplome = collect($diplomas)->firstWhere('nom_fr', $data['diplome_fr']);
                $data['diplome_ar'] = $diplome ? $diplome['nom_ar'] : null;
            }

            DB::beginTransaction();
            $specialite = Specialite::create($data);
            $specialite->load(['secteur', 'sousSecteur']);
            DB::commit();

            Log::info('Spécialité créée', [
                'id' => $specialite->id,
                'code' => $specialite->code,
                'request' => $request->all(),
            ]);

            return response()->json($specialite, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Erreur de validation lors de la création de la spécialité', [
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'error' => 'Erreur de validation.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la spécialité: '.$e->getMessage(), [
                'request' => $request->all(),
            ]);

            return response()->json([
                'error' => 'Erreur lors de la création de la spécialité.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $specialite = Specialite::findOrFail($id);
            $diplomas = Liste::where('nom_fr', 'Diplomes')->first()->options ?? [];
            $standardisationMap = [
                'جديد' => 'Nouveau',
                'مقيس' => 'Standardisé',
                'غير مقيس' => 'Non Standardisé',
            ];

            $rules = [
                'code' => 'required|string|max:20',
                'standardisation_ar' => 'required|in:جديد,مقيس,غير مقيس',
                'nom_ar' => 'required|string|max:255',
                'nom_fr' => 'nullable|string|max:255',
                'diplome_fr' => 'required|string',
                'statut' => 'required|in:Active,Annulée,Remplacée',
                'secteur_id' => 'required_unless:standardisation_ar,جديد|nullable|exists:secteurs,id',
                'sous_secteur_id' => 'required_if:standardisation_ar,مقيس|nullable|exists:sous_secteurs,id',
                'observation' => 'nullable|string|max:1000',
                'date_creation' => 'nullable|date',
                'date_annulation' => 'nullable|date',
            ];

            $validated = $request->validate($rules);

            $data = $request->all();
            $data['standardisation_fr'] = $standardisationMap[$data['standardisation_ar']] ?? null;
            if (! empty($data['diplome_fr'])) {
                $diplome = collect($diplomas)->firstWhere('nom_fr', $data['diplome_fr']);
                $data['diplome_ar'] = $diplome ? $diplome['nom_ar'] : null;
            }

            DB::beginTransaction();
            $specialite->update($data);
            $specialite->load(['secteur', 'sousSecteur']);
            DB::commit();

            Log::info('Spécialité mise à jour', [
                'id' => $specialite->id,
                'code' => $specialite->code,
                'request' => $request->all(),
            ]);

            return response()->json($specialite, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Erreur de validation lors de la mise à jour de la spécialité', [
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'error' => 'Erreur de validation.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la spécialité: '.$e->getMessage(), [
                'id' => $id,
                'request' => $request->all(),
            ]);

            return response()->json([
                'error' => 'Erreur lors de la mise à jour de la spécialité.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $specialite = Specialite::findOrFail($id);
            $specialite->delete();

            Log::info('Spécialité supprimée', [
                'id' => $id,
                'request' => request()->fullUrl(),
            ]);

            return response()->json(['message' => 'Spécialité supprimée avec succès'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la spécialité: '.$e->getMessage(), [
                'id' => $id,
                'request' => request()->fullUrl(),
            ]);

            return response()->json(['error' => 'Erreur lors de la suppression de la spécialité.'], 500);
        }
    }

    public function deleteSelected(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                Log::warning('Aucun ID fourni pour la suppression des spécialités', [
                    'request' => $request->fullUrl(),
                ]);

                return response()->json(['error' => 'Aucun ID fourni pour la suppression.'], 400);
            }

            DB::beginTransaction();
            $deletedCount = Specialite::whereIn('id', $ids)->delete();
            DB::commit();

            Log::info('Spécialités supprimées', [
                'ids' => $ids,
                'count' => $deletedCount,
                'request' => $request->fullUrl(),
            ]);

            return response()->json([
                'message' => "$deletedCount spécialité(s) supprimée(s) avec succès.",
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression des spécialités sélectionnées: '.$e->getMessage(), [
                'ids' => $ids,
                'request' => $request->fullUrl(),
            ]);

            return response()->json([
                'error' => 'Erreur lors de la suppression des spécialités.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSecteur(Request $request): JsonResponse
    {
        $code = $request->query('code');
        $secteur = Secteur::where('code', $code)->whereNull('deleted_at')->first();
        if (! $secteur) {
            return response()->json(['error' => 'Secteur non trouvé'], 404);
        }

        return response()->json(['id' => $secteur->id], 200);
    }

    public function getSousSecteur(Request $request): JsonResponse
    {
        $code = $request->query('code');
        $sousSecteur = SousSecteur::where('code', $code)->whereNull('deleted_at')->first();
        if (! $sousSecteur) {
            return response()->json(['error' => 'Sous-secteur non trouvé'], 404);
        }

        return response()->json(['id' => $sousSecteur->id], 200);
    }
}
