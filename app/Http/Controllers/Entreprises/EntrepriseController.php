<?php

namespace App\Http\Controllers\Entreprises;

use App\Http\Controllers\Controller;
use App\Models\Entreprises\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EntrepriseController extends Controller
{
    public function index()
    {
        $entreprises = Entreprise::with(['secteur', 'tuteurs'])->get();

        return response()->json($entreprises);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code_entre' => 'required|string|max:50|unique:entreprises',
            'nom_fr' => 'required|string|max:100',
            'nom_ar' => 'required|string|max:100',
            'email_entr' => 'required|email|max:100',
            'secteur_id' => 'required|exists:secteurs,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $entreprise = Entreprise::create($request->all());

        return response()->json($entreprise->load('secteur', 'tuteurs'), 201);
    }

    public function update(Request $request, $id)
    {
        $entreprise = Entreprise::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code_entre' => 'required|string|max:50|unique:entreprises,code_entre,'.$id,
            'nom_fr' => 'required|string|max:100',
            'nom_ar' => 'required|string|max:100',
            'email_entr' => 'required|email|max:100',
            'secteur_id' => 'required|exists:secteurs,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $entreprise->update($request->all());

        return response()->json($entreprise->load('secteur', 'tuteurs'));
    }

    public function destroy($id)
    {
        $entreprise = Entreprise::findOrFail($id);
        $entreprise->delete();

        return response()->json(['message' => 'Entreprise supprimée avec succès']);
    }

    // À implémenter selon vos besoins pour l'import/export XLS
    public function importXls(Request $request)
    {
        // Logique d'importation XLS à implémenter
        return response()->json(['message' => 'Importation non implémentée']);
    }
}
