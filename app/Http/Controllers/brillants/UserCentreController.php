<?php
namespace App\Http\Controllers\brillants;

use App\Http\Controllers\Controller;
use App\Models\Utilisateurs\UserCentre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserCentreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $search = $request->query('search');
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);

            $userCentres = UserCentre::with(['user', 'centre', 'updatedBy'])
                ->when($search, function ($query, $search) {
                    $query->whereHas('user', function ($q) use ($search) {
                        $q->where('nom_fr', 'like', "%{$search}%")
                            ->orWhere('prenom_fr', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('centre', function ($q) use ($search) {
                        $q->where('nom_fr', 'like', "%{$search}%");
                    });
                })
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'data' => $userCentres->items(),
                'total' => $userCentres->total(),
                'current_page' => $userCentres->currentPage(),
                'per_page' => $userCentres->perPage(),
                'message' => 'Utilisateurs des centres récupérés avec succès.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des utilisateurs des centres:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des utilisateurs des centres.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validCentreStatuts = ['Centre officiel', 'Centre secondaire'];
            $validTypeMouvements = ['Affectation initiale', 'Transfert', 'Détachement'];
            $validStatuts = ['Actif', 'Inactif', 'Transféré'];

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'centre_id' => 'required|exists:centres,id',
                'centre_statut_fr' => ['required', Rule::in($validCentreStatuts)],
                'centre_statut_ar' => 'required|string|max:255',
                'type_mouvement_fr' => ['required', Rule::in($validTypeMouvements)],
                'type_mouvement_ar' => 'required|string|max:255',
                'date_debut' => 'required|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'statut' => ['required', Rule::in($validStatuts)],
                'observation_fr' => 'nullable|string',
                'observation_ar' => 'nullable|string',
                'updated_by' => 'nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation échouée',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $userCentre = UserCentre::create($validator->validated());

            return response()->json([
                'data' => $userCentre->load(['user', 'centre', 'updatedBy']),
                'message' => 'Utilisateur du centre créé avec succès.'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'utilisateur du centre:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création de l\'utilisateur du centre.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $userCentre = UserCentre::with(['user', 'centre', 'updatedBy'])->findOrFail($id);
            return response()->json([
                'data' => $userCentre,
                'message' => 'Utilisateur du centre récupéré avec succès.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'utilisateur du centre:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Utilisateur du centre non trouvé.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $userCentre = UserCentre::findOrFail($id);

            $validCentreStatuts = ['Centre officiel', 'Centre secondaire'];
            $validTypeMouvements = ['Affectation initiale', 'Transfert', 'Détachement'];
            $validStatuts = ['Actif', 'Inactif', 'Transféré'];

            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|required|exists:users,id',
                'centre_id' => 'sometimes|required|exists:centres,id',
                'centre_statut_fr' => ['sometimes', 'required', Rule::in($validCentreStatuts)],
                'centre_statut_ar' => 'sometimes|required|string|max:255',
                'type_mouvement_fr' => ['sometimes', 'required', Rule::in($validTypeMouvements)],
                'type_mouvement_ar' => 'sometimes|required|string|max:255',
                'date_debut' => 'sometimes|required|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'statut' => ['sometimes', 'required', Rule::in($validStatuts)],
                'observation_fr' => 'sometimes|nullable|string',
                'observation_ar' => 'sometimes|nullable|string',
                'updated_by' => 'sometimes|nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation échouée',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $userCentre->update($validator->validated());

            return response()->json([
                'data' => $userCentre->load(['user', 'centre', 'updatedBy']),
                'message' => 'Utilisateur du centre mis à jour avec succès.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'utilisateur du centre:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour de l\'utilisateur du centre.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $userCentre = UserCentre::findOrFail($id);
            $userCentre->delete();

            return response()->json([
                'message' => 'Utilisateur du centre supprimé avec succès.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'utilisateur du centre:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Une erreur est survenue lors de la suppression de l\'utilisateur du centre.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
