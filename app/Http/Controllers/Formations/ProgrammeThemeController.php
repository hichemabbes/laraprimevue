<?php

namespace App\Http\Controllers\Formations;

use App\Http\Controllers\Controller;
use App\Models\Formations\ProgrammeTheme;
use Illuminate\Http\Request;

class ProgrammeThemeController extends Controller
{
    public function index()
    {
        $programmes = ProgrammeTheme::with('theme')->get();

        return response()->json($programmes, 200);
    }

    public function show($id)
    {
        $programme = ProgrammeTheme::with(['theme', 'modulesThemes', 'documentsProgrammesThemes'])->findOrFail($id);

        return response()->json($programme, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'theme_id'       => 'nullable|exists:themes,id',
            'version'        => 'nullable|string|max:100',
            'date_debut'     => 'nullable|date',
            'date_fin'       => 'nullable|date|after_or_equal:date_debut',
            'description_fr' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'observation_fr' => 'nullable|string',
            'observation_ar' => 'nullable|string',
            'statut'         => 'nullable|string|max:50',
        ]);

        $programme = ProgrammeTheme::create($data);

        return response()->json($programme, 201);
    }

    public function update(Request $request, $id)
    {
        $programme = ProgrammeTheme::findOrFail($id);

        $data = $request->validate([
            'theme_id'       => 'nullable|exists:themes,id',
            'version'        => 'nullable|string|max:100',
            'date_debut'     => 'nullable|date',
            'date_fin'       => 'nullable|date|after_or_equal:date_debut',
            'description_fr' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'observation_fr' => 'nullable|string',
            'observation_ar' => 'nullable|string',
            'statut'         => 'nullable|string|max:50',
        ]);

        $programme->update($data);

        return response()->json($programme, 200);
    }

    public function destroy($id)
    {
        $programme = ProgrammeTheme::findOrFail($id);
        $programme->delete();

        return response()->json(['message' => 'Programme thème supprimé avec succès'], 200);
    }
}
