<?php

namespace App\Http\Controllers\brillants\ATFP\Specialites\Secteurs;

use App\Http\Controllers\Controller;
use App\Models\brillants\Specialites\Secteurs\Secteur;
use App\Models\DeletePassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SecteurController extends Controller
{
    /**
     * Vérifie le mot de passe de suppression.
     *
     * @throws \Exception
     */
    private function verifyDeletePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $passwordRecord = Cache::remember('delete_password', 3600, fn () => DeletePassword::first());
        if (! $passwordRecord) {
            Log::warning('Aucun mot de passe de suppression défini');
            throw new \Exception('Aucun mot de passe de suppression défini', 400);
        }
        Log::info('Vérification du mot de passe pour suppression secteur', [
            'input' => $request->password,
            'stored_hash' => $passwordRecord->password,
        ]);
        if (! $passwordRecord->verifyPassword($request->password)) {
            Log::warning('Mot de passe incorrect pour suppression secteur', ['input' => $request->password]);
            throw new \Exception('Mot de passe incorrect', 401);
        }
    }

    // Lister tous les secteurs
    public function index()
    {
        try {
            return response()->json(Secteur::all(), 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des secteurs : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la récupération des secteurs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Afficher un secteur spécifique
    public function show($id)
    {
        try {
            $secteur = Secteur::find($id);
            if (! $secteur) {
                return response()->json(['message' => 'Secteur introuvable'], 404);
            }

            return response()->json($secteur, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du secteur : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la récupération du secteur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Vérifier l'unicité du code
    public function checkCode(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|max:20',
                'standardisation_ar' => 'required|in:مقيس,غير مقيس',
                'id' => 'nullable|integer|exists:secteurs,id',
            ]);

            // Skip uniqueness check if editing the same record
            if ($request->id) {
                $secteur = Secteur::find($request->id);
                if ($secteur && $secteur->code === $request->code && $secteur->standardisation_ar === $request->standardisation_ar) {
                    return response()->json(['isUnique' => true], 200);
                }
            }

            $query = Secteur::where('code', $request->code)
                ->where('standardisation_ar', $request->standardisation_ar)
                ->whereNull('deleted_at');

            if ($request->id) {
                $query->where('id', '!=', $request->id);
            }

            if ($query->exists()) {
                return response()->json([
                    'isUnique' => false,
                    'message' => "Ce code est déjà utilisé pour un secteur de standardisation {$request->standardisation_ar}.",
                ], 200);
            }

            return response()->json(['isUnique' => true], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du code : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la vérification du code',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Créer un nouveau secteur
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => [
                    'required',
                    'string',
                    'max:20',
                    function ($attribute, $value, $fail) use ($request) {
                        if (Secteur::where('code', $value)
                            ->where('standardisation_ar', $request->standardisation_ar)
                            ->whereNull('deleted_at')
                            ->exists()
                        ) {
                            $fail("Ce code est déjà utilisé pour un secteur de standardisation {$request->standardisation_ar}.");
                        }
                    },
                ],
                'nom_fr' => 'nullable|string|max:255',
                'nom_ar' => 'required|string|max:255',
                'standardisation_fr' => 'nullable|string|max:255',
                'standardisation_ar' => 'required|in:مقيس,غير مقيس',
                'statut' => 'nullable|in:Actif,Inactif',
                'date_creation' => 'nullable|date',
                'date_annulation' => 'nullable|date|after_or_equal:date_creation',
                'observation' => 'nullable|string|max:1000',
            ]);

            // Mapping pour standardisation_fr
            $validated['standardisation_fr'] = $validated['standardisation_ar'] === 'مقيس' ? 'Standardisé' : 'Non Standardisé';

            $secteur = Secteur::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Secteur créé',
                'secteur' => $secteur,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du secteur : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la création du secteur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Mettre à jour un secteur
    public function update(Request $request, $id)
    {
        try {
            $secteur = Secteur::find($id);

            if (! $secteur) {
                return response()->json(['message' => 'Secteur introuvable'], 404);
            }

            $validated = $request->validate([
                'code' => [
                    'required',
                    'string',
                    'max:20',
                    function ($attribute, $value, $fail) use ($id, $request, $secteur) {
                        // Skip uniqueness check if code and standardisation_ar are unchanged
                        if ($secteur->code === $value && $secteur->standardisation_ar === $request->standardisation_ar) {
                            return;
                        }
                        if (Secteur::where('code', $value)
                            ->where('standardisation_ar', $request->standardisation_ar)
                            ->whereNull('deleted_at')
                            ->where('id', '!=', $id)
                            ->exists()
                        ) {
                            $fail("Ce code est déjà utilisé pour un secteur de standardisation {$request->standardisation_ar}.");
                        }
                    },
                ],
                'nom_fr' => 'nullable|string|max:255',
                'nom_ar' => 'required|string|max:255',
                'standardisation_fr' => 'nullable|string|max:255',
                'standardisation_ar' => 'required|in:مقيس,غير مقيس',
                'statut' => 'nullable|in:Actif,Inactif',
                'date_creation' => 'nullable|date',
                'date_annulation' => 'nullable|date|after_or_equal:date_creation',
                'observation' => 'nullable|string|max:1000',
            ]);

            // Mapping pour standardisation_fr
            $validated['standardisation_fr'] = $validated['standardisation_ar'] === 'مقيس' ? 'Standardisé' : 'Non Standardisé';

            $secteur->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Secteur mis à jour',
                'secteur' => $secteur,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du secteur : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la mise à jour du secteur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Supprimer un secteur (soft delete)
    public function destroy(Request $request, $id)
    {
        try {
            $this->verifyDeletePassword($request);
            $secteur = Secteur::find($id);
            if (! $secteur) {
                return response()->json(['message' => 'Secteur introuvable'], 404);
            }
            $secteur->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Secteur supprimé',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du secteur : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la suppression du secteur',
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Supprimer les lignes sélectionnées
    public function deleteSelected(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:secteurs,id',
            ]);

            $this->verifyDeletePassword($request);

            Secteur::whereIn('id', $request->ids)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Secteurs supprimés',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression des secteurs : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la suppression des secteurs',
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Lister les secteurs supprimés (soft delete)
    public function trashed()
    {
        try {
            $secteurs = Secteur::onlyTrashed()->get();

            return response()->json($secteurs, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des secteurs supprimés : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la récupération des secteurs supprimés',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Restaurer un secteur supprimé
    public function restore($id)
    {
        try {
            // Valider que l'ID est un entier
            if (! is_numeric($id) || (int) $id <= 0) {
                Log::warning('ID invalide pour la restauration du secteur', ['id' => $id]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'ID de secteur invalide',
                ], 400);
            }

            $secteur = Secteur::onlyTrashed()->find($id);
            if (! $secteur) {
                return response()->json(['message' => 'Secteur introuvable'], 404);
            }
            $secteur->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Secteur restauré',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la restauration du secteur : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la restauration du secteur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function standardises()
    {
        try {
            $secteurs = Secteur::where('standardisation_ar', 'مقيس')
                ->whereNull('deleted_at')
                ->get();

            Log::info('Secteurs standardisés récupérés', ['count' => $secteurs->count()]);

            return response()->json($secteurs, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des secteurs standardisés : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des secteurs standardisés',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function nonStandardises()
    {
        try {
            $secteurs = Secteur::where('standardisation_ar', 'غير مقيس')
                ->whereNull('deleted_at')
                ->get();

            Log::info('Secteurs non standardisés récupérés', ['count' => $secteurs->count()]);

            return response()->json($secteurs, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des secteurs non standardisés : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des secteurs non standardisés',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Dans SecteurController.php
    public function getByCode($code)
    {
        try {
            $secteur = Secteur::where('code', $code)->whereNull('deleted_at')->firstOrFail();

            return response()->json($secteur, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du secteur par code: '.$e->getMessage(), ['code' => $code]);

            return response()->json(['error' => 'Secteur non trouvé.'], 404);
        }
    }
}
