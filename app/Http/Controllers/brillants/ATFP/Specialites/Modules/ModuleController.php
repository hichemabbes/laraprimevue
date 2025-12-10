<?php

namespace App\Http\Controllers\brillants\ATFP\Specialites\Modules;

use App\Http\Controllers\Controller;
use App\Imports\ModulesImport;
use App\Models\brillants\Specialites\Modules\DocumentModule;
use App\Models\brillants\Specialites\Modules\Module;
use App\Models\brillants\Specialites\Programmes\Programme;
use App\Models\Liste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ModuleController extends Controller
{
    /**
     * Récupère les types de documents depuis la table listes.
     *
     * @return array
     */
    protected function getDocumentTypes()
    {
        $liste = Liste::where('nom_fr', 'Types Documents Modules')->first();
        if (!$liste || empty($liste->options)) {
            return [];
        }

        $types = [];
        foreach ($liste->options as $option) {
            $types[$option['nom_fr']] = $option['nom_ar'];
        }
        return $types;
    }

    /**
     * Liste tous les modules, avec filtre optionnel par programme.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexModules(Request $request)
    {
        try {
            $programmeId = $request->query('programme_id');

            if ($programmeId && !Programme::where('id', $programmeId)->exists()) {
                return response()->json([], 200);
            }

            $query = Module::with(['documents', 'programme.specialite.sous_secteur.secteur']);

            if ($programmeId) {
                $query->where('programme_id', $programmeId);
            }

            $modules = $query->paginate(20);

            return response()->json($modules);
        } catch (\Exception $e) {
            Log::error('Erreur dans indexModules: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json(['error' => 'Une erreur est survenue lors du chargement des modules.'], 500);
        }
    }

    /**
     * Affiche un module spécifique.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showModule($id)
    {
        try {
            $module = Module::with(['programme.specialite.sous_secteur.secteur', 'documents'])->findOrFail($id);

            return response()->json($module);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('DocumentProgrammeSpecialite non trouvé: ' . $id);
            return response()->json(['error' => 'DocumentProgrammeSpecialite non trouvé.'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur dans showModule: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération du module.'], 500);
        }
    }

    /**
     * Crée un nouveau module.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeModule(Request $request)
    {
        try {
            Log::debug('Requête reçue pour créer un module', $request->all());

            $validated = $request->validate([
                'programme_id' => 'required|exists:programmes_etudes,id',
                'code' => [
                    'required',
                    'string',
                    'max:100',
                    function ($attribute, $value, $fail) use ($request) {
                        $exists = Module::where('programme_id', $request->programme_id)
                            ->where('code', $value)
                            ->whereNull('deleted_at')
                            ->exists();
                        if ($exists) {
                            $fail("Le code '$value' existe déjà pour ce programme.");
                        }
                    },
                ],
                'titre_module' => 'required|string',
                'type_module_fr' => 'required|string|in:Spécifique,Général,Stage',
                'type_module_ar' => 'nullable|string|max:100',
                'heures_theoriques' => 'nullable|integer|min:0',
                'heures_pratiques' => 'nullable|integer|min:0',
                'heures_evaluation' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
                'observation' => 'nullable|string',
                'statut' => 'required|string|in:Actif,Inactif',
            ]);

            $module = Module::create($validated);
            Log::info('DocumentProgrammeSpecialite créé:', $module->toArray());

            return response()->json($module, 201);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation dans storeModule: ' . json_encode($e->errors()));
            return response()->json(['error' => 'Erreur de validation', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans storeModule: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la création du module.'], 500);
        }
    }

    /**
     * Met à jour un module existant.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateModule(Request $request, $id)
    {
        try {
            Log::debug('Requête reçue pour mettre à jour un module', [
                'id' => $id,
                'data' => $request->all(),
            ]);

            $module = Module::findOrFail($id);

            $validated = $request->validate([
                'programme_id' => 'required|exists:programmes_etudes,id',
                'code' => [
                    'required',
                    'string',
                    'max:100',
                    function ($attribute, $value, $fail) use ($request, $id) {
                        $exists = Module::where('programme_id', $request->programme_id)
                            ->where('code', $value)
                            ->whereNull('deleted_at')
                            ->where('id', '!=', $id)
                            ->exists();
                        if ($exists) {
                            $fail("Le code '$value' existe déjà pour ce programme.");
                        }
                    },
                ],
                'titre_module' => 'required|string',
                'type_module_fr' => 'required|string|in:Spécifique,Général,Stage',
                'type_module_ar' => 'nullable|string|max:100',
                'heures_theoriques' => 'nullable|integer|min:0',
                'heures_pratiques' => 'nullable|integer|min:0',
                'heures_evaluation' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
                'observation' => 'nullable|string',
                'statut' => 'required|string|in:Actif,Inactif',
            ]);

            $module->update($validated);
            Log::info('DocumentProgrammeSpecialite mis à jour:', $module->toArray());

            return response()->json($module);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation dans updateModule: ' . json_encode($e->errors()));
            return response()->json(['error' => 'Erreur de validation', 'details' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('DocumentProgrammeSpecialite non trouvé pour mise à jour: ' . $id);
            return response()->json(['error' => 'DocumentProgrammeSpecialite non trouvé.'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur dans updateModule: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la mise à jour du module.'], 500);
        }
    }

    /**
     * Supprime un module (soft delete).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyModule($id)
    {
        try {
            $module = Module::findOrFail($id);
            $module->delete();
            Log::info('DocumentProgrammeSpecialite supprimé (soft delete):', ['id' => $id]);

            return response()->json(['message' => 'DocumentProgrammeSpecialite supprimé avec succès']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('DocumentProgrammeSpecialite non trouvé pour suppression: ' . $id);
            return response()->json(['error' => 'DocumentProgrammeSpecialite non trouvé.'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur dans destroyModule: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la suppression du module.'], 500);
        }
    }

    /**
     * Crée un nouveau document pour un module.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeDocument(Request $request)
    {
        try {
            Log::info('Requête storeDocument reçue:', [
                'input' => $request->all(),
                'files' => $request->hasFile('fichier') ? $request->file('fichier')->getClientOriginalName() : null,
            ]);

            $documentTypes = $this->getDocumentTypes();
            $validTypes = array_keys($documentTypes);

            $validated = $request->validate([
                'module_id' => 'required|exists:modules,id',
                'titre' => 'required|string|max:255',
                'type_document_fr' => [
                    'required',
                    'string',
                    'max:100',
                    'in:' . implode(',', $validTypes),
                    function ($attribute, $value, $fail) use ($request) {
                        $exists = DocumentModule::where('module_id', $request->module_id)
                            ->where('type_document_fr', $value)
                            ->where('titre', $request->titre)
                            ->whereNull('deleted_at')
                            ->exists();
                        if ($exists) {
                            $fail('Un document avec ce titre et ce type existe déjà pour ce module.');
                        }
                    },
                ],
                'type_document_ar' => [
                    'required',
                    'string',
                    'max:100',
                    function ($attribute, $value, $fail) use ($request, $documentTypes) {
                        if ($documentTypes[$request->type_document_fr] !== $value) {
                            $fail('Le type de document en arabe ne correspond pas au type en français.');
                        }
                    },
                ],
                'fichier' => 'required|file|mimes:pdf|max:2048',
                'statut' => 'required|string|in:Actif,Inactif',
                'description' => 'nullable|string|max:1000',
                'observation' => 'nullable|string|max:1000',
            ]);

            // Gérer le téléchargement du fichier PDF
            $file = $request->file('fichier');
            $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $filePath = $file->storeAs('documents/modules', $fileName, 'public');
            $validated['fichier'] = $filePath;

            // Mettre à jour le statut des autres documents si le document est Actif
            if ($validated['statut'] === 'Actif') {
                DocumentModule::where('module_id', $validated['module_id'])
                    ->where('type_document_fr', $validated['type_document_fr'])
                    ->whereNull('deleted_at')
                    ->where('id', '!=', $validated['module_id'])
                    ->update(['statut' => 'Inactif']);
            }

            $document = DocumentModule::create($validated);
            Log::info('Document créé:', [
                'id' => $document->id,
                'titre' => $document->titre,
                'type_document_fr' => $document->type_document_fr,
                'module_id' => $document->module_id,
            ]);

            return response()->json(['data' => $document], 201);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation dans storeDocument: ' . json_encode($e->errors()));
            return response()->json(['error' => 'Erreur de validation', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans storeDocument: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'file' => $request->hasFile('fichier') ? $request->file('fichier')->getClientOriginalName() : null,
            ]);

            return response()->json(['error' => 'Erreur lors de la création du document: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Met à jour un document existant pour un module.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDocument(Request $request, $id)
    {
        try {
            Log::info('Requête updateDocument reçue:', [
                'input' => $request->all(),
                'files' => $request->hasFile('fichier') ? $request->file('fichier')->getClientOriginalName() : null,
            ]);

            $document = DocumentModule::findOrFail($id);
            $documentTypes = $this->getDocumentTypes();
            $validTypes = array_keys($documentTypes);

            $validated = $request->validate([
                'module_id' => 'required|exists:modules,id',
                'titre' => 'required|string|max:255',
                'type_document_fr' => [
                    'required',
                    'string',
                    'max:100',
                    'in:' . implode(',', $validTypes),
                    function ($attribute, $value, $fail) use ($request, $id) {
                        $exists = DocumentModule::where('module_id', $request->module_id)
                            ->where('type_document_fr', $value)
                            ->where('titre', $request->titre)
                            ->whereNull('deleted_at')
                            ->where('id', '!=', $id)
                            ->exists();
                        if ($exists) {
                            $fail('Un document avec ce titre et ce type existe déjà pour ce module.');
                        }
                    },
                ],
                'type_document_ar' => [
                    'required',
                    'string',
                    'max:100',
                    function ($attribute, $value, $fail) use ($request, $documentTypes) {
                        if ($documentTypes[$request->type_document_fr] !== $value) {
                            $fail('Le type de document en arabe ne correspond pas au type en français.');
                        }
                    },
                ],
                'fichier' => 'nullable|file|mimes:pdf|max:2048',
                'statut' => 'required|string|in:Actif,Inactif',
                'description' => 'nullable|string|max:1000',
                'observation' => 'nullable|string|max:1000',
            ]);

            // Gérer le téléchargement du fichier PDF, si fourni
            if ($request->hasFile('fichier')) {
                if ($document->fichier && Storage::disk('public')->exists($document->fichier)) {
                    Storage::disk('public')->delete($document->fichier);
                }
                $file = $request->file('fichier');
                $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs('documents/modules', $fileName, 'public');
                $validated['fichier'] = $filePath;
            } else {
                unset($validated['fichier']);
            }

            // Mettre à jour le statut des autres documents si le document est Actif
            if ($validated['statut'] === 'Actif') {
                DocumentModule::where('module_id', $validated['module_id'])
                    ->where('type_document_fr', $validated['type_document_fr'])
                    ->where('id', '!=', $id)
                    ->whereNull('deleted_at')
                    ->update(['statut' => 'Inactif']);
            }

            $document->update($validated);
            Log::info('Document mis à jour:', $document->toArray());

            return response()->json(['data' => $document], 200);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation dans updateDocument: ' . json_encode($e->errors()));
            return response()->json(['error' => 'Erreur de validation', 'details' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Document non trouvé pour mise à jour: ' . $id);
            return response()->json(['error' => 'Document non trouvé.'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur dans updateDocument: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la mise à jour du document.'], 500);
        }
    }

    /**
     * Supprime un document d'un module (soft delete).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyDocument($id)
    {
        try {
            $document = DocumentModule::findOrFail($id);
            if ($document->fichier && Storage::disk('public')->exists($document->fichier)) {
                Storage::disk('public')->delete($document->fichier);
            }
            $document->delete();
            Log::info('Document supprimé:', ['id' => $id]);

            return response()->json(['message' => 'Document supprimé avec succès.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Document non trouvé pour suppression: ' . $id);
            return response()->json(['error' => 'Document non trouvé.'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur dans destroyDocument: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la suppression du document.'], 500);
        }
    }

    /**
     * Liste tous les documents, avec filtre optionnel par module.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexDocuments(Request $request)
    {
        try {
            $moduleId = $request->query('module_id');
            $query = DocumentModule::with(['module.programme.specialite']);

            if ($moduleId) {
                $query->where('module_id', $moduleId);
            }

            $documents = $query->get();
            Log::info('Documents récupérés', ['count' => $documents->count(), 'module_id' => $moduleId]);

            return response()->json(['data' => $documents], 200);
        } catch (\Exception $e) {
            Log::error('Erreur dans indexDocuments: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du chargement des documents.'], 500);
        }
    }

    /**
     * Télécharge un document d'un module.
     *
     * @param int $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function downloadDocument($id)
    {
        try {
            $document = DocumentModule::findOrFail($id);
            Log::info('Tentative de téléchargement du document', ['id' => $id]);

            if (!$document->fichier || !Storage::disk('public')->exists($document->fichier)) {
                Log::error('Fichier non trouvé pour le téléchargement', ['id' => $id]);
                return response()->json(['error' => 'Fichier non trouvé.'], 404);
            }

            $filePath = Storage::disk('public')->path($document->fichier);
            $fileName = $document->titre ? str_replace(' ', '_', $document->titre) . '.pdf' : 'document.pdf';

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Document non trouvé pour le téléchargement: ' . $id);
            return response()->json(['error' => 'Document non trouvé.'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du document: ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['error' => 'Erreur lors du téléchargement du document.'], 500);
        }
    }

    /**
     * Récupère la liste des types de documents pour les modules.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDocumentTypesList()
    {
        try {
            $liste = Liste::where('nom_fr', 'Types Documents Modules')->first();
            if (!$liste || empty($liste->options)) {
                return response()->json(['data' => []], 200);
            }

            return response()->json(['data' => $liste->options], 200);
        } catch (\Exception $e) {
            Log::error('Erreur dans getDocumentTypesList: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des types de documents.'], 500);
        }
    }

    /**
     * Importe des modules depuis un fichier Excel.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importxls(Request $request)
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:xlsx,xls',
                'programme_id' => 'required|exists:programmes_etudes,id',
            ]);

            Excel::import(new ModulesImport($validated['programme_id']), $request->file('file'));
            Log::info('Modules importés via fichier Excel', ['programme_id' => $validated['programme_id']]);

            return response()->json(['message' => 'Modules importés avec succès.'], 200);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation dans importxls: ' . json_encode($e->errors()));
            return response()->json(['error' => 'Erreur de validation', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans importxls: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'importation des modules.'], 500);
        }
    }

    /**
     * Récupère un programme par sa version et éventuellement par spécialité.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProgrammeByVersion(Request $request)
    {
        try {
            $version = $request->query('version');
            $specialiteId = $request->query('specialite_id');

            $query = Programme::with(['specialite.sous_secteur.secteur']);

            if ($version) {
                $query->where('version', $version);
            }

            if ($specialiteId) {
                $query->where('specialite_id', $specialiteId);
            }

            $programme = $query->first();

            if (!$programme) {
                return response()->json(['error' => 'Programme non trouvé.'], 404);
            }

            return response()->json($programme);
        } catch (\Exception $e) {
            Log::error('Erreur dans getProgrammeByVersion: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération du programme.'], 500);
        }
    }
}
