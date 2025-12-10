<?php

namespace App\Http\Controllers\Formations;

use App\Http\Controllers\Controller;
use App\Models\Formations\DocumentModuleSpecialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentModuleSpecialiteController extends Controller
{
    public function index(Request $request)
    {
        $query = DocumentModuleSpecialite::with('moduleSpecialite');

        if ($request->filled('module_specialite_id')) {
            $query->where('module_specialite_id', $request->input('module_specialite_id'));
        }

        $docs = $query->get();

        $docs->each(function ($doc) {
            $doc->setAttribute(
                'fichier_url',
                $doc->id
                    ? url('/api/documents-modules-specialites/' . $doc->id . '/download')
                    : null
            );
        });

        return response()->json($docs, 200);
    }

    public function show($id)
    {
        $doc = DocumentModuleSpecialite::with('moduleSpecialite')->findOrFail($id);

        $doc->setAttribute(
            'fichier_url',
            $doc->id
                ? url('/api/documents-modules-specialites/' . $doc->id . '/download')
                : null
        );

        return response()->json($doc, 200);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'module_specialite_id' => 'required|integer|exists:modules_specialites,id',
                'titre_fr'            => 'nullable|string|max:255',
                'titre_ar'            => 'nullable|string|max:255',
                'type_document_fr'    => 'nullable|string|max:255',
                'type_document_ar'    => 'nullable|string|max:255',
                'fichier'             => 'nullable',
                'statut'              => 'nullable|string|max:50',
                'description_fr'      => 'nullable|string',
                'description_ar'      => 'nullable|string',
                'observation_fr'      => 'nullable|string',
                'observation_ar'      => 'nullable|string',
            ]);

            if ($request->hasFile('fichier')) {
                $file = $request->file('fichier');

                if (strtolower($file->getClientOriginalExtension()) !== 'pdf') {
                    return response()->json([
                        'message' => 'Le fichier doit être un PDF.',
                    ], 422);
                }

                $binary = file_get_contents($file->getRealPath());
                $data['fichier'] = base64_encode($binary);
            } elseif ($request->filled('fichier') && is_string($request->input('fichier'))) {
                $data['fichier'] = $request->input('fichier');
            } else {
                $data['fichier'] = null;
            }

            $doc = DocumentModuleSpecialite::create($data);

            $doc->setAttribute(
                'fichier_url',
                $doc->id
                    ? url('/api/documents-modules-specialites/' . $doc->id . '/download')
                    : null
            );

            return response()->json($doc, 201);
        } catch (\Throwable $e) {
            Log::error('Erreur store DocumentModuleSpecialite', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erreur interne lors de la création du document.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $doc = DocumentModuleSpecialite::findOrFail($id);

        try {
            $data = $request->validate([
                'module_specialite_id' => 'nullable|integer|exists:modules_specialites,id',
                'titre_fr'            => 'nullable|string|max:255',
                'titre_ar'            => 'nullable|string|max:255',
                'type_document_fr'    => 'nullable|string|max:255',
                'type_document_ar'    => 'nullable|string|max:255',
                'fichier'             => 'nullable',
                'statut'              => 'nullable|string|max:50',
                'description_fr'      => 'nullable|string',
                'description_ar'      => 'nullable|string',
                'observation_fr'      => 'nullable|string',
                'observation_ar'      => 'nullable|string',
            ]);

            if ($request->hasFile('fichier')) {
                $file = $request->file('fichier');

                if (strtolower($file->getClientOriginalExtension()) !== 'pdf') {
                    return response()->json([
                        'message' => 'Le fichier doit être un PDF.',
                    ], 422);
                }

                $binary = file_get_contents($file->getRealPath());
                $data['fichier'] = base64_encode($binary);
            } elseif ($request->filled('fichier') && is_string($request->input('fichier'))) {
                $data['fichier'] = $request->input('fichier');
            } else {
                unset($data['fichier']);
            }

            $doc->update($data);

            $doc->setAttribute(
                'fichier_url',
                $doc->id
                    ? url('/api/documents-modules-specialites/' . $doc->id . '/download')
                    : null
            );

            return response()->json($doc, 200);
        } catch (\Throwable $e) {
            Log::error('Erreur update DocumentModuleSpecialite', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erreur interne lors de la mise à jour du document.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        $doc = DocumentModuleSpecialite::findOrFail($id);

        if ($doc->fichier && ! $this->isBase64Like($doc->fichier) &&
            Storage::disk('public')->exists($doc->fichier)
        ) {
            Storage::disk('public')->delete($doc->fichier);
        }

        $doc->delete();

        return response()->json([
            'message' => 'Document module spécialité supprimé avec succès',
        ], 200);
    }

    public function download($id)
    {
        $doc = DocumentModuleSpecialite::findOrFail($id);

        if (! $doc->fichier) {
            return response()->json(['error' => 'Fichier introuvable'], 404);
        }

        // cas chemin
        if (! $this->isBase64Like($doc->fichier) &&
            Storage::disk('public')->exists($doc->fichier)
        ) {
            $path = Storage::disk('public')->path($doc->fichier);

            $filename = $doc->titre_fr
                ? Str::slug($doc->titre_fr) . '.pdf'
                : basename($path);

            return response()->download($path, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        // cas base64
        $binary = base64_decode($doc->fichier, true);

        if ($binary === false || $binary === null) {
            Log::error('DocumentModuleSpecialite base64 invalide', [
                'id' => $doc->id,
            ]);
            return response()->json(['error' => 'Contenu de fichier invalide'], 500);
        }

        $filename = $doc->titre_fr
            ? Str::slug($doc->titre_fr) . '.pdf'
            : 'document-module.pdf';

        return response($binary, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Content-Length', strlen($binary));
    }

    protected function isBase64Like(string $value): bool
    {
        if (str_contains($value, '/') || str_ends_with(strtolower($value), '.pdf')) {
            return false;
        }

        return (bool) preg_match('/^[A-Za-z0-9+\/=]+$/', $value);
    }
}
