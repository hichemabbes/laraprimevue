<?php

namespace App\Http\Controllers\brillants\ATFP\Specialites;

use App\Http\Controllers\Controller;
use App\Models\brillants\Specialites\Programmes\DocumentProgramme;
use App\Models\brillants\Specialites\Specialite;
use App\Models\Liste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DocumentController extends Controller
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
            return [];
        }

        $types = [];
        foreach ($liste->options as $option) {
            $types[$option['nom_fr']] = $option['nom_ar'];
        }
        return $types;
    }

    public function index(Request $request)
    {
        try {
            $specialiteId = $request->query('specialite_id');
            $query = DocumentProgramme::with(['programme.specialite']);

            if ($specialiteId) {
                $query->whereHas('programme', function ($q) use ($specialiteId) {
                    $q->where('specialite_id', $specialiteId);
                });
            }

            $documents = $query->get();
            Log::info('Documents récupérés', ['count' => $documents->count(), 'specialite_id' => $specialiteId]);

            return response()->json([
                'status' => 'success',
                'data' => $documents,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des documents: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des documents.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $documentTypes = $this->getDocumentTypes();
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
                        if ($documentTypes[$request->type_document_fr] !== $value) {
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

            // Mettre à jour le statut des autres documents si le nouveau document est Actif
            if ($validated['statut'] === 'Actif') {
                DocumentProgramme::where('programme_id', $validated['programme_id'])
                    ->where('type_document_fr', $validated['type_document_fr'])
                    ->whereNull('deleted_at')
                    ->update(['statut' => 'Inactif']);
            }

            // Créer le document
            $document = DocumentProgramme::create($validated);
            Log::info('Document créé:', [
                'id' => $document->id,
                'titre' => $document->titre,
                'type_document_fr' => $document->type_document_fr,
                'programme_id' => $document->programme_id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Document créé avec succès.',
                'data' => $document->load(['programme.specialite']),
            ], 201);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation dans store: ' . json_encode($e->errors()));
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du document: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création du document: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $document = DocumentProgramme::with(['programme.specialite'])->findOrFail($id);
            Log::info('Document récupéré', ['id' => $id]);

            return response()->json([
                'status' => 'success',
                'data' => $document,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Document non trouvé', ['id' => $id]);

            return response()->json([
                'status' => 'error',
                'message' => 'Document non trouvé.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du document ID ' . $id . ': ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération du document.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $document = DocumentProgramme::findOrFail($id);
            $documentTypes = $this->getDocumentTypes();
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
                        if ($documentTypes[$request->type_document_fr] !== $value) {
                            $fail("Le type de document en arabe ne correspond pas au type en français.");
                        }
                    },
                ],
                'fichier' => 'nullable|file|mimes:pdf|max:2048',
                'statut' => 'required|string|in:Actif,Inactif',
                'description' => 'nullable|string|max:1000',
                'observation' => 'nullable|string|max:1000',
            ]);

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
            Log::info('Document mis à jour:', $document->toArray());

            return response()->json([
                'status' => 'success',
                'message' => 'Document mis à jour avec succès.',
                'data' => $document->load(['programme.specialite']),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Document non trouvé pour mise à jour', ['id' => $id]);

            return response()->json([
                'status' => 'error',
                'message' => 'Document non trouvé.',
            ], 404);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation dans update: ' . json_encode($e->errors()));

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du document ID ' . $id . ': ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du document: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $document = DocumentProgramme::findOrFail($id);
            $document->delete();
            Log::info('Document supprimé:', ['id' => $id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Document supprimé avec succès.',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Document non trouvé pour suppression', ['id' => $id]);

            return response()->json([
                'status' => 'error',
                'message' => 'Document non trouvé.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du document ID ' . $id . ': ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression du document.',
            ], 500);
        }
    }

    public function download($id)
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

    public function attachAnnees(Request $request, $id)
    {
        try {
            $document = DocumentProgramme::findOrFail($id);
            $validatedData = $request->validate([
                'annee_ids' => 'required|array',
                'annee_ids.*' => 'exists:annees_formations,id',
            ]);

            $document->annees()->sync($validatedData['annee_ids']);
            Log::info('Années attachées', ['document_id' => $id, 'annee_ids' => $validatedData['annee_ids']]);

            return response()->json([
                'status' => 'success',
                'message' => 'Années attachées avec succès.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'attachement des années au document ID ' . $id . ': ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'attachement des années.',
            ], 500);
        }
    }

    public function detachAnnees(Request $request, $id)
    {
        try {
            $document = DocumentProgramme::findOrFail($id);
            $validatedData = $request->validate([
                'annee_ids' => 'required|array',
                'annee_ids.*' => 'exists:annees_formations,id',
            ]);

            $document->annees()->detach($validatedData['annee_ids']);
            Log::info('Années détachées', ['document_id' => $id, 'annee_ids' => $validatedData['annee_ids']]);

            return response()->json([
                'status' => 'success',
                'message' => 'Années détachées avec succès.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors du détachement des années du document ID ' . $id . ': ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du détachement des années.',
            ], 500);
        }
    }

    public function getSpecialitesWithDocuments()
    {
        try {
            $specialites = Specialite::with(['programmes.documents'])->get();
            Log::info('Spécialités avec documents récupérées', ['count' => $specialites->count()]);

            return response()->json([
                'status' => 'success',
                'data' => $specialites,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des spécialités avec documents: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des spécialités.',
            ], 500);
        }
    }
}
