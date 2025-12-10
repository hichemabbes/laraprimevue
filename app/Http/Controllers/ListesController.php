<?php

namespace App\Http\Controllers;

use App\Models\Liste;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ListesController extends Controller
{
    /**
     * Récupérer toutes les listes.
     */
    public function index(): JsonResponse
    {
        try {
            Log::info('Requête reçue pour /listes', ['user_id' => auth()->id()]);
            $listes = Liste::withTrashed()->get()->map(function ($liste) {
                $liste->options = $liste->options ?: [];

                return $liste;
            });

            Log::info('Listes récupérées avec succès.', ['count' => $listes->count()]);

            return response()->json($listes);
        } catch (\Exception $e) {
            Log::error("Erreur dans ListesController::index : {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erreur serveur lors de la récupération des listes'], 500);
        }
    }

    /**
     * Afficher une liste spécifique.
     *
     * @param  int  $id
     */
    public function show($id): JsonResponse
    {
        try {
            $liste = Liste::withTrashed()->find($id);
            if (! $liste) {
                Log::warning('Liste non trouvée.', ['id' => $id]);

                return response()->json(['error' => 'Liste non trouvée'], 404);
            }

            $liste->options = $liste->options ?: [];
            Log::info('Liste récupérée avec succès.', ['id' => $liste->id, 'nom_fr' => $liste->nom_fr]);

            return response()->json($liste);
        } catch (\Exception $e) {
            Log::error("Erreur dans ListesController::show : {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
                'id' => $id,
            ]);

            return response()->json(['error' => 'Erreur serveur lors de la récupération de la liste'], 500);
        }
    }

    /**
     * Créer une nouvelle liste.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom_fr' => 'required|string|max:255',
                'nom_ar' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'icone' => 'nullable|string|max:255',
                'couleur' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'fond' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'ordre' => 'required|integer|min:0',
                'statut' => 'required|in:Actif,Inactif',
                'options' => 'nullable|array',
                'options.*.nom_fr' => 'required_with:options|string|max:255',
                'options.*.nom_ar' => 'required_with:options|string|max:255',
                'options.*.valeur' => 'required_with:options|string|max:255|regex:/^[a-zA-Z0-9_-]+$/',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation échouée lors de la création de la liste.', ['errors' => $validator->errors()]);

                return response()->json(['error' => $validator->errors()], 422);
            }

            // Vérifier l'unicité des valeurs dans les options
            if ($request->has('options')) {
                $valeurs = array_column($request->input('options'), 'valeur');
                if (count($valeurs) !== count(array_unique($valeurs))) {
                    Log::warning('Valeurs en double détectées dans les options.');

                    return response()->json(['error' => 'Les valeurs des options doivent être uniques'], 422);
                }
            }

            $liste = Liste::create($request->all());
            Log::info('Liste créée avec succès.', ['id' => $liste->id, 'nom_fr' => $liste->nom_fr]);

            return response()->json($liste, 201);
        } catch (\Exception $e) {
            Log::error("Erreur dans ListesController::store : {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);

            return response()->json(['error' => 'Erreur serveur lors de la création de la liste'], 500);
        }
    }

    /**
     * Mettre à jour une liste existante.
     *
     * @param  int  $id
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $liste = Liste::withTrashed()->find($id);
            if (! $liste) {
                Log::warning('Liste non trouvée pour mise à jour.', ['id' => $id]);

                return response()->json(['error' => 'Liste non trouvée'], 404);
            }

            $validator = Validator::make($request->all(), [
                'nom_fr' => 'required|string|max:255',
                'nom_ar' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'icone' => 'nullable|string|max:255',
                'couleur' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'fond' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'ordre' => 'required|integer|min:0',
                'statut' => 'required|in:Actif,Inactif',
                'options' => 'nullable|array',
                'options.*.nom_fr' => 'required_with:options|string|max:255',
                'options.*.nom_ar' => 'required_with:options|string|max:255',
                'options.*.valeur' => 'required_with:options|string|max:255|regex:/^[a-zA-Z0-9_-]+$/',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation échouée lors de la mise à jour de la liste.', ['errors' => $validator->errors()]);

                return response()->json(['error' => $validator->errors()], 422);
            }

            // Vérifier l'unicité des valeurs dans les options
            if ($request->has('options')) {
                $valeurs = array_column($request->input('options'), 'valeur');
                if (count($valeurs) !== count(array_unique($valeurs))) {
                    Log::warning('Valeurs en double détectées dans les options.', ['id' => $id]);

                    return response()->json(['error' => 'Les valeurs des options doivent être uniques'], 422);
                }
            }

            $liste->update($request->all());
            Log::info('Liste mise à jour avec succès.', ['id' => $liste->id, 'nom_fr' => $liste->nom_fr]);

            return response()->json($liste);
        } catch (\Exception $e) {
            Log::error("Erreur dans ListesController::update : {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
                'id' => $id,
                'data' => $request->all(),
            ]);

            return response()->json(['error' => 'Erreur serveur lors de la mise à jour de la liste'], 500);
        }
    }

    /**
     * Récupérer les options d'une liste spécifique.
     *
     * @param  int  $id
     */
    public function getListe($id): JsonResponse
    {
        try {
            $liste = Liste::where('statut', 'Actif')->find($id);
            if (! $liste) {
                Log::warning('Liste non trouvée ou inactive.', ['id' => $id]);

                return response()->json(['error' => 'Liste non trouvée ou inactive'], 404);
            }

            $options = $liste->options ?: [];
            Log::info('Options récupérées avec succès pour la liste.', ['id' => $id, 'nom_fr' => $liste->nom_fr]);

            return response()->json($options);
        } catch (\Exception $e) {
            Log::error("Erreur dans ListesController::getListe : {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
                'id' => $id,
            ]);

            return response()->json(['error' => 'Erreur serveur lors de la récupération des options'], 500);
        }
    }

    /**
     * Supprimer une liste (suppression douce).
     *
     * @param  int  $id
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $liste = Liste::withTrashed()->find($id);
            if (! $liste) {
                Log::warning('Liste non trouvée pour suppression.', ['id' => $id]);

                return response()->json(['error' => 'Liste non trouvée'], 404);
            }

            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation échouée pour le mot de passe.', ['errors' => $validator->errors()]);

                return response()->json(['error' => $validator->errors()], 422);
            }

            // Vérification du mot de passe de l'utilisateur authentifié
            if (! auth('sanctum')->user() || ! Hash::check($request->password, auth('sanctum')->user()->password)) {
                Log::warning('Mot de passe incorrect pour la suppression.', ['id' => $id]);

                return response()->json(['error' => 'Mot de passe incorrect'], 403);
            }

            $liste->delete();
            Log::info('Liste supprimée avec succès.', ['id' => $id, 'nom_fr' => $liste->nom_fr]);

            return response()->json(['message' => 'Liste supprimée avec succès']);
        } catch (\Exception $e) {
            Log::error("Erreur dans ListesController::destroy : {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
                'id' => $id,
            ]);

            return response()->json(['error' => 'Erreur serveur lors de la suppression de la liste'], 500);
        }
    }

    /**
     * Récupérer les options des listes spécifiées par nom (méthode héritée).
     */
    public function options(Request $request): JsonResponse
    {
        try {
            $lists = $request->query('lists', '');
            $listNames = array_filter(explode(',', $lists));

            if (empty($listNames)) {
                Log::info('Aucune liste demandée dans la requête.');

                return response()->json([]);
            }

            $options = [];
            foreach ($listNames as $listName) {
                $listName = trim($listName);
                Log::info("Récupération des options pour la liste : {$listName}");

                $liste = Liste::where('nom_fr', $listName)
                    ->where('statut', 'Actif')
                    ->first();

                if (! $liste) {
                    Log::warning("Liste '{$listName}' non trouvée dans la base de données.");
                    $options[$listName] = [];

                    continue;
                }

                $options[$listName] = $liste->options ?: [];
            }

            Log::info('Options récupérées avec succès.', ['lists' => array_keys($options)]);

            return response()->json($options);
        } catch (\Exception $e) {
            Log::error("Erreur dans ListesController::options : {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
                'lists' => $request->query('lists', ''),
            ]);

            return response()->json(['error' => 'Erreur serveur lors de la récupération des options'], 500);
        }
    }
}
