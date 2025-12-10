<?php

namespace App\Http\Controllers\Formations;

use App\Http\Controllers\Controller;
use App\Models\Formations\Specialite;
use Illuminate\Http\Request;

class SpecialiteController extends Controller
{
    public function index()
    {
        $specialites = Specialite::orderBy('ordre')->orderBy('nom_fr')->get();
        return response()->json($specialites);
    }

    public function show($id)
    {
        $specialite = Specialite::findOrFail($id);
        return response()->json($specialite);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:30|unique:specialites,code',
            'nom_fr' => 'required|string|max:255',
            'nom_ar' => 'required|string|max:255',
            'niveau_fr' => 'nullable|string|max:255',
            'niveau_ar' => 'nullable|string|max:255',
            'diplome_fr' => 'nullable|string|max:255',
            'diplome_ar' => 'nullable|string|max:255',
            'duree_formation' => 'nullable|numeric|between:0,99.9',
            'heures_et' => 'nullable|integer|min:0',
            'heures_eg' => 'nullable|integer|min:0',
            'heures_total' => 'nullable|integer|min:0',
            'est_homologue' => 'nullable|boolean',
            'reference_homologation' => 'nullable|string|max:255',
            'description_fr' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'criteres_admission_fr' => 'nullable|array',
            'criteres_admission_ar' => 'nullable|array',
            'statut' => 'required|in:Actif,Inactif',
            'ordre' => 'nullable|integer',
        ]);

        $specialite = Specialite::create($validated);

        return response()->json($specialite, 201);
    }

    public function update(Request $request, $id)
    {
        $specialite = Specialite::findOrFail($id);

        $validated = $request->validate([
            'code' => 'nullable|string|max:30|unique:specialites,code,' . $id,
            'nom_fr' => 'required|string|max:255',
            'nom_ar' => 'required|string|max:255',
            'niveau_fr' => 'nullable|string|max:255',
            'niveau_ar' => 'nullable|string|max:255',
            'diplome_fr' => 'nullable|string|max:255',
            'diplome_ar' => 'nullable|string|max:255',
            'duree_formation' => 'nullable|numeric|between:0,99.9',
            'heures_et' => 'nullable|integer|min:0',
            'heures_eg' => 'nullable|integer|min:0',
            'heures_total' => 'nullable|integer|min:0',
            'est_homologue' => 'nullable|boolean',
            'reference_homologation' => 'nullable|string|max:255',
            'description_fr' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'criteres_admission_fr' => 'nullable|array',
            'criteres_admission_ar' => 'nullable|array',
            'statut' => 'required|in:Actif,Inactif',
            'ordre' => 'nullable|integer',
        ]);

        $specialite->update($validated);

        return response()->json($specialite);
    }

    public function destroy($id)
    {
        $specialite = Specialite::findOrFail($id);
        $specialite->delete();

        return response()->json(['message' => 'Spécialité supprimée avec succès']);
    }
}
