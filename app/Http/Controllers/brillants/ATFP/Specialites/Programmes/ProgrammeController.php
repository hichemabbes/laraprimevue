<?php

namespace App\Http\Controllers\brillants\ATFP\Specialites\Programmes;

use App\Http\Controllers\Controller;
use App\Models\Annees\AnneeFormation;
use App\Models\brillants\Specialites\Programmes\DocumentProgramme;
use App\Models\brillants\Specialites\Programmes\Programme;
use App\Models\brillants\Specialites\Specialite;
use App\Models\Liste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProgrammeController extends Controller
{
    /**
     * Récupère les types de documents depuis la table listes.
     *
     * @return array
     */
    private function getDocumentTypes()
    {
        $liste = Liste::where('nom_fr', 'Types Documents Spécialités')->first();
        if (!$liste || empty($liste->options)) {
            Log::warning('Liste Types Documents Spécialités non trouvée ou vide dans la table listes.');
            return [];
        }

        $types = [];
        foreach ($liste->options as $option) {
            $types[$option['nom_fr']] = $option['nom_ar'];
        }
        return $types;
    }

    /**
     * Retourne la liste des types de documents pour le frontend.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDocumentTypesList()
    {
        try {
            $liste = Liste::where('nom_fr', 'Types Documents Spécialités')->first();
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
     * Liste les spécialités avec leurs programmes filtrés par année, spécialité et standardisation.
     */
    public function indexProgrammes(Request $request)
    {
        try {
            $standardisation = $request->query('standardisation', 'tous');
            $anneeId = $request->query('annee_id');
            $specialiteId = $request->query('specialite_id');
            $perPage = 20;

            // Vérifier si l'année existe
            if ($anneeId && !AnneeFormation::where('id', $anneeId)->exists()) {
                return response()->json(['data' => [], 'total' => 0], 200);
            }

            // Construire la requête principale
            $query = Specialite::with([
                'programmes' => function ($q) use ($anneeId) {
                    if ($anneeId) {
                        $annee = AnneeFormation::find($anneeId);
                        if ($annee && $annee->date_debut && $annee->date_fin) {
                            $q->whereNotNull('date_debut')
                                ->where('date_debut', '<=', $annee->date_fin)
                                ->where(function ($subQ) use ($annee) {
                                    $subQ->whereNull('date_fin')
                                        ->orWhere(function ($subSubQ) use ($annee) {
                                            $subSubQ->whereNotNull('date_fin')
                                                ->where('date_fin', '>=', $annee->date_debut);
                                        });
                                });
                        }
                    }
                    $q->with(['modules', 'documents']);
                },
                'infos_specialites' => function ($q) use ($anneeId) {
                    if ($anneeId) {
                        $q->where('annee_formation_id', $anneeId);
                    }
                }
            ]);

            // Appliquer le filtre de spécialité si fourni
            if ($specialiteId) {
                $query->where('id', $specialiteId);
            }

            // Appliquer le filtre de standardisation
            if ($standardisation !== 'tous') {
                $query->where('standardisation_ar', $standardisation);
            }

            // Exécuter la requête avec pagination
            $specialites = $query->paginate($perPage);

            return response()->json($specialites);
        } catch (\Exception $e) {
            Log::error('Erreur dans indexProgrammes: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['error' => 'Une erreur est survenue lors du chargement des programmes.'], 500);
        }
    }

    /**
     * Affiche les détails d'un programme spécifique.
     */
    public function showProgramme($id)
    {
        try {
            $programme = Programme::with(['specialite', 'modules', 'documents'])->findOrFail($id);
            return response()->json($programme);
        } catch (\Exception $e) {
            Log::error('Erreur dans showProgramme: ' . $e->getMessage());
            return response()->json(['error' => 'Programme non trouvé.'], 404);
        }
    }

    /**
     * Crée un nouveau programme et gère le statut actif/inactif.
     */
    public function storeProgramme(Request $request)
    {
        try {
            $validated = $request->validate([
                'specialite_id' => 'required|exists:specialites,id',
                'version' => [
                    'required',
                    'string',
                    'max:100',
                    function ($attribute, $value, $fail) use ($request) {
                        $exists = Programme::where('specialite_id', $request->specialite_id)
                            ->where('version', $value)
                            ->whereNull('deleted_at')
                            ->exists();
                        if ($exists) {
                            $fail("La version '$value' existe déjà pour cette spécialité.");
                        }
                    },
                ],
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'description' => 'nullable|string',
                'observation' => 'nullable|string',
                'statut' => 'nullable|string|in:Actif,Inactif',
            ]);

            $validated['statut'] = $validated['statut'] ?? 'Actif';

            DB::transaction(function () use ($validated, &$programme) {
                // Si le nouveau programme est Actif, mettre les autres à Inactif
                if ($validated['statut'] === 'Actif') {
                    Programme::where('specialite_id', $validated['specialite_id'])
                        ->whereNull('deleted_at')
                        ->update(['statut' => 'Inactif']);
                }

                // Créer le programme
                $programme = Programme::create($validated);
            });

            Log::info('Programme créé:', $programme->toArray());
            return response()->json($programme, 201);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation dans storeProgramme: ' . json_encode($e->errors()));
            return response()->json(['error' => 'Erreur de validation', 'details' => $e->errors()], 400);
        } catch (\Exception $e) {
            Log::error('Erreur dans storeProgramme: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la création du programme.'], 400);
        }
    }

    /**
     * Met à jour un programme et gère le statut actif/inactif.
     */
    public function updateProgramme(Request $request, $id)
    {
        try {
            $programme = Programme::findOrFail($id);

            $validated = $request->validate([
                'specialite_id' => 'required|exists:specialites,id',
                'version' => [
                    'required',
                    'string',
                    'max:100',
                    function ($attribute, $value, $fail) use ($request, $id) {
                        $exists = Programme::where('specialite_id', $request->specialite_id)
                            ->where('version', $value)
                            ->whereNull('deleted_at')
                            ->where('id', '!=', $id)
                            ->exists();
                        if ($exists) {
                            $fail("La version '$value' existe déjà pour cette spécialité.");
                        }
                    },
                ],
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'description' => 'nullable|string',
                'observation' => 'nullable|string',
                'statut' => 'nullable|string|in:Actif,Inactif',
            ]);

            $validated['statut'] = $validated['statut'] ?? $programme->statut ?? 'Actif';

            DB::transaction(function () use ($validated, $id, $programme) {
                // Si le programme devient Actif, mettre les autres à Inactif
                if ($validated['statut'] === 'Actif') {
                    Programme::where('specialite_id', $validated['specialite_id'])
                        ->where('id', '!=', $id)
                        ->whereNull('deleted_at')
                        ->update(['statut' => 'Inactif']);
                }

                // Mettre à jour le programme
                $programme->update($validated);
            });

            Log::info('Programme mis à jour:', $programme->toArray());
            return response()->json($programme);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation dans updateProgramme: ' . json_encode($e->errors()));
            return response()->json(['error' => 'Erreur de validation', 'details' => $e->errors()], 400);
        } catch (\Exception $e) {
            Log::error('Erreur dans updateProgramme: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la mise à jour du programme.'], 400);
        }
    }

    /**
     * Supprime un programme (soft delete).
     */
    public function destroyProgramme($id)
    {
        try {
            $programme = Programme::findOrFail($id);
            $programme->delete();
            Log::info('Programme supprimé (soft delete):', ['id' => $id]);
            return response()->json(['message' => 'Programme supprimé avec succès']);
        } catch (\Exception $e) {
            Log::error('Erreur dans destroyProgramme: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la suppression du programme.'], 400);
        }
    }

    /**
     * Crée un nouveau document pour un programme.
     */
    public function storeDocument(Request $request)
    {
        try {
            Log::info('Requête storeDocument reçue:', [
                'input' => $request->all(),
                'files' => $request->hasFile('fichier') ? $request->file('fichier')->getClientOriginalName() : null
            ]);

            $documentTypes = $this->getDocumentTypes();
            if (empty($documentTypes)) {
                Log::error('Aucun type de document valide trouvé.');
                return response()->json(['error' => 'Aucun type de document configuré dans le système.'], 422);
            }
            $validTypes = array_keys($documentTypes);

            $validated = $request->validate([
                'programme_id' => 'required|exists:programmes_etudes,id',
                'titre' => 'required|string|max:255',
                'type_document_fr' => [
                    'required',
                    'string',
                    'max:100',
                    'in:' . implode(',', $validTypes),
                    function ($attribute, $value, $fail) use ($request) {
                        $exists = DocumentProgramme::where('programme_id', $request->programme_id)
                            ->where('type_document_fr', $value)
                            ->where('titre', $request->titre)
                            ->whereNull('deleted_at')
                            ->exists();
                        if ($exists) {
                            $fail("Un document avec ce titre et ce type existe déjà pour ce programme.");
                        }
                    },
                ],
                'type_document_ar' => [
                    'required',
                    'string',
                    'max:100',
                    function ($attribute, $value, $fail) use ($request, $documentTypes) {
                        if (!isset($documentTypes[$request->type_document_fr]) || $documentTypes[$request->type_document_fr] !== $value) {
                            $fail("Le type de document en arabe ne correspond pas au type en français.");
                        }
                    },
                ],
                'fichier' => 'required|file|mimes:pdf|max:2048',
                'statut' => 'required|string|in:Actif,Inactif',
                'description' => 'nullable|string|max:1000',
                'observation' => 'nullable|string|max:1000',
            ]);

            // Convertir le fichier PDF en base64
            $file = $request->file('fichier');
            $fileContent = file_get_contents($file->getRealPath());
            $base64File = base64_encode($fileContent);
            $validated['fichier'] = $base64File;

            DB::transaction(function () use ($validated) {
                // Mettre à jour le statut des autres documents si le nouveau document est Actif
                if ($validated['statut'] === 'Actif') {
                    DocumentProgramme::where('programme_id', $validated['programme_id'])
                        ->where('type_document_fr', $validated['type_document_fr'])
                        ->whereNull('deleted_at')
                        ->update(['statut' => 'Inactif']);
                }

                // Créer le document
                DocumentProgramme::create($validated);
            });

            $document = DocumentProgramme::where('programme_id', $validated['programme_id'])
                ->where('titre', $validated['titre'])
                ->where('type_document_fr', $validated['type_document_fr'])
                ->first();

            Log::info('Document créé:', [
                'id' => $document->id,
                'titre' => $document->titre,
                'type_document_fr' => $document->type_document_fr,
                'programme_id' => $document->programme_id,
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
            return response()->json(['error' => 'Erreur lors de la création du document.'], 500);
        }
    }

    /**
     * Met à jour un document existant.
     */
    public function updateDocument(Request $request, $id)
    {
        try {
            Log::info('Requête updateDocument reçue:', [
                'input' => $request->all(),
                'files' => $request->hasFile('fichier') ? $request->file('fichier')->getClientOriginalName() : null
            ]);

            $document = DocumentProgramme::findOrFail($id);
            $documentTypes = $this->getDocumentTypes();
            if (empty($documentTypes)) {
                Log::error('Aucun type de document valide trouvé.');
                return response()->json(['error' => 'Aucun type de document configuré dans le système.'], 422);
            }
            $validTypes = array_keys($documentTypes);

            $validated = $request->validate([
                'programme_id' => 'required|exists:programmes_etudes,id',
                'titre' => 'required|string|max:255',
                'type_document_fr' => [
                    'required',
                    'string',
                    'max:100',
                    'in:' . implode(',', $validTypes),
                    function ($attribute, $value, $fail) use ($request, $id) {
                        $exists = DocumentProgramme::where('programme_id', $request->programme_id)
                            ->where('type_document_fr', $value)
                            ->where('titre', $request->titre)
                            ->whereNull('deleted_at')
                            ->where('id', '!=', $id)
                            ->exists();
                        if ($exists) {
                            $fail("Un document avec ce titre et ce type existe déjà pour ce programme.");
                        }
                    },
                ],
                'type_document_ar' => [
                    'required',
                    'string',
                    'max:100',
                    function ($attribute, $value, $fail) use ($request, $documentTypes) {
                        if (!isset($documentTypes[$request->type_document_fr]) || $documentTypes[$request->type_document_fr] !== $value) {
                            $fail("Le type de document en arabe ne correspond pas au type en français.");
                        }
                    },
                ],
                'fichier' => 'nullable|file|mimes:pdf|max:2048',
                'statut' => 'required|string|in:Actif,Inactif',
                'description' => 'nullable|string|max:1000',
                'observation' => 'nullable|string|max:1000',
            ]);

            DB::transaction(function () use ($request, $validated, $id, $document) {
                // Si un nouveau fichier est fourni, le convertir en base64
                if ($request->hasFile('fichier')) {
                    $file = $request->file('fichier');
                    $fileContent = file_get_contents($file->getRealPath());
                    $base64File = base64_encode($fileContent);
                    $validated['fichier'] = $base64File;
                } else {
                    unset($validated['fichier']);
                }

                // Mettre à jour le statut des autres documents si le document est Actif
                if ($validated['statut'] === 'Actif') {
                    DocumentProgramme::where('programme_id', $validated['programme_id'])
                        ->where('type_document_fr', $validated['type_document_fr'])
                        ->where('id', '!=', $id)
                        ->whereNull('deleted_at')
                        ->update(['statut' => 'Inactif']);
                }

                // Mettre à jour le document
                $document->update($validated);
            });

            Log::info('Document mis à jour:', $document->toArray());
            return response()->json(['data' => $document], 200);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation dans updateDocument: ' . json_encode($e->errors()));
            return response()->json(['error' => 'Erreur de validation', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans updateDocument: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la mise à jour du document.'], 500);
        }
    }

    /**
     * Supprime un document (soft delete).
     */
    public function destroyDocument($id)
    {
        try {
            $document = DocumentProgramme::findOrFail($id);
            $document->delete();
            Log::info('Document supprimé:', ['id' => $id]);
            return response()->json(['message' => 'Document supprimé avec succès.']);
        } catch (\Exception $e) {
            Log::error('Erreur dans destroyDocument: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la suppression du document.'], 500);
        }
    }

    /**
     * Liste les documents associés à un programme.
     */
    public function indexDocuments(Request $request)
    {
        try {
            $programmeId = $request->query('programme_id');
            $query = DocumentProgramme::query();

            if ($programmeId) {
                $query->where('programme_id', $programmeId);
            }

            $documents = $query->get();
            Log::info('Documents récupérés', ['count' => $documents->count(), 'programme_id' => $programmeId]);
            return response()->json(['data' => $documents], 200);
        } catch (\Exception $e) {
            Log::error('Erreur dans indexDocuments: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du chargement des documents.'], 500);
        }
    }

    /**
     * Télécharge un document PDF.
     */
    public function downloadDocument($id)
    {
        try {
            $document = DocumentProgramme::findOrFail($id);
            Log::info('Tentative de téléchargement du document', ['id' => $id]);

            if (!$document->fichier) {
                Log::error('Fichier non trouvé pour le téléchargement', ['id' => $id]);
                return response()->json(['error' => 'Fichier non trouvé.'], 404);
            }

            // Décoder le fichier base64
            $fileContent = base64_decode($document->fichier);
            if ($fileContent === false) {
                Log::error('Erreur de décodage du fichier base64', ['id' => $id]);
                return response()->json(['error' => 'Fichier corrompu ou invalide.'], 500);
            }

            // Générer une réponse avec le contenu du fichier
            $fileName = $document->titre ? str_replace(' ', '_', $document->titre) . '.pdf' : 'document.pdf';
            return response($fileContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Document non trouvé pour le téléchargement', ['id' => $id]);
            return response()->json(['error' => 'Document non trouvé.'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du document', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur lors du téléchargement du document.'], 500);
        }
    }
}
