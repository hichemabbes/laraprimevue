<?php

namespace App\Http\Controllers\Formations;

use App\Http\Controllers\Controller;
use App\Models\Formations\Formation;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function index()
    {
        $formations = Formation::with(['session', 'specialite', 'theme'])->get();

        return response()->json($formations, 200);
    }

    public function show($id)
    {
        $formation = Formation::with(['session', 'specialite', 'theme'])->findOrFail($id);

        return response()->json($formation, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'session_id'   => 'nullable|exists:sessions,id',
            'specialite_id'=> 'nullable|exists:specialites,id',
            'theme_id'     => 'required|exists:themes,id',
            'code'         => 'nullable|string|max:20',
            'nom_fr'       => 'nullable|string|max:255',
            'nom_ar'       => 'nullable|string|max:255',
            'cible_fr'     => 'nullable|string|max:255',
            'cible_ar'     => 'nullable|string|max:255',
            'statut'       => 'nullable|string|max:50',
            'observation_fr'=> 'nullable|string',
            'observation_ar'=> 'nullable|string',
        ]);

        $formation = Formation::create($data);

        return response()->json($formation, 201);
    }

    public function update(Request $request, $id)
    {
        $formation = Formation::findOrFail($id);

        $data = $request->validate([
            'session_id'   => 'nullable|exists:sessions,id',
            'specialite_id'=> 'nullable|exists:specialites,id',
            'theme_id'     => 'nullable|exists:themes,id',
            'code'         => 'nullable|string|max:20',
            'nom_fr'       => 'nullable|string|max:255',
            'nom_ar'       => 'nullable|string|max:255',
            'cible_fr'     => 'nullable|string|max:255',
            'cible_ar'     => 'nullable|string|max:255',
            'statut'       => 'nullable|string|max:50',
            'observation_fr'=> 'nullable|string',
            'observation_ar'=> 'nullable|string',
        ]);

        $formation->update($data);

        return response()->json($formation, 200);
    }

    public function destroy($id)
    {
        $formation = Formation::findOrFail($id);
        $formation->delete();

        return response()->json(['message' => 'Formation supprimée avec succès'], 200);
    }
}
