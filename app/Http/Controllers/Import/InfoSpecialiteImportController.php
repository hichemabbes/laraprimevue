<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Models\Annees\AnneeFormation;
use App\Models\brillants\Specialites\InfoSpecialite;
use App\Models\brillants\Specialites\Specialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InfoSpecialiteImportController extends Controller
{
    public function importxls(Request $request)
    {
        // Validation du fichier
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:2048',
        ]);

        $file = $request->file('file');

        try {
            // Chargement du fichier Excel
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Suppression de l'en-tête
            $header = array_shift($rows);
            $header = array_map('trim', $header);

            // Mappage des colonnes
            $columnMap = [
                'Code' => 0,
                'Année' => 1,
                'Homologation' => 2,
                'Heures Techniques' => 3,
                'Heures Généraux' => 4,
                'Durée de Formation' => 5,
                'Statut' => 6,
                'Observation' => 7,
            ];

            $errorLines = [];
            $homologationMapping = [
                'Homologuée' => 'منظر',
                'Non Homologuée' => 'غير منظر',
            ];

            foreach ($rows as $index => $row) {
                try {
                    // Vérification des champs requis
                    if (empty(trim($row[$columnMap['Code']])) || empty(trim($row[$columnMap['Année']]))) {
                        throw new \Exception("Données manquantes pour 'Code' ou 'Année'.");
                    }

                    // Vérification de la spécialité
                    $specialite = Specialite::where('code', trim($row[$columnMap['Code']]))->first();
                    if (! $specialite) {
                        throw new \Exception("Spécialité avec le code '{$row[$columnMap['Code']]}' introuvable.");
                    }

                    // Vérification de l'année
                    $annee = AnneeFormation::where('intitule', trim($row[$columnMap['Année']]))->first();
                    if (! $annee) {
                        throw new \Exception("Année '{$row[$columnMap['Année']]}' introuvable.");
                    }

                    // Préparation des données
                    $data = [
                        'specialite_id' => $specialite->id,
                        'annee_formation_id' => $annee->id,
                        'homologation_fr' => isset($row[$columnMap['Homologation']]) && in_array(trim($row[$columnMap['Homologation']]), ['Homologuée', 'Non Homologuée']) ? trim($row[$columnMap['Homologation']]) : null,
                        'homologation_ar' => isset($row[$columnMap['Homologation']]) && isset($homologationMapping[trim($row[$columnMap['Homologation']])]) ? $homologationMapping[trim($row[$columnMap['Homologation']])] : null,
                        'heures_et' => isset($row[$columnMap['Heures Techniques']]) && is_numeric($row[$columnMap['Heures Techniques']]) && $row[$columnMap['Heures Techniques']] >= 0 ? (int) $row[$columnMap['Heures Techniques']] : null,
                        'heures_eg' => isset($row[$columnMap['Heures Généraux']]) && is_numeric($row[$columnMap['Heures Généraux']]) && $row[$columnMap['Heures Généraux']] >= 0 ? (int) $row[$columnMap['Heures Généraux']] : null,
                        'duree_formation' => isset($row[$columnMap['Durée de Formation']]) && is_numeric($row[$columnMap['Durée de Formation']]) && $row[$columnMap['Durée de Formation']] >= 0 && $row[$columnMap['Durée de Formation']] <= 9.9 ? (float) $row[$columnMap['Durée de Formation']] : null,
                        'statut' => isset($row[$columnMap['Statut']]) && in_array(trim($row[$columnMap['Statut']]), ['Active', 'Non Active', 'Annulée', 'Remplacée']) ? trim($row[$columnMap['Statut']]) : null,
                        'observation' => isset($row[$columnMap['Observation']]) ? trim($row[$columnMap['Observation']]) : null,
                    ];

                    // Création ou mise à jour de l'enregistrement
                    InfoSpecialite::updateOrCreate(
                        [
                            'specialite_id' => $specialite->id,
                            'annee_formation_id' => $annee->id,
                        ],
                        $data
                    );
                } catch (\Exception $e) {
                    $errorLines[] = [
                        'line' => $index + 2,
                        'message' => $e->getMessage(),
                        'data' => array_combine(array_keys($columnMap), array_map(fn ($i) => isset($row[$i]) ? $row[$i] : null, array_values($columnMap))),
                    ];
                }
            }

            if (empty($errorLines)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Importation réussie.',
                    'error_lines' => [],
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Importation terminée avec des erreurs.',
                    'error_lines' => $errorLines,
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'importation : '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Une erreur s\'est produite lors de l\'importation : '.$e->getMessage(),
                'error_lines' => [],
            ], 500);
        }
    }

    public function importErrors()
    {
        // À implémenter si un modèle ImportError existe
        return response()->json([], 200);
    }

    public function destroyImportError($id)
    {
        // À implémenter si un modèle ImportError existe
        return response()->json(['message' => 'Non implémenté'], 501);
    }
}
