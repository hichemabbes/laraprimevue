<?php

namespace App\Http\Controllers\Formations;

use App\Http\Controllers\Controller;
use App\Models\Formations\DocumentProgrammeSpecialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentProgrammeSpecialiteController extends Controller
{
    public function index(Request $request)
    {
        $query = DocumentProgrammeSpecialite::with('programmeSpecialite');

        if ($request->filled('programme_specialite_id')) {
            $query->where('programme_specialite_id', $request->input('programme_specialite_id'));
        }

        $docs = $query->get();

        $docs->each(function ($doc) {
            $doc->setAttribute(
                'fichier_url',
                $doc->id
                    ? url('/api/documents-programmes-specialites/' . $doc->id . '/download')
                    : null
            );
        });

        return response()->json($docs, 200);
    }

    public function show($id)
    {
        $doc = DocumentProgrammeSpecialite::with('programmeSpecialite')->findOrFail($id);

        $doc->setAttribute(
            'fichier_url',
            $doc->id
                ? url('/api/documents-programmes-specialites/' . $doc->id . '/download')
                : null
        );

        return response()->json($doc, 200);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'programme_specialite_id' => 'required|integer|exists:programmes_specialites,id',
                'titre_fr'                => 'nullable|string|max:255',
                'titre_ar'                => 'nullable|string|max:255',
                'type_document_fr'        => 'nullable|string|max:255',
                'type_document_ar'        => 'nullable|string|max:255',
                'fichier'                 => 'nullable', // base64 ou file
                'statut'                  => 'nullable|string|max:50',
                'description_fr'          => 'nullable|string',
                'description_ar'          => 'nullable|string',
                'observation_fr'          => 'nullable|string',
                'observation_ar'          => 'nullable|string',
            ]);

            // 1) Cas FormData avec fichier PDF
            if ($request->hasFile('fichier')) {
                $file = $request->file('fichier');

                if (strtolower($file->getClientOriginalExtension()) !== 'pdf') {
                    return response()->json([
                        'message' => 'Le fichier doit être un PDF.',
                    ], 422);
                }

                $binary = file_get_contents($file->getRealPath());
                $data['fichier'] = base64_encode($binary);
            }
            // 2) Cas base64 direct
            elseif ($request->filled('fichier') && is_string($request->input('fichier'))) {
                $data['fichier'] = $request->input('fichier');
            } else {
                $data['fichier'] = null;
            }

            $doc = DocumentProgrammeSpecialite::create($data);

            $doc->setAttribute(
                'fichier_url',
                $doc->id
                    ? url('/api/documents-programmes-specialites/' . $doc->id . '/download')
                    : null
            );

            return response()->json($doc, 201);
        } catch (\Throwable $e) {
            Log::error('Erreur store DocumentProgrammeSpecialite', [
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
        $doc = DocumentProgrammeSpecialite::findOrFail($id);

        try {
            $data = $request->validate([
                'programme_specialite_id' => 'nullable|integer|exists:programmes_specialites,id',
                'titre_fr'                => 'nullable|string|max:255',
                'titre_ar'                => 'nullable|string|max:255',
                'type_document_fr'        => 'nullable|string|max:255',
                'type_document_ar'        => 'nullable|string|max:255',
                'fichier'                 => 'nullable',
                'statut'                  => 'nullable|string|max:50',
                'description_fr'          => 'nullable|string',
                'description_ar'          => 'nullable|string',
                'observation_fr'          => 'nullable|string',
                'observation_ar'          => 'nullable|string',
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
                // on ne touche pas au fichier existant
                unset($data['fichier']);
            }

            $doc->update($data);

            $doc->setAttribute(
                'fichier_url',
                $doc->id
                    ? url('/api/documents-programmes-specialites/' . $doc->id . '/download')
                    : null
            );

            return response()->json($doc, 200);
        } catch (\Throwable $e) {
            Log::error('Erreur update DocumentProgrammeSpecialite', [
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
        $doc = DocumentProgrammeSpecialite::findOrFail($id);

        // si jamais ancien système avec chemin de fichier physique
        if ($doc->fichier && ! $this->isBase64Like($doc->fichier) &&
            Storage::disk('public')->exists($doc->fichier)
        ) {
            Storage::disk('public')->delete($doc->fichier);
        }

        $doc->delete();

        return response()->json([
            'message' => 'Document programme spécialité supprimé avec succès',
        ], 200);
    }

    public function download($id)
    {
        $doc = DocumentProgrammeSpecialite::findOrFail($id);

        if (! $doc->fichier) {
            return response()->json(['error' => 'Fichier introuvable'], 404);
        }

        // 1) Cas chemin de fichier (compatibilité ancienne version)
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

        // 2) Cas base64 en BDD
        $binary = base64_decode($doc->fichier, true);

        if ($binary === false || $binary === null) {
            Log::error('DocumentProgrammeSpecialite base64 invalide', [
                'id' => $doc->id,
            ]);
            return response()->json(['error' => 'Contenu de fichier invalide'], 500);
        }

        $filename = $doc->titre_fr
            ? Str::slug($doc->titre_fr) . '.pdf'
            : 'document-programme.pdf';

        return response($binary, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Content-Length', strlen($binary));
    }

    /**
     * Détection simple : est-ce que la chaîne ressemble à du base64 ?
     */
    protected function isBase64Like(string $value): bool
    {
        // si contient un slash ou ".pdf" → probablement chemin
        if (str_contains($value, '/') || str_ends_with(strtolower($value), '.pdf')) {
            return false;
        }

        // base64 ne contient que ces caractères
        return (bool) preg_match('/^[A-Za-z0-9+\/=]+$/', $value);
    }
}
