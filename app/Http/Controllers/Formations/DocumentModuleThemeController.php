<?php

namespace App\Http\Controllers\Formations;

use App\Http\Controllers\Controller;
use App\Models\Formations\DocumentModuleTheme;
use Illuminate\Http\Request;

class DocumentModuleThemeController extends Controller
{
    public function index()
    {
        $docs = DocumentModuleTheme::with('moduleTheme')->get();

        return response()->json($docs, 200);
    }

    public function show($id)
    {
        $doc = DocumentModuleTheme::with('moduleTheme')->findOrFail($id);

        return response()->json($doc, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'module_theme_id'   => 'required|exists:modules_themes,id',
            'titre_fr'          => 'nullable|string|max:255',
            'titre_ar'          => 'nullable|string|max:255',
            'type_document_fr'  => 'nullable|string|max:255',
            'type_document_ar'  => 'nullable|string|max:255',
            'fichier'           => 'nullable|string',
            'statut'            => 'nullable|string|max:50',
            'description_fr'    => 'nullable|string',
            'description_ar'    => 'nullable|string',
            'observation_fr'    => 'nullable|string',
            'observation_ar'    => 'nullable|string',
        ]);

        $doc = DocumentModuleTheme::create($data);

        return response()->json($doc, 201);
    }

    public function update(Request $request, $id)
    {
        $doc = DocumentModuleTheme::findOrFail($id);

        $data = $request->validate([
            'module_theme_id'   => 'nullable|exists:modules_themes,id',
            'titre_fr'          => 'nullable|string|max:255',
            'titre_ar'          => 'nullable|string|max:255',
            'type_document_fr'  => 'nullable|string|max:255',
            'type_document_ar'  => 'nullable|string|max:255',
            'fichier'           => 'nullable|string',
            'statut'            => 'nullable|string|max:50',
            'description_fr'    => 'nullable|string',
            'description_ar'    => 'nullable|string',
            'observation_fr'    => 'nullable|string',
            'observation_ar'    => 'nullable|string',
        ]);

        $doc->update($data);

        return response()->json($doc, 200);
    }

    public function destroy($id)
    {
        $doc = DocumentModuleTheme::findOrFail($id);
        $doc->delete();

        return response()->json(['message' => 'Document module thème supprimé avec succès'], 200);
    }
}
