<?php

namespace App\Http\Controllers\Formations;

use App\Http\Controllers\Controller;
use App\Models\Formations\ModuleTheme;
use Illuminate\Http\Request;

class ModuleThemeController extends Controller
{
    public function index()
    {
        $modules = ModuleTheme::with('programmeTheme')->get();

        return response()->json($modules, 200);
    }

    public function show($id)
    {
        $module = ModuleTheme::with(['programmeTheme', 'documentsModulesThemes'])->findOrFail($id);

        return response()->json($module, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'programme_theme_id' => 'nullable|exists:programmes_themes,id',
            'code'               => 'nullable|string|max:100',
            'titre_module_fr'    => 'nullable|string',
            'titre_module_ar'    => 'nullable|string',
            'type_module_fr'     => 'nullable|string|max:255',
            'type_module_ar'     => 'nullable|string|max:255',
            'heures_theoriques'  => 'nullable|integer',
            'heures_pratiques'   => 'nullable|integer',
            'heures_evaluation'  => 'nullable|integer',
            'description_fr'     => 'nullable|string',
            'description_ar'     => 'nullable|string',
            'observation_fr'     => 'nullable|string',
            'observation_ar'     => 'nullable|string',
            'statut'             => 'nullable|string|max:50',
        ]);

        $module = ModuleTheme::create($data);

        return response()->json($module, 201);
    }

    public function update(Request $request, $id)
    {
        $module = ModuleTheme::findOrFail($id);

        $data = $request->validate([
            'programme_theme_id' => 'nullable|exists:programmes_themes,id',
            'code'               => 'nullable|string|max:100',
            'titre_module_fr'    => 'nullable|string',
            'titre_module_ar'    => 'nullable|string',
            'type_module_fr'     => 'nullable|string|max:255',
            'type_module_ar'     => 'nullable|string|max:255',
            'heures_theoriques'  => 'nullable|integer',
            'heures_pratiques'   => 'nullable|integer',
            'heures_evaluation'  => 'nullable|integer',
            'description_fr'     => 'nullable|string',
            'description_ar'     => 'nullable|string',
            'observation_fr'     => 'nullable|string',
            'observation_ar'     => 'nullable|string',
            'statut'             => 'nullable|string|max:50',
        ]);

        $module->update($data);

        return response()->json($module, 200);
    }

    public function destroy($id)
    {
        $module = ModuleTheme::findOrFail($id);
        $module->delete();

        return response()->json(['message' => 'Module thème supprimé avec succès'], 200);
    }
}
