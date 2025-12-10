<?php

namespace App\Http\Controllers\Formations;

use App\Http\Controllers\Controller;
use App\Models\Formations\Theme;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function index()
    {
        $themes = Theme::orderBy('ordre')->get();

        return response()->json($themes, 200);
    }

    public function show($id)
    {
        $theme = Theme::findOrFail($id);

        return response()->json($theme, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'                  => 'nullable|string|max:50',
            'nom_fr'                => 'nullable|string|max:255',
            'nom_ar'                => 'nullable|string|max:255',
            'certificat_fr'         => 'nullable|string|max:255',
            'certificat_ar'         => 'nullable|string|max:255',
            'criteres_admission_fr' => 'nullable|array',
            'criteres_admission_ar' => 'nullable|array',
            'description_fr'        => 'nullable|string',
            'description_ar'        => 'nullable|string',
            'statut'                => 'nullable|in:Actif,Inactif',
            'ordre'                 => 'nullable|integer',
        ]);

        $theme = Theme::create($data);

        return response()->json($theme, 201);
    }

    public function update(Request $request, $id)
    {
        $theme = Theme::findOrFail($id);

        $data = $request->validate([
            'code'                  => 'nullable|string|max:50',
            'nom_fr'                => 'nullable|string|max:255',
            'nom_ar'                => 'nullable|string|max:255',
            'certificat_fr'         => 'nullable|string|max:255',
            'certificat_ar'         => 'nullable|string|max:255',
            'criteres_admission_fr' => 'nullable|array',
            'criteres_admission_ar' => 'nullable|array',
            'description_fr'        => 'nullable|string',
            'description_ar'        => 'nullable|string',
            'statut'                => 'nullable|in:Actif,Inactif',
            'ordre'                 => 'nullable|integer',
        ]);

        $theme->update($data);

        return response()->json($theme, 200);
    }

    public function destroy($id)
    {
        $theme = Theme::findOrFail($id);
        $theme->delete();

        return response()->json(['message' => 'Thème supprimé avec succès'], 200);
    }
}
