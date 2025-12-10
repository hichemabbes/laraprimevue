<?php

use App\Models\Categorie;
use App\Models\Liste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiController extends \App\Http\Controllers\Controller
{
    // Gestion des types de catégories
    public function getTypesCategories()
    {
        try {
            $types = Categorie::where('actif', true)->get();

            return response()->json($types);
        } catch (\Exception $e) {
            Log::error('Erreur dans getTypesCategories: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    public function createTypeCategorie(Request $request)
    {
        try {
            $request->validate([
                'nom' => 'required|string|max:255|unique:types_categories,nom',
                'description' => 'nullable|string|max:500',
                'actif' => 'boolean',
            ]);

            $type = Categorie::create($request->all());

            return response()->json(['status' => 'success', 'type' => $type], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans createTypeCategorie: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    public function updateTypeCategorie(Request $request, $id)
    {
        try {
            $request->validate([
                'nom' => 'required|string|max:255|unique:types_categories,nom,'.$id,
                'description' => 'nullable|string|max:500',
                'actif' => 'boolean',
            ]);

            $type = Categorie::findOrFail($id);
            $type->update($request->all());

            return response()->json(['status' => 'success', 'type' => $type]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans updateTypeCategorie: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    public function deleteTypeCategorie($id)
    {
        try {
            $type = Categorie::findOrFail($id);
            $type->delete();

            return response()->json(['status' => 'success', 'message' => 'Type de catégorie supprimé']);
        } catch (\Exception $e) {
            Log::error('Erreur dans deleteTypeCategorie: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    // Gestion des options des listes
    public function getOptionsListes($type, $centreId)
    {
        try {
            $typeCategorie = Categorie::where('nom', $type)->firstOrFail();

            $query = Liste::where('type_categorie_id', $typeCategorie->id)
                ->where(function ($query) use ($centreId) {
                    $query->where('centre_id', $centreId)
                        ->orWhere('centre_id', 0);
                });

            if (isset($_GET['actif'])) {
                $isActive = $_GET['actif'] === 'true';
                $query->where('actif', $isActive);
            }

            $options = $query->get()
                ->map(function ($item) {
                    $item->actif = (bool) $item->actif;
                    $item->datetime = date('d F Y - H:i', strtotime($item->created_at));

                    return $item;
                });

            return response()->json($options);
        } catch (\Exception $e) {
            Log::error('Erreur dans getOptionsListes: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    public function createOptionListe(Request $request)
    {
        try {
            $request->validate([
                'type_categorie_id' => 'required|exists:types_categories,id',
                'nom' => 'required|string|max:255',
                'couleur' => 'nullable|string|max:20',
                'fond' => 'nullable|string|max:20',
                'ordre' => 'integer|min:0',
                'actif' => 'boolean',
                'centre_id' => 'integer|min:0',
            ]);

            $option = Liste::create($request->all());

            return response()->json(['status' => 'success', 'option' => $option], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans createOptionListe: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    public function updateOptionListe(Request $request, $id)
    {
        try {
            $request->validate([
                'type_categorie_id' => 'required|exists:types_categories,id',
                'nom' => 'required|string|max:255',
                'couleur' => 'nullable|string|max:20',
                'fond' => 'nullable|string|max:20',
                'ordre' => 'integer|min:0',
                'actif' => 'boolean',
                'centre_id' => 'integer|min:0',
            ]);

            $option = Liste::findOrFail($id);
            $option->update($request->all());

            return response()->json(['status' => 'success', 'option' => $option]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans updateOptionListe: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    public function deleteOptionListe($id)
    {
        try {
            $option = Liste::findOrFail($id);
            $option->delete();

            return response()->json(['status' => 'success', 'message' => 'Option supprimée']);
        } catch (\Exception $e) {
            Log::error('Erreur dans deleteOptionListe: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    public function activerOptionListe(Request $request)
    {
        try {
            $request->validate(['id' => 'required|integer', 'actif' => 'required|boolean']);
            $option = Liste::findOrFail($request->id);
            $option->update(['actif' => $request->actif]);

            return response()->json(['status' => 'success', 'option' => $option]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans activerOptionListe: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }

    public function supprimerToutOptionsListes(Request $request)
    {
        try {
            $request->validate(['ids' => 'required|array']);
            Liste::whereIn('id', $request->ids)->delete();

            return response()->json(['status' => 'success', 'message' => 'Options supprimées']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans supprimerToutOptionsListes: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Erreur serveur: '.$e->getMessage()], 500);
        }
    }
}
