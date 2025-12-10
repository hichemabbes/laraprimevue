<?php

namespace App\Http\Controllers\Formations;

use App\Http\Controllers\Controller;
use App\Models\Formations\ModuleSpecialite;
use App\Models\Formations\DocumentModuleSpecialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ModuleSpecialiteController extends Controller
{
    /* ====================== MODULES ====================== */

    public function index(Request $request)
    {
        $query = ModuleSpecialite::with('programmeSpecialite');

        if ($request->filled('programme_specialite_id')) {
            $query->where('programme_specialite_id', $request->input('programme_specialite_id'));
        }

        $modules = $query->get();

        return response()->json($modules, 200);
    }

    public function show($id)
    {
        $module = ModuleSpecialite::with([
            'programmeSpecialite',
            'documentsModulesSpecialites',
        ])->findOrFail($id);

        return response()->json($module, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'programme_specialite_id' => 'nullable|exists:programmes_specialites,id',
            'code'                    => 'nullable|string|max:100',
            'titre_module_fr'         => 'nullable|string',
            'titre_module_ar'         => 'nullable|string',
            'type_module_fr'          => 'nullable|string|max:255',
            'type_module_ar'          => 'nullable|string|max:255',
            'heures_theoriques'       => 'nullable|integer',
            'heures_pratiques'        => 'nullable|integer',
            'heures_evaluation'       => 'nullable|integer',
            'description_fr'          => 'nullable|string',
            'description_ar'          => 'nullable|string',
            'observation_fr'          => 'nullable|string',
            'observation_ar'          => 'nullable|string',
            'statut'                  => 'nullable|string|max:50',
        ]);

        $module = ModuleSpecialite::create($data);

        return response()->json($module, 201);
    }

    public function update(Request $request, $id)
    {
        $module = ModuleSpecialite::findOrFail($id);

        $data = $request->validate([
            'programme_specialite_id' => 'nullable|exists:programmes_specialites,id',
            'code'                    => 'nullable|string|max:100',
            'titre_module_fr'         => 'nullable|string',
            'titre_module_ar'         => 'nullable|string',
            'type_module_fr'          => 'nullable|string|max:255',
            'type_module_ar'          => 'nullable|string|max:255',
            'heures_theoriques'       => 'nullable|integer',
            'heures_pratiques'        => 'nullable|integer',
            'heures_evaluation'       => 'nullable|integer',
            'description_fr'          => 'nullable|string',
            'description_ar'          => 'nullable|string',
            'observation_fr'          => 'nullable|string',
            'observation_ar'          => 'nullable|string',
            'statut'                  => 'nullable|string|max:50',
        ]);

        $module->update($data);

        return response()->json($module, 200);
    }

    public function destroy($id)
    {
        $module = ModuleSpecialite::findOrFail($id);
        $module->delete();

        return response()->json(['message' => 'Module spécialité supprimé avec succès'], 200);
    }

    /* ====================== DOCUMENTS MODULES ====================== */

    public function documentsIndex(Request $request)
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

    public function documentsShow($id)
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

    public function documentsStore(Request $request)
    {
        try {
            $data = $request->validate([
                'module_specialite_id' => 'required|integer|exists:modules_specialites,id',
                'titre_fr'             => 'nullable|string|max:255',
                'titre_ar'             => 'nullable|string|max:255',
                'type_document_fr'     => 'nullable|string|max:255',
                'type_document_ar'     => 'nullable|string|max:255',
                'fichier'              => 'nullable',
                'statut'               => 'nullable|string|max:50',
                'description_fr'       => 'nullable|string',
                'description_ar'       => 'nullable|string',
                'observation_fr'       => 'nullable|string',
                'observation_ar'       => 'nullable|string',
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
        } catch (ValidationException $e) {
            throw $e;
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

    public function documentsUpdate(Request $request, $id)
    {
        $doc = DocumentModuleSpecialite::findOrFail($id);

        try {
            $data = $request->validate([
                'module_specialite_id' => 'nullable|integer|exists:modules_specialites,id',
                'titre_fr'             => 'nullable|string|max:255',
                'titre_ar'             => 'nullable|string|max:255',
                'type_document_fr'     => 'nullable|string|max:255',
                'type_document_ar'     => 'nullable|string|max:255',
                'fichier'              => 'nullable',
                'statut'               => 'nullable|string|max:50',
                'description_fr'       => 'nullable|string',
                'description_ar'       => 'nullable|string',
                'observation_fr'       => 'nullable|string',
                'observation_ar'       => 'nullable|string',
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
        } catch (ValidationException $e) {
            throw $e;
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

    public function documentsDestroy($id)
    {
        $doc = DocumentModuleSpecialite::findOrFail($id);

        $doc->delete();

        return response()->json([
            'message' => 'Document module spécialité supprimé avec succès',
        ], 200);
    }

    public function documentsDownload($id)
    {
        $doc = DocumentModuleSpecialite::findOrFail($id);

        if (!$doc->fichier) {
            return response()->json(['error' => 'Fichier introuvable'], 404);
        }

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
            : 'document-module.pdf';

        return response($binary, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}
