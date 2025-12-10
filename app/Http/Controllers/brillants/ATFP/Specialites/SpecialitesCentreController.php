<?php

namespace App\Http\Controllers\brillants\ATFP\Specialites;

use App\Http\Controllers\Controller;
use App\Models\Centre\Centre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SpecialitesCentreController extends Controller
{
    private const STATUT_ACTIF = 'Actif';
    private const STATUT_INACTIF = 'Inactif';

    /**
     * Liste tous les centres avec leurs spécialités associées.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 20);
            $centres = Centre::with(['specialites' => function ($query) {
                $query->whereNull('specialites_centres.deleted_at')
                    ->select([
                        'specialites.id',
                        'specialites.code',
                        'specialites.nom_fr',
                        'specialites.nom_ar',
                        'specialites.standardisation_ar',
                        'specialites.secteur_id',
                        'specialites.sous_secteur_id',
                        'specialites_centres.statut as pivot_statut',
                        'specialites_centres.observation as specialite_observation',
                        'specialites_centres.date_debut',
                        'specialites_centres.date_fin',
                        'specialites_centres.centre_id',
                    ]);
            }])
                ->whereNull('centres.deleted_at')
                ->select([
                    'id',
                    'code',
                    'nom_fr',
                    'nom_ar',
                    'adresse_fr',
                    'adresse_ar',
                    'telephone_1',
                    'telephone_2',
                    'fax_1',
                    'fax_2',
                    'email',
                    'gouvernorat_fr',
                    'gouvernorat_ar',
                    'type_centre_fr',
                    'type_centre_ar',
                    'classe_fr',
                    'classe_ar',
                    'economat_fr',
                    'economat_ar',
                    'etat_fr',
                    'etat_ar',
                    'statut_fr',
                    'statut_ar',
                    'date_creation',
                    'date_ouverture',
                    'date_fermeture',
                    'observation_fr',
                    'observation_ar',
                    'logo',
                    'created_at',
                    'updated_at',
                ])
                ->paginate($perPage);

            Log::info('Centres récupérés avec leurs spécialités', [
                'count' => $centres->total(),
                'page' => $centres->currentPage(),
                'per_page' => $perPage,
                'request' => $request->fullUrl(),
            ]);

            return response()->json([
                'data' => $centres->items(),
                'pagination' => [
                    'total' => $centres->total(),
                    'per_page' => $centres->perPage(),
                    'current_page' => $centres->currentPage(),
                    'last_page' => $centres->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des centres : ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Erreur lors de la récupération des centres',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Associe plusieurs spécialités à un centre.
     */
    public function associateSpecialites(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'centre_id' => 'required|exists:centres,id,deleted_at,NULL',
                'specialite_ids' => 'required|array|min:1',
                'specialite_ids.*' => 'exists:specialites,id,deleted_at,NULL',
                'statut' => 'required|in:' . self::STATUT_ACTIF . ',' . self::STATUT_INACTIF,
                'date_debut' => 'required|date|before_or_equal:today',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'observation' => 'nullable|string|max:5000',
            ]);

            DB::beginTransaction();

            $centre = Centre::findOrFail($validated['centre_id']);
            $existingSpecialites = $centre->specialites()->pluck('specialites.id')->toArray();
            $newSpecialiteIds = array_diff($validated['specialite_ids'], $existingSpecialites);

            if (empty($newSpecialiteIds)) {
                DB::rollBack();
                return response()->json(['error' => 'Toutes les spécialités sélectionnées sont déjà associées'], 422);
            }

            $syncData = [];
            foreach ($newSpecialiteIds as $specialiteId) {
                $syncData[$specialiteId] = [
                    'statut' => $validated['statut'],
                    'date_debut' => $validated['date_debut'],
                    'date_fin' => $validated['date_fin'],
                    'observation' => $validated['observation'] ? Str::limit(strip_tags($validated['observation']), 5000) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $centre->specialites()->syncWithoutDetaching($syncData);

            DB::commit();

            $centre->load(['specialites' => function ($query) {
                $query->whereNull('specialites_centres.deleted_at')
                    ->select([
                        'specialites.id',
                        'specialites.code',
                        'specialites.nom_fr',
                        'specialites.nom_ar',
                        'specialites.standardisation_ar',
                        'specialites.secteur_id',
                        'specialites.sous_secteur_id',
                        'specialites_centres.statut as pivot_statut',
                        'specialites_centres.observation as specialite_observation',
                        'specialites_centres.date_debut',
                        'specialites_centres.date_fin',
                        'specialites_centres.centre_id',
                    ]);
            }]);

            Log::info('Spécialités associées au centre', [
                'centre_id' => $centre->id,
                'specialite_ids' => $newSpecialiteIds,
                'statut' => $validated['statut'],
            ]);

            return response()->json(['data' => $centre], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Erreur de validation lors de l\'association des spécialités', [
                'errors' => $e->errors(),
                'input' => $request->except(['observation']),
            ]);
            return response()->json(['error' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'association des spécialités : ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Erreur lors de l\'association des spécialités', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Met à jour une association centre-spécialité.
     */
    public function updateSpecialite(Request $request, $centreId, $specialiteId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'statut' => 'required|in:' . self::STATUT_ACTIF . ',' . self::STATUT_INACTIF,
                'date_debut' => 'required|date|before_or_equal:today',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'observation' => 'nullable|string|max:5000',
            ]);

            DB::beginTransaction();

            $centre = Centre::whereNull('deleted_at')->findOrFail($centreId);

            $exists = $centre->specialites()->where('specialites.id', $specialiteId)->whereNull('specialites.deleted_at')->exists();
            if (!$exists) {
                DB::rollBack();
                return response()->json(['error' => 'Cette spécialité n\'est pas associée à ce centre ou a été supprimée'], 404);
            }

            $centre->specialites()->updateExistingPivot($specialiteId, [
                'statut' => $validated['statut'],
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'observation' => $validated['observation'] ? Str::limit(strip_tags($validated['observation']), 5000) : null,
                'updated_at' => now(),
            ]);

            DB::commit();

            $centre->load(['specialites' => function ($query) {
                $query->whereNull('specialites_centres.deleted_at')
                    ->select([
                        'specialites.id',
                        'specialites.code',
                        'specialites.nom_fr',
                        'specialites.nom_ar',
                        'specialites.standardisation_ar',
                        'specialites.secteur_id',
                        'specialites.sous_secteur_id',
                        'specialites_centres.statut as pivot_statut',
                        'specialites_centres.observation as specialite_observation',
                        'specialites_centres.date_debut',
                        'specialites_centres.date_fin',
                        'specialites_centres.centre_id',
                    ]);
            }]);

            Log::info('Association centre-spécialité mise à jour', [
                'centre_id' => $centreId,
                'specialite_id' => $specialiteId,
                'statut' => $validated['statut'],
            ]);

            return response()->json(['data' => $centre], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Erreur de validation lors de la mise à jour de l\'association', [
                'errors' => $e->errors(),
                'input' => $request->except(['observation']),
            ]);
            return response()->json(['error' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'association : ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Erreur lors de la mise à jour de l\'association', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Supprime une association centre-spécialité.
     */
    public function dissociateSpecialite($centreId, $specialiteId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $centre = Centre::whereNull('deleted_at')->findOrFail($centreId);

            $exists = $centre->specialites()->where('specialites.id', $specialiteId)->whereNull('specialites.deleted_at')->exists();
            if (!$exists) {
                DB::rollBack();
                return response()->json(['error' => 'Cette spécialité n\'est pas associée à ce centre ou a été supprimée'], 404);
            }

            $centre->specialites()->detach($specialiteId);

            DB::commit();

            $centre->load(['specialites' => function ($query) {
                $query->whereNull('specialites_centres.deleted_at')
                    ->select([
                        'specialites.id',
                        'specialites.code',
                        'specialites.nom_fr',
                        'specialites.nom_ar',
                        'specialites.standardisation_ar',
                        'specialites.secteur_id',
                        'specialites.sous_secteur_id',
                        'specialites_centres.statut as pivot_statut',
                        'specialites_centres.observation as specialite_observation',
                        'specialites_centres.date_debut',
                        'specialites_centres.date_fin',
                        'specialites_centres.centre_id',
                    ]);
            }]);

            Log::info('Spécialité désassociée du centre', [
                'centre_id' => $centreId,
                'specialite_id' => $specialiteId,
            ]);

            return response()->json(['data' => $centre], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la désassociation de la spécialité : ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Erreur lors de la désassociation de la spécialité', 'message' => $e->getMessage()], 500);
        }
    }
}
