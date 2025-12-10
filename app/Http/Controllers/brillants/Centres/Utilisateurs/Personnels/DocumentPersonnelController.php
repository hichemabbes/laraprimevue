<?php

namespace App\Http\Controllers\brillants\Centres\Utilisateurs\Personnels;

use App\Http\Controllers\Controller;
use App\Models\Centre\Utilisateurs\Personnels\DocumentPersonnel;
use App\Models\Centre\Utilisateurs\Personnels\UserCentre;
use App\Models\Liste;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentPersonnelController extends Controller
{
    public function index(Request $request)
    {
        Log::info('Fetching documents', ['request' => $request->all()]);
        try {
            $validator = Validator::make($request->all(), [
                'personnel_id' => 'required|exists:personnels_centres,id',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed', ['errors' => $validator->errors()]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $query = DocumentPersonnel::with('personnel')->withoutGlobalScopes();
            $personnelId = $request->input('personnel_id');
            Log::info('Filtering documents by personnel_id', ['personnel_id' => $personnelId]);
            $query->where('personnel_id', $personnelId);
            $documents = $query->get();
            Log::info('Documents fetched', ['count' => $documents->count()]);
            return response()->json($documents);
        } catch (\Exception $e) {
            Log::error('Error fetching documents:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors de la récupération des documents.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Storing new document', ['request' => $request->all()]);
        $validator = Validator::make($request->all(), [
            'personnel_id' => 'required|exists:personnels_centres,id',
            'type_doc_fr' => 'required|string',
            'type_doc_ar' => 'nullable|string',
            'document_path' => 'nullable|string',
            'date_depot' => 'nullable|date',
            'validite_doc_fr' => 'nullable|in:Valide,Non Valide',
            'validite_doc_ar' => 'nullable|string',
            'type_depot_fr' => 'nullable|in:Bureau d\'ordre,Par Poste,Par mail,Par fax',
            'type_depot_ar' => 'nullable|string',
            'description_fr' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'observation_fr' => 'nullable|string',
            'observation_ar' => 'nullable|string',
            'statut' => 'nullable|in:Actif,Inactif'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if (!empty($data['document_path'])) {
            try {
                $personnel = PersonnelCentre::findOrFail($data['personnel_id']);
                $userCentre = UserCentre::findOrFail($personnel->user_centre_id);
                if (!$userCentre) {
                    Log::error('No centre associated with personnel', ['personnel_id' => $data['personnel_id'], 'user_centre_id' => $personnel->user_centre_id]);
                    return response()->json(['error' => 'Aucun centre associé à ce personnel.'], 400);
                }
                $centreId = $userCentre->centre_id;

                $centreDir = "centres/{$centreId}/personnels/{$data['personnel_id']}";

                if (!Storage::disk('public')->exists("centres/{$centreId}")) {
                    Storage::disk('public')->makeDirectory("centres/{$centreId}");
                    Log::info('Centre directory created', ['path' => "centres/{$centreId}"]);
                }

                if (!Storage::disk('public')->exists($centreDir)) {
                    Storage::disk('public')->makeDirectory($centreDir);
                    Log::info('Personnel directory created', ['path' => $centreDir]);
                }

                $base64String = preg_replace('#^data:[\w/]+;base64,#i', '', $data['document_path']);
                $fileData = base64_decode($base64String);
                $extension = $this->getFileExtension($data['document_path']);
                $fileName = "{$centreDir}/" . uniqid('doc_', true) . '.' . $extension;
                Storage::disk('public')->put($fileName, $fileData);
                $data['document_path'] = $fileName;
                Log::info('File stored', ['path' => $fileName]);
            } catch (\Exception $e) {
                Log::error('File storage failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Erreur lors du stockage du fichier.'], 500);
            }
        }

        $document = DocumentPersonnel::create($data);
        Log::info('Document created', ['document' => $document]);

        return response()->json($document, 201);
    }

    public function update(Request $request, $id)
    {
        Log::info('Updating document', ['id' => $id, 'request' => $request->all()]);
        $document = DocumentPersonnel::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'personnel_id' => 'required|exists:personnels_centres,id',
            'type_doc_fr' => 'required|string',
            'type_doc_ar' => 'nullable|string',
            'document_path' => 'nullable|string',
            'date_depot' => 'nullable|date',
            'validite_doc_fr' => 'nullable|in:Valide,Non Valide',
            'validite_doc_ar' => 'nullable|string',
            'type_depot_fr' => 'nullable|in:Bureau d\'ordre,Par Poste,Par mail,Par fax',
            'type_depot_ar' => 'nullable|string',
            'description_fr' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'observation_fr' => 'nullable|string',
            'observation_ar' => 'nullable|string',
            'statut' => 'nullable|in:Actif,Inactif'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if (!empty($data['document_path']) && $data['document_path'] !== $document->document_path) {
            try {
                $personnel = PersonnelCentre::findOrFail($data['personnel_id']);
                $userCentre = UserCentre::findOrFail($personnel->user_centre_id);
                if (!$userCentre) {
                    Log::error('No centre associated with personnel', ['personnel_id' => $data['personnel_id'], 'user_centre_id' => $personnel->user_centre_id]);
                    return response()->json(['error' => 'Aucun centre associé à ce personnel.'], 400);
                }
                $centreId = $userCentre->centre_id;

                $centreDir = "centres/{$centreId}/personnels/{$data['personnel_id']}";

                if (!Storage::disk('public')->exists("centres/{$centreId}")) {
                    Storage::disk('public')->makeDirectory("centres/{$centreId}");
                    Log::info('Centre directory created', ['path' => "centres/{$centreId}"]);
                }

                if (!Storage::disk('public')->exists($centreDir)) {
                    Storage::disk('public')->makeDirectory($centreDir);
                    Log::info('Personnel directory created', ['path' => $centreDir]);
                }

                if ($document->document_path) {
                    Storage::disk('public')->delete($document->document_path);
                    Log::info('Old file deleted', ['path' => $document->document_path]);
                }

                $base64String = preg_replace('#^data:[\w/]+;base64,#i', '', $data['document_path']);
                $fileData = base64_decode($base64String);
                $extension = $this->getFileExtension($data['document_path']);
                $fileName = "{$centreDir}/" . uniqid('doc_', true) . '.' . $extension;
                Storage::disk('public')->put($fileName, $fileData);
                $data['document_path'] = $fileName;
                Log::info('New file stored', ['path' => $fileName]);
            } catch (\Exception $e) {
                Log::error('File storage failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Erreur lors du stockage du fichier.'], 500);
            }
        }

        $document->update($data);
        Log::info('Document updated', ['document' => $document]);

        return response()->json($document);
    }

    public function show($id)
    {
        Log::info('Fetching document', ['id' => $id]);
        try {
            $document = DocumentPersonnel::with('personnel')->findOrFail($id);
            return response()->json($document);
        } catch (\Exception $e) {
            Log::error('Error fetching document:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors de la récupération du document.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        Log::info('Deleting document', ['id' => $id]);
        $document = DocumentPersonnel::findOrFail($id);
        if ($document->document_path) {
            Storage::disk('public')->delete($document->document_path);
            Log::info('File deleted', ['path' => $document->document_path]);
        }
        $document->delete();
        return response()->json(null, 204);
    }

    public function downloadDocument(Request $request, $id)
    {
        Log::info('Downloading document', ['id' => $id]);
        try {
            $document = DocumentPersonnel::findOrFail($id);
            if (!$document->document_path) {
                Log::error('No file associated with document', ['id' => $id]);
                return response()->json(['error' => 'Aucun fichier associé à ce document.'], 404);
            }

            $filePath = storage_path('app/public/' . $document->document_path);
            if (!Storage::disk('public')->exists($document->document_path)) {
                Log::error('File does not exist', ['path' => $document->document_path]);
                return response()->json(['error' => 'Le fichier n\'existe pas.'], 404);
            }

            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
            ];
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
            $fileName = basename($document->document_path);

            return response()->download($filePath, $fileName, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Error downloading document:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors du téléchargement du document.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function getDocumentTypes()
    {
        Log::info('Fetching document types');
        try {
            $liste = Liste::where('nom_fr', 'Types Documents Administratifs PersonnelsDirectionCentrale')->first();
            Log::info('Liste found:', ['liste' => $liste ? $liste->toArray() : null]);
            $documentTypes = $liste ? $liste->options : [];
            Log::info('Document Types fetched:', ['documentTypes' => $documentTypes]);
            if (empty($documentTypes) || !is_array($documentTypes)) {
                Log::warning('No valid document types found, using fallback data');
                $documentTypes = [
                    ['nom_fr' => 'Copie CIN', 'nom_ar' => 'نسخة بطاقة التعريف', 'valeur' => 'cin'],
                    ['nom_fr' => 'CV', 'nom_ar' => 'السيرة الذاتية', 'valeur' => 'cv'],
                    ['nom_fr' => 'Copie diplôme', 'nom_ar' => 'نسخة الشهادة', 'valeur' => 'diplome'],
                    ['nom_fr' => 'Attestation de travail', 'nom_ar' => 'شهادة عمل', 'valeur' => 'attestation_travail'],
                    ['nom_fr' => 'Fiche de paie', 'nom_ar' => 'إيصال الأجر', 'valeur' => 'fiche_paie'],
                    ['nom_fr' => 'Contrat de travail', 'nom_ar' => 'عقد العمل', 'valeur' => 'contrat_travail']
                ];
            }
            return response()->json([
                'document_types' => $documentTypes
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getDocumentTypes:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur serveur lors de la récupération des types de documents.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    protected function getFileExtension($base64String)
    {
        $mime = explode(';', $base64String)[0];
        $mime = str_replace('data:', '', $mime);
        switch ($mime) {
            case 'application/pdf':
                return 'pdf';
            case 'image/png':
                return 'png';
            case 'image/jpeg':
                return 'jpg';
            default:
                throw new \Exception('Invalid file type');
        }
    }
}
