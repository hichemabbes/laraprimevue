<?php

namespace App\Http\Controllers\brillants\Centres\Utilisateurs\Personnels;

use App\Http\Controllers\Controller;
use App\Models\Centre\Utilisateurs\Personnels\ContratPersonnel;
use App\Models\Centre\Utilisateurs\Personnels\UserCentre;
use App\Models\Liste;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContratPersonnelController extends Controller
{
    public function index(Request $request)
    {
        Log::info('Fetching all contracts', ['request' => $request->all()]);
        try {
            $validator = Validator::make($request->query(), [
                'personnel_id' => 'sometimes|exists:personnels_centres,id',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed', ['errors' => $validator->errors()]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $query = ContratPersonnel::with('personnel')->withoutGlobalScopes();
            if ($request->has('personnel_id')) {
                $query->where('personnel_id', $request->query('personnel_id'));
            }
            $contracts = $query->get();
            Log::info('Contracts fetched', ['count' => $contracts->count()]);
            return response()->json($contracts);
        } catch (\Exception $e) {
            Log::error('Error fetching contracts:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors de la récupération des contrats.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Storing new contract', ['request' => $request->all()]);
        $validator = Validator::make($request->all(), [
            'personnel_id' => 'required|exists:personnels_centres,id',
            'type_contrat_fr' => 'required|string',
            'type_contrat_ar' => 'nullable|string',
            'document_path' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'num_contrat' => 'required|string|unique:contrats_personnels,num_contrat',
            'num_decision' => 'nullable|string',
            'date_decision' => 'nullable|date',
            'heures_travail' => 'nullable|integer',
            'date_resiliation' => 'nullable|date',
            'motif_resiliation' => 'nullable|string',
            'description_fr' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'observation_fr' => 'nullable|string',
            'observation_ar' => 'nullable|string',
            'statut' => 'nullable|in:Actif,Terminé,Résilié',
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
                    Log::error('No centre associated with personnel', ['personnel_id' => $data['personnel_id'], 'user_centre_id' => $personnel->user_ccentre_id]);
                    return response()->json(['error' => 'Aucun centre associé à ce personnel.'], 400);
                }
                $centreId = $userCentre->centre_id;

                $centreDir = "centres/{$centreId}/personnels/{$data['personnel_id']}/contrats";

                if (!Storage::disk('public')->exists("centres/{$centreId}")) {
                    Storage::disk('public')->makeDirectory("centres/{$centreId}");
                    Log::info('Centre directory created', ['path' => "centres/{$centreId}"]);
                }

                if (!Storage::disk('public')->exists($centreDir)) {
                    Storage::disk('public')->makeDirectory($centreDir);
                    Log::info('Personnel contracts directory created', ['path' => $centreDir]);
                }

                $base64String = preg_replace('#^data:[\w/]+;base64,#i', '', $data['document_path']);
                $fileData = base64_decode($base64String);
                $extension = $this->getFileExtension($data['document_path']);
                $fileName = "{$centreDir}/" . uniqid('contrat_', true) . '.' . $extension;
                Storage::disk('public')->put($fileName, $fileData);
                $data['document_path'] = $fileName;
                Log::info('File stored', ['path' => $fileName]);
            } catch (\Exception $e) {
                Log::error('File storage failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Erreur lors du stockage du fichier.'], 500);
            }
        }

        $contrat = ContratPersonnel::create($data);
        Log::info('Contract created', ['contrat' => $contrat]);

        return response()->json($contrat, 201);
    }

    public function show(Request $request, $id)
    {
        Log::info('Fetching contract', ['contrat_id' => $id]);
        try {
            $contrat = ContratPersonnel::with('personnel')->findOrFail($id);
            return response()->json($contrat);
        } catch (\Exception $e) {
            Log::error('Error fetching contract:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors de la récupération du contrat.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Updating contract', ['contrat_id' => $id, 'request' => $request->all()]);
        $contrat = ContratPersonnel::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'personnel_id' => 'required|exists:personnels_centres,id',
            'type_contrat_fr' => 'required|string',
            'type_contrat_ar' => 'nullable|string',
            'document_path' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'num_contrat' => 'required|string|unique:contrats_personnels,num_contrat,' . $id,
            'num_decision' => 'nullable|string',
            'date_decision' => 'nullable|date',
            'heures_travail' => 'nullable|integer',
            'date_resiliation' => 'nullable|date',
            'motif_resiliation' => 'nullable|string',
            'description_fr' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'observation_fr' => 'nullable|string',
            'observation_ar' => 'nullable|string',
            'statut' => 'nullable|in:Actif,Terminé,Résilié',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if (!empty($data['document_path']) && $data['document_path'] !== $contrat->document_path) {
            try {
                $personnel = PersonnelCentre::findOrFail($data['personnel_id']);
                $userCentre = UserCentre::findOrFail($personnel->user_centre_id);
                if (!$userCentre) {
                    Log::error('No centre associated with personnel', ['personnel_id' => $data['personnel_id'], 'user_centre_id' => $personnel->user_centre_id]);
                    return response()->json(['error' => 'Aucun centre associé à ce personnel.'], 400);
                }
                $centreId = $userCentre->centre_id;

                $centreDir = "centres/{$centreId}/personnels/{$data['personnel_id']}/contrats";

                if (!Storage::disk('public')->exists("centres/{$centreId}")) {
                    Storage::disk('public')->makeDirectory("centres/{$centreId}");
                    Log::info('Centre directory created', ['path' => "centres/{$centreId}"]);
                }

                if (!Storage::disk('public')->exists($centreDir)) {
                    Storage::disk('public')->makeDirectory($centreDir);
                    Log::info('Personnel contracts directory created', ['path' => $centreDir]);
                }

                if ($contrat->document_path) {
                    Storage::disk('public')->delete($contrat->document_path);
                    Log::info('Old file deleted', ['path' => $contrat->document_path]);
                }

                $base64String = preg_replace('#^data:[\w/]+;base64,#i', '', $data['document_path']);
                $fileData = base64_decode($base64String);
                $extension = $this->getFileExtension($data['document_path']);
                $fileName = "{$centreDir}/" . uniqid('contrat_', true) . '.' . $extension;
                Storage::disk('public')->put($fileName, $fileData);
                $data['document_path'] = $fileName;
                Log::info('New file stored', ['path' => $fileName]);
            } catch (\Exception $e) {
                Log::error('File storage failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Erreur lors du stockage du fichier.'], 500);
            }
        }

        $contrat->update($data);
        Log::info('Contract updated', ['contrat' => $contrat]);

        return response()->json($contrat);
    }

    public function destroy(Request $request, $id)
    {
        Log::info('Deleting contract', ['contrat_id' => $id]);
        $contrat = ContratPersonnel::findOrFail($id);
        if ($contrat->document_path) {
            Storage::disk('public')->delete($contrat->document_path);
            Log::info('File deleted', ['path' => $contrat->document_path]);
        }
        $contrat->delete();
        return response()->json(null, 204);
    }

    public function downloadContract(Request $request, $id)
    {
        Log::info('Downloading contract', ['contrat_id' => $id]);
        try {
            $contrat = ContratPersonnel::findOrFail($id);
            if (!$contrat->document_path) {
                Log::error('No file associated with contract', ['contrat_id' => $id]);
                return response()->json(['error' => 'Aucun fichier associé à ce contrat.'], 404);
            }

            $filePath = storage_path('app/public/' . $contrat->document_path);
            if (!Storage::disk('public')->exists($contrat->document_path)) {
                Log::error('File does not exist', ['path' => $contrat->document_path]);
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
            $fileName = basename($contrat->document_path);

            return response()->download($filePath, $fileName, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Error downloading contract:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors du téléchargement du contrat.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function getContractTypes()
    {
        Log::info('Fetching contract types');
        try {
            $liste = Liste::where('nom_fr', 'Types Contrats PersonnelsDirectionCentrale')->first();
            Log::info('Liste found:', ['liste' => $liste ? $liste->toArray() : null]);
            $contractTypes = $liste && isset($liste->options) ? $liste->options : [];
            Log::info('Contract Types fetched:', ['contractTypes' => $contractTypes]);

            if (empty($contractTypes) || !is_array($contractTypes)) {
                Log::warning('No valid contract types found, using fallback data');
                $contractTypes = [
                    ['nom_fr' => 'Contrat à Durée Indéterminée (CDI)', 'nom_ar' => 'عقد لمدة غير محدوة', 'valeur' => 'cdi'],
                    ['nom_fr' => 'Contrat à Durée Déterminée (CDD)', 'nom_ar' => 'عقد لمدة محدوة', 'valeur' => 'cdd'],
                    ['nom_fr' => 'Contrat de Vacation', 'nom_ar' => 'عقد عمل مؤقت', 'valeur' => 'vacation'],
                    ['nom_fr' => 'Contrat d\'Intérim', 'nom_ar' => 'عقد نيابة', 'valeur' => 'interim']
                ];
            }
            return response()->json([
                'contract_types' => $contractTypes
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getContractTypes:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur serveur lors de la récupération des types de contrats.',
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
