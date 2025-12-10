<?php

namespace App\Http\Controllers\Formations;

use App\Http\Controllers\Controller;
use App\Models\Formations\DocumentProgrammeTheme;
use Illuminate\Http\Request;

class DocumentProgrammeThemeController extends Controller
{
    public function index()
    {
        $docs = DocumentProgrammeTheme::with('programmeTheme')->get();

        return response()->json($docs, 200);
    }

    public function show($id)
    {
        $doc = DocumentProgrammeTheme::with('programmeTheme')->findOrFail($id);

        return response()->json($doc, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'programme_theme_id' => 'required|exists:programmes_themes,id',
            'titre_fr'           => 'nullable|string|max:255',
            'titre_ar'           => 'nullable|string|max:255',
            'type_document_fr'   => 'nullable|string|max:255',
            'type_document_ar'   => 'nullable|string|max:255',
            'fichier'            => 'nullable|string',
            'statut'             => 'nullable|string|max:50',
            'description_fr'     => 'nullable|string',
            'description_ar'     => 'nullable|string',
            'observation_fr'     => 'nullable|string',
            'observation_ar'     => 'nullable|string',
        ]);

        $doc = DocumentProgrammeTheme::create($data);

        return response()->json($doc, 201);
    }

    public function update(Request $request, $id)
    {
        $doc = DocumentProgrammeTheme::findOrFail($id);

        $data = $request->validate([
            'programme_theme_id' => 'nullable|exists:programmes_themes,id',
            'titre_fr'           => 'nullable|string|max:255',
            'titre_ar'           => 'nullable|string|max:255',
            'type_document_fr'   => 'nullable|string|max:255',
            'type_document_ar'   => 'nullable|string|max:255',
            'fichier'            => 'nullable|string',
            'statut'             => 'nullable|string|max:50',
            'description_fr'     => 'nullable|string',
            'description_ar'     => 'nullable|string',
            'observation_fr'     => 'nullable|string',
            'observation_ar'     => 'nullable|string',
        ]);

        $doc->update($data);

        return response()->json($doc, 200);
    }

    public function destroy($id)
    {
        $doc = DocumentProgrammeTheme::findOrFail($id);
        $doc->delete();

        return response()->json(['message' => 'Document programme thème supprimé avec succès'], 200);
    }
}
