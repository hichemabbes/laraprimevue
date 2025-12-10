<?php

namespace App\Http\Controllers\brillants\ATFP\Specialites;

use App\Http\Controllers\Controller;
use App\Models\Annees\AnneeFormation;
use App\Models\brillants\Specialites\InfoSpecialite;
use App\Models\brillants\Specialites\Specialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class InfoSpecialiteController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'specialite_id' => 'required|exists:specialites,id',
                'annee_formation_id' => 'required|exists:annees_formations,id',
                'homologation_fr' => 'nullable|in:Homologuée,Non Homologuée',
                'homologation_ar' => 'nullable|in:منظر,غير منظر',
                'heures_et' => 'nullable|integer|min:0',
                'heures_eg' => 'nullable|integer|min:0',
                'duree_formation' => 'nullable|numeric|min:0|max:9.9|regex:/^\d+(\.\d{1})?$/',
                'statut' => 'nullable|in:Active,Non Active,Annulée,Remplacée',
                'observation' => 'nullable|string|max:65535',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation échouée pour store: '.json_encode($validator->errors()));

                return response()->json(['message' => 'Validation échouée', 'errors' => $validator->errors()], 422);
            }

            $existingInfo = InfoSpecialite::where('specialite_id', $request->specialite_id)
                ->where('annee_formation_id', $request->annee_formation_id)
                ->first();

            if ($existingInfo) {
                return response()->json(['message' => 'Une information existe déjà pour cette spécialité et cette année.'], 422);
            }

            $infoSpecialite = InfoSpecialite::create($request->all());

            return response()->json($infoSpecialite, 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'ajout d\'info spécialité: '.$e->getMessage());

            return response()->json(['message' => 'Erreur lors de l\'ajout de l\'information.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $infoSpecialite = InfoSpecialite::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'specialite_id' => 'sometimes|exists:specialites,id',
                'annee_formation_id' => 'sometimes|exists:annees_formations,id',
                'homologation_fr' => 'nullable|in:Homologuée,Non Homologuée',
                'homologation_ar' => 'nullable|in:منظر,غير منظر',
                'heures_et' => 'nullable|integer|min:0',
                'heures_eg' => 'nullable|integer|min:0',
                'duree_formation' => 'nullable|numeric|min:0|max:9.9|regex:/^\d+(\.\d{1})?$/',
                'statut' => 'nullable|in:Active,Non Active,Annulée,Remplacée',
                'observation' => 'nullable|string|max:65535',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation échouée pour update: '.json_encode($validator->errors()));

                return response()->json(['message' => 'Validation échouée', 'errors' => $validator->errors()], 422);
            }

            $infoSpecialite->update($request->all());

            return response()->json($infoSpecialite);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour d\'info spécialité: '.$e->getMessage());

            return response()->json(['message' => 'Erreur lors de la mise à jour de l\'information.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $infoSpecialite = InfoSpecialite::findOrFail($id);
            $infoSpecialite->delete();

            return response()->json(['message' => 'Information supprimée avec succès']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression d\'info spécialité: '.$e->getMessage());

            return response()->json(['message' => 'Erreur lors de la suppression de l\'information.'], 500);
        }
    }

    public function deleteSelected(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'exists:infos_specialites,id',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation échouée pour deleteSelected: '.json_encode($validator->errors()));

                return response()->json(['message' => 'Validation échouée', 'errors' => $validator->errors()], 422);
            }

            InfoSpecialite::whereIn('id', $request->ids)->delete();

            return response()->json(['message' => 'Informations supprimées avec succès']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression des infos spécialités: '.$e->getMessage());

            return response()->json(['message' => 'Erreur lors de la suppression des informations.'], 500);
        }
    }

    public function importXls(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xls,xlsx',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation échouée pour importXls: '.json_encode($validator->errors()));

                return response()->json(['message' => 'Fichier invalide', 'errors' => $validator->errors()], 422);
            }

            $errorLines = [];
            $rows = Excel::toArray([], $request->file('file'))[0];
            $header = array_shift($rows);

            foreach ($rows as $index => $row) {
                $data = array_combine($header, $row);
                $validator = Validator::make($data, [
                    'Code' => 'required|exists:specialites,code',
                    'Homologation' => 'nullable|in:Homologuée,Non Homologuée',
                    'Heures Techniques' => 'nullable|integer|min:0',
                    'Heures Généraux' => 'nullable|integer|min:0',
                    'Durée de Formation' => 'nullable|numeric|min:0|max:9.9',
                    'Statut' => 'nullable|in:Active,Non Active,Annulée,Remplacée',
                    'Observation' => 'nullable|string|max:65535',
                ]);

                if ($validator->fails()) {
                    $errorLines[] = ['line' => $index + 2, 'errors' => $validator->errors()];

                    continue;
                }

                $specialite = Specialite::where('code', $data['Code'])->first();
                if ($specialite) {
                    InfoSpecialite::updateOrCreate(
                        [
                            'specialite_id' => $specialite->id,
                            'annee_formation_id' => $request->annee_formation_id ?? AnneeFormation::latest()->first()->id,
                        ],
                        [
                            'homologation_fr' => $data['Homologation'] ?? null,
                            'homologation_ar' => $data['Homologation'] === 'Homologuée' ? 'منظر' : ($data['Homologation'] === 'Non Homologuée' ? 'غير منظر' : null),
                            'heures_et' => $data['Heures Techniques'] ?? null,
                            'heures_eg' => $data['Heures Généraux'] ?? null,
                            'duree_formation' => $data['Durée de Formation'] ?? null,
                            'statut' => $data['Statut'] ?? null,
                            'observation' => $data['Observation'] ?? null,
                        ]
                    );
                } else {
                    $errorLines[] = ['line' => $index + 2, 'errors' => ['Code' => 'Spécialité non trouvée']];
                }
            }

            return response()->json(['message' => 'Importation terminée', 'error_lines' => $errorLines]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'importation XLS: '.$e->getMessage());

            return response()->json(['message' => 'Échec de l\'importation', 'error' => $e->getMessage()], 500);
        }
    }
}
