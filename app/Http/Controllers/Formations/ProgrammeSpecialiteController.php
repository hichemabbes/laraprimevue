<?php

namespace App\Http\Controllers\Formations;

use App\Http\Controllers\Controller;
use App\Models\Formations\ProgrammeSpecialite;
use App\Models\Formations\DocumentProgrammeSpecialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProgrammeSpecialiteController extends Controller
{
    /* ====================== PROGRAMMES ====================== */

    public function index(Request $request)
    {
        $query = ProgrammeSpecialite::with('specialite')->latest();

        if ($request->filled('specialite_id')) {
            $query->where('specialite_id', $request->input('specialite_id'));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        $programmes = $query->get();

        return response()->json($programmes);
    }

    public function show($id)
    {
        $programme = ProgrammeSpecialite::with([
            'specialite',
            'modulesSpecialites',
            'documentsProgrammesSpecialites',
        ])->findOrFail($id);

        return response()->json($programme);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'specialite_id'  => 'required|exists:specialites,id',
            'version'        => 'nullable|string|max:100',
            'date_debut'     => 'nullable|date',
            'date_fin'       => 'nullable|date|after_or_equal:date_debut',
            'description_fr' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'observation_fr' => 'nullable|string',
            'observation_ar' => 'nullable|string',
            'statut'         => 'nullable|in:Actif,Inactif',
        ]);

        if (!empty($validated['version'])) {
            $exists = ProgrammeSpecialite::where('specialite_id', $validated['specialite_id'])
                ->where('version', $validated['version'])
                ->exists();

            if ($exists) {
                return response()->json(
                    ['errors' => [
                        'version' => ['Cette version existe déjà pour cette spécialité'],
                    ]],
                    422
                );
            }
        }

        $programme = ProgrammeSpecialite::create($validated);

        return response()->json($programme->load('specialite'), 201);
    }

    public function update(Request $request, $id)
    {
        $programme = ProgrammeSpecialite::findOrFail($id);

        $validated = $request->validate([
            'specialite_id'  => 'sometimes|required|exists:specialites,id',
            'version'        => 'nullable|string|max:100',
            'date_debut'     => 'nullable|date',
            'date_fin'       => 'nullable|date|after_or_equal:date_debut',
            'description_fr' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'observation_fr' => 'nullable|string',
            'observation_ar' => 'nullable|string',
            'statut'         => 'nullable|in:Actif,Inactif',
        ]);

        $specialiteId = $validated['specialite_id'] ?? $programme->specialite_id;
        $version      = array_key_exists('version', $validated)
            ? $validated['version']
            : $programme->version;

        if (!empty($version)) {
            $exists = ProgrammeSpecialite::where('specialite_id', $specialiteId)
                ->where('version', $version)
                ->where('id', '!=', $programme->id)
                ->exists();

            if ($exists) {
                return response()->json(
                    ['errors' => [
                        'version' => ['Cette version existe déjà pour cette spécialité'],
                    ]],
                    422
                );
            }
        }

        $programme->update($validated);

        return response()->json($programme->load('specialite'));
    }

    public function destroy($id)
    {
        $programme = ProgrammeSpecialite::findOrFail($id);
        $programme->delete();

        return response()->json(['message' => 'Programme supprimé avec succès']);
    }

    /* ====================== DOCUMENTS PROGRAMMES ====================== */

    public function documentsIndex(Request $request)
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

    public function documentsShow($id)
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

    public function documentsStore(Request $request)
    {
        try {
            $data = $request->validate([
                'programme_specialite_id' => 'required|integer|exists:programmes_specialites,id',
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

            // Upload via FormData (fichier physique)
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
            // Cas base64 direct
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
        } catch (ValidationException $e) {
            throw $e;
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

    public function documentsUpdate(Request $request, $id)
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
                // pas de nouveau fichier -> ne pas écraser l'ancien
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
        } catch (ValidationException $e) {
            throw $e;
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

    public function documentsDestroy($id)
    {
        $doc = DocumentProgrammeSpecialite::findOrFail($id);

        // On ne gère plus de fichiers physiques ici : tout en base64
        $doc->delete();

        return response()->json([
            'message' => 'Document programme spécialité supprimé avec succès',
        ], 200);
    }

    public function documentsDownload($id)
    {
        $doc = DocumentProgrammeSpecialite::findOrFail($id);

        if (!$doc->fichier) {
            return response()->json(['error' => 'Fichier introuvable'], 404);
        }

        // Gérer les deux cas : base64 simple ou data:application/pdf;base64,...
        $base64 = $doc->fichier;

        if (str_starts_with($base64, 'data:')) {
            $parts = explode(',', $base64, 2);
            $base64 = $parts[1] ?? '';
        }

        $binary = base64_decode($base64, true);

        if ($binary === false) {
            return response()->json(['error' => 'Contenu de fichier invalide'], 500);
        }

        $filename = $doc->titre_fr
            ? Str::slug($doc->titre_fr) . '.pdf'
            : 'document-programme.pdf';

        return response($binary, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}
