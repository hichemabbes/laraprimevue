<?php

namespace App\Http\Controllers\brillants\ATFP\Specialites\SousSecteurs;

use App\Http\Controllers\Controller;
use App\Models\brillants\Specialites\Secteurs\Secteur;
use App\Models\brillants\Specialites\SousSecteurs\SousSecteur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SousSecteurController extends Controller
{
    public function index()
    {
        try {
            $sousSecteurs = SousSecteur::with('secteur')->get();
            Log::info('Sous-secteurs récupérés', ['count' => $sousSecteurs->count()]);

            return response()->json($sousSecteurs, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des sous-secteurs: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des sous-secteurs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $sousSecteur = SousSecteur::with('secteur')->find($id);
            if (! $sousSecteur) {
                Log::warning('Sous-secteur introuvable', ['id' => $id]);

                return response()->json(['message' => 'Sous-secteur introuvable'], 404);
            }

            return response()->json($sousSecteur, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du sous-secteur: '.$e->getMessage(), ['id' => $id]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération du sous-secteur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkCode(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|max:20',
                'secteur_id' => 'required|exists:secteurs,id',
                'id' => 'nullable|integer|exists:sous_secteurs,id',
            ]);

            $secteur = Secteur::find($request->secteur_id);
            if (! $secteur) {
                Log::warning('Secteur invalide pour vérification de code', ['secteur_id' => $request->secteur_id]);

                return response()->json(['isUnique' => false, 'message' => 'Secteur invalide.'], 400);
            }

            // Skip uniqueness check if editing the same record
            if ($request->id) {
                $sousSecteur = SousSecteur::find($request->id);
                if ($sousSecteur && $sousSecteur->code === $request->code && $sousSecteur->secteur_id === $request->secteur_id) {
                    return response()->json(['isUnique' => true], 200);
                }
            }

            $query = SousSecteur::where('code', $request->code)
                ->where('secteur_id', $request->secteur_id)
                ->whereNull('deleted_at');

            if ($request->id) {
                $query->where('id', '!=', $request->id);
            }

            if ($query->exists()) {
                return response()->json([
                    'isUnique' => false,
                    'message' => "Ce code est déjà utilisé pour un sous-secteur du secteur {$secteur->nom_ar}.",
                ], 200);
            }

            return response()->json(['isUnique' => true], 200);
        } catch (ValidationException $e) {
            Log::warning('Erreur de validation dans checkCode', ['errors' => $e->errors()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans checkCode: '.$e->getMessage(), ['request' => $request->all()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la vérification du code',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => [
                    'required',
                    'string',
                    'max:20',
                    function ($attribute, $value, $fail) use ($request) {
                        if (SousSecteur::where('code', $value)
                            ->where('secteur_id', $request->secteur_id)
                            ->whereNull('deleted_at')
                            ->exists()
                        ) {
                            $secteur = Secteur::find($request->secteur_id);
                            $fail("Ce code est déjà utilisé pour un sous-secteur du secteur {$secteur->nom_ar}.");
                        }
                    },
                ],
                'nom_fr' => 'nullable|string|max:100',
                'nom_ar' => 'required|string|max:100',
                'secteur_id' => 'required|exists:secteurs,id',
                'statut' => 'nullable|in:Actif,Inactif',
                'date_creation' => 'nullable|date',
                'date_annulation' => 'nullable|date|after_or_equal:date_creation',
                'observation' => 'nullable|string|max:1000',
            ]);

            $sousSecteur = SousSecteur::create($validated);
            Log::info('Sous-secteur créé', ['id' => $sousSecteur->id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Sous-secteur créé',
                'sousSecteur' => $sousSecteur,
            ], 201);
        } catch (ValidationException $e) {
            Log::warning('Erreur de validation dans store', ['errors' => $e->errors()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans store: '.$e->getMessage(), ['request' => $request->all()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création du sous-secteur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $sousSecteur = SousSecteur::find($id);
            if (! $sousSecteur) {
                Log::warning('Sous-secteur introuvable pour mise à jour', ['id' => $id]);

                return response()->json(['message' => 'Sous-secteur introuvable'], 404);
            }

            $validated = $request->validate([
                'code' => [
                    'required',
                    'string',
                    'max:20',
                    function ($attribute, $value, $fail) use ($id, $request, $sousSecteur) {
                        // Skip uniqueness check if code and secteur_id are unchanged
                        if ($sousSecteur->code === $value && $sousSecteur->secteur_id === $request->secteur_id) {
                            return;
                        }
                        if (SousSecteur::where('code', $value)
                            ->where('secteur_id', $request->secteur_id)
                            ->whereNull('deleted_at')
                            ->where('id', '!=', $id)
                            ->exists()
                        ) {
                            $secteur = Secteur::find($request->secteur_id);
                            $fail("Ce code est déjà utilisé pour un sous-secteur du secteur {$secteur->nom_ar}.");
                        }
                    },
                ],
                'nom_fr' => 'nullable|string|max:100',
                'nom_ar' => 'required|string|max:100',
                'secteur_id' => 'required|exists:secteurs,id',
                'statut' => 'nullable|in:Actif,Inactif',
                'date_creation' => 'nullable|date',
                'date_annulation' => 'nullable|date|after_or_equal:date_creation',
                'observation' => 'nullable|string|max:1000',
            ]);

            $sousSecteur->update($validated);
            Log::info('Sous-secteur mis à jour', ['id' => $id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Sous-secteur mis à jour',
                'sousSecteur' => $sousSecteur,
            ], 200);
        } catch (ValidationException $e) {
            Log::warning('Erreur de validation dans update', ['errors' => $e->errors()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans update: '.$e->getMessage(), ['id' => $id, 'request' => $request->all()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du sous-secteur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $sousSecteur = SousSecteur::find($id);
            if (! $sousSecteur) {
                Log::warning('Sous-secteur introuvable pour suppression', ['id' => $id]);

                return response()->json(['message' => 'Sous-secteur introuvable'], 404);
            }
            $sousSecteur->delete();
            Log::info('Sous-secteur supprimé', ['id' => $id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Sous-secteur supprimé',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur dans destroy: '.$e->getMessage(), ['id' => $id]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression du sous-secteur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteSelected(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:sous_secteurs,id',
            ]);

            SousSecteur::whereIn('id', $request->ids)->delete();
            Log::info('Sous-secteurs supprimés', ['ids' => $request->ids]);

            return response()->json([
                'status' => 'success',
                'message' => 'Sous-secteurs supprimés',
            ], 200);
        } catch (ValidationException $e) {
            Log::warning('Erreur de validation dans deleteSelected', ['errors' => $e->errors()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans deleteSelected: '.$e->getMessage(), ['ids' => $request->ids]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression des sous-secteurs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function trashed()
    {
        try {
            $sousSecteurs = SousSecteur::onlyTrashed()->with('secteur')->get();
            Log::info('Sous-secteurs supprimés récupérés', ['count' => $sousSecteurs->count()]);

            return response()->json($sousSecteurs, 200);
        } catch (\Exception $e) {
            Log::error('Erreur dans trashed: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des sous-secteurs supprimés',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $sousSecteur = SousSecteur::onlyTrashed()->find($id);
            if (! $sousSecteur) {
                Log::warning('Sous-secteur introuvable pour restauration', ['id' => $id]);

                return response()->json(['message' => 'Sous-secteur introuvable'], 404);
            }
            $sousSecteur->restore();
            Log::info('Sous-secteur restauré', ['id' => $id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Sous-secteur restauré',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur dans restore: '.$e->getMessage(), ['id' => $id]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la restauration du sous-secteur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getByCode($code)
    {
        try {
            $sousSecteur = SousSecteur::where('code', $code)->whereNull('deleted_at')->firstOrFail();

            return response()->json($sousSecteur, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du sous-secteur par code: '.$e->getMessage(), ['code' => $code]);

            return response()->json(['error' => 'Sous-secteur non trouvé.'], 404);
        }
    }
}
