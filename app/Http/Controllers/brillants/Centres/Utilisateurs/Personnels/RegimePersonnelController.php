<?php

namespace App\Http\Controllers\brillants\Centres\Utilisateurs\Personnels;

use App\Http\Controllers\Controller;
use App\Models\Centre\Utilisateurs\Personnels\RegimePersonnel;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RegimePersonnelController extends Controller
{
    protected $centreId;
    protected $isCentreRole;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware(function ($request, $next) {
            $this->centreId = $request->header('X-Centre-Id');
            $this->isCentreRole = $request->header('X-Is-Centre-Role') == '1';

            if (!$this->centreId) {
                Log::error('Centre ID manquant dans les en-têtes.');
                return response()->json(['message' => 'Centre ID manquant.'], 400);
            }

            if (!Auth::check()) {
                Log::error('Utilisateur non authentifié.');
                return response()->json(['message' => 'Non authentifié.'], 401);
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        try {
            $personnelId = $request->query('personnel_id');

            if (!$personnelId) {
                Log::warning('Personnel ID manquant dans la requête.');
                return response()->json(['message' => 'Personnel ID requis.'], 400);
            }

            $personnel = PersonnelCentre::where('id', $personnelId)->first();

            if (!$personnel) {
                Log::warning('Personnel non trouvé pour ID: ' . $personnelId);
                return response()->json(['message' => 'Personnel non trouvé.'], 404);
            }

            $regimes = RegimePersonnel::with(['annee_formation', 'annee_administrative'])
                ->where('personnel_id', $personnelId)
                ->get();

            if ($regimes->isEmpty()) {
                Log::info('Aucun régime trouvé pour personnel_id: ' . $personnelId);
            }

            return response()->json($regimes, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des régimes: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur serveur.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $regime = RegimePersonnel::with(['annee_formation', 'annee_administrative'])
                ->where('id', $id)
                ->firstOrFail();

            return response()->json($regime, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du régime ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Régime non trouvé.'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'personnel_id' => 'required|exists:personnels_centres,id',
                'annee_formation_id' => 'nullable|exists:annees_formations,id',
                'annee_administrative_id' => 'nullable|exists:annees_administratives,id',
                'regime_horaire' => 'required|integer|min:0',
                'rabattement' => 'nullable|integer|min:0',
                'observation_fr' => 'nullable|string',
                'observation_ar' => 'nullable|string',
                'statut' => 'nullable|in:Actif,Inactif'
            ], [
                'annee_formation_id.exists' => 'L\'année de formation spécifiée n\'existe pas.',
                'annee_administrative_id.exists' => 'L\'année administrative spécifiée n\'existe pas.',
                'annee_formation_id.required_without' => 'L\'année de formation ou l\'année administrative doit être spécifiée.',
                'annee_administrative_id.required_without' => 'L\'année administrative ou l\'année de formation doit être spécifiée.'
            ]);

            if (!$request->has('annee_formation_id') && !$request->has('annee_administrative_id')) {
                return response()->json(['message' => 'L\'année de formation ou l\'année administrative doit être spécifiée.'], 422);
            }

            if ($request->has('annee_formation_id') && $request->has('annee_administrative_id')) {
                return response()->json(['message' => 'Seule l\'année de formation ou l\'année administrative peut être spécifiée, pas les deux.'], 422);
            }

            if ($validator->fails()) {
                return response()->json(['message' => 'Validation échouée.', 'errors' => $validator->errors()], 422);
            }

            $personnel = PersonnelCentre::where('id', $request->personnel_id)->first();

            if (!$personnel || !$personnel->user_centre_id) {
                Log::warning('Personnel non valide ou sans user_centre_id pour ID: ' . $request->personnel_id);
                return response()->json(['message' => 'Personnel non autorisé.'], 403);
            }

            $existingRegime = RegimePersonnel::where('personnel_id', $request->personnel_id)
                ->where(function ($query) use ($request) {
                    if ($request->annee_formation_id) {
                        $query->where('annee_formation_id', $request->annee_formation_id);
                    }
                    if ($request->annee_administrative_id) {
                        $query->where('annee_administrative_id', $request->annee_administrative_id);
                    }
                })
                ->whereNull('deleted_at')
                ->exists();

            if ($existingRegime) {
                return response()->json(['message' => 'Un régime existe déjà pour ce personnel et cette année.'], 422);
            }

            $regime = RegimePersonnel::create($request->all());
            Log::info('Régime créé pour personnel_id: ' . $request->personnel_id);
            return response()->json($regime, 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du régime: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur serveur.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $regime = RegimePersonnel::where('id', $id)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'personnel_id' => 'required|exists:personnels_centres,id',
                'annee_formation_id' => 'nullable|exists:annees_formations,id',
                'annee_administrative_id' => 'nullable|exists:annees_administratives,id',
                'regime_horaire' => 'required|integer|min:0',
                'rabattement' => 'nullable|integer|min:0',
                'observation_fr' => 'nullable|string',
                'observation_ar' => 'nullable|string',
                'statut' => 'nullable|in:Actif,Inactif'
            ], [
                'annee_formation_id.exists' => 'L\'année de formation spécifiée n\'existe pas.',
                'annee_administrative_id.exists' => 'L\'année administrative spécifiée n\'existe pas.',
                'annee_formation_id.required_without' => 'L\'année de formation ou l\'année administrative doit être spécifiée.',
                'annee_administrative_id.required_without' => 'L\'année administrative ou l\'année de formation doit être spécifiée.'
            ]);

            if (!$request->has('annee_formation_id') && !$request->has('annee_administrative_id')) {
                return response()->json(['message' => 'L\'année de formation ou l\'année administrative doit être spécifiée.'], 422);
            }

            if ($request->has('annee_formation_id') && $request->has('annee_administrative_id')) {
                return response()->json(['message' => 'Seule l\'année de formation ou l\'année administrative peut être spécifiée, pas les deux.'], 422);
            }

            if ($validator->fails()) {
                return response()->json(['message' => 'Validation échouée.', 'errors' => $validator->errors()], 422);
            }

            $personnel = PersonnelCentre::where('id', $request->personnel_id)->first();

            if (!$personnel || !$personnel->user_centre_id) {
                Log::warning('Personnel non valide ou sans user_centre_id pour ID: ' . $request->personnel_id);
                return response()->json(['message' => 'Personnel non autorisé.'], 403);
            }

            $existingRegime = RegimePersonnel::where('personnel_id', $request->personnel_id)
                ->where(function ($query) use ($request) {
                    if ($request->annee_formation_id) {
                        $query->where('annee_formation_id', $request->annee_formation_id);
                    }
                    if ($request->annee_administrative_id) {
                        $query->where('annee_administrative_id', $request->annee_administrative_id);
                    }
                })
                ->where('id', '!=', $id)
                ->whereNull('deleted_at')
                ->exists();

            if ($existingRegime) {
                return response()->json(['message' => 'Un régime existe déjà pour ce personnel et cette année.'], 422);
            }

            $regime->update($request->all());
            Log::info('Régime mis à jour pour ID: ' . $id);
            return response()->json($regime, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du régime ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur serveur.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $regime = RegimePersonnel::where('id', $id)->firstOrFail();
            $regime->delete();
            Log::info('Régime supprimé pour ID: ' . $id);
            return response()->json(['message' => 'Régime supprimé.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du régime ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur serveur.'], 500);
        }
    }
}
