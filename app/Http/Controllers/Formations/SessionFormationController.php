<?php

namespace App\Http\Controllers\Formations;

use App\Http\Controllers\Controller;
use App\Models\Formations\Session;
use Illuminate\Http\Request;

class SessionFormationController extends Controller
{
    public function index()
    {
        $sessions = Session::with('anneeFormation')->orderBy('date_debut')->get();

        return response()->json($sessions, 200);
    }

    public function show($id)
    {
        $session = Session::with('anneeFormation')->findOrFail($id);

        return response()->json($session, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'annee_formation_id' => 'required|exists:annees_formations,id',
            'code'               => 'nullable|string|max:50',
            'intitule_fr'        => 'nullable|string|max:255',
            'intitule_ar'        => 'nullable|string|max:255',
            'date_debut'         => 'nullable|date',
            'date_fin'           => 'nullable|date|after_or_equal:date_debut',
            'capacite_min'       => 'nullable|integer',
            'capacite_max'       => 'nullable|integer',
            'statut'             => 'nullable|in:Planifiee,Ouverte_inscription,En_cours,Terminee,Annulee',
            'observation_fr'     => 'nullable|string',
            'observation_ar'     => 'nullable|string',
        ]);

        $session = Session::create($data);

        return response()->json($session, 201);
    }

    public function update(Request $request, $id)
    {
        $session = Session::findOrFail($id);

        $data = $request->validate([
            'annee_formation_id' => 'nullable|exists:annees_formations,id',
            'code'               => 'nullable|string|max:50',
            'intitule_fr'        => 'nullable|string|max:255',
            'intitule_ar'        => 'nullable|string|max:255',
            'date_debut'         => 'nullable|date',
            'date_fin'           => 'nullable|date|after_or_equal:date_debut',
            'capacite_min'       => 'nullable|integer',
            'capacite_max'       => 'nullable|integer',
            'statut'             => 'nullable|in:Planifiee,Ouverte_inscription,En_cours,Terminee,Annulee',
            'observation_fr'     => 'nullable|string',
            'observation_ar'     => 'nullable|string',
        ]);

        $session->update($data);

        return response()->json($session, 200);
    }

    public function destroy($id)
    {
        $session = Session::findOrFail($id);
        $session->delete();

        return response()->json(['message' => 'Session supprimée avec succès'], 200);
    }
}
