<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Models\brillants\Specialites\Modules\Module;
use App\Models\brillants\Specialites\Programmes\Programme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ModuleImportController extends Controller
{
    public function importxls(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:2048',
            'programme_id' => 'required|exists:programmes_etudes,id',
        ]);

        $file = $request->file('file');
        $programme_id = $request->input('programme_id');

        try {
            $programme = Programme::findOrFail($programme_id);
            if ($programme->trashed()) {
                throw new \Exception('Le programme est supprimé.');
            }

            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            $header = array_shift($rows); // Récupérer l'en-tête pour vérification

            // Log de l'en-tête pour vérifier l'ordre des colonnes
            Log::debug('En-tête du fichier Excel', ['header' => $header]);

            $errorLines = [];

            foreach ($rows as $index => $row) {
                try {
                    // Vérifier le nombre de colonnes
                    if (count($row) < 9) {
                        Log::warning('Ligne incomplète', [
                            'line' => $index + 2,
                            'row' => $row,
                        ]);
                    }

                    // Normalisation des données
                    $code = trim($row[0] ?? '');
                    $titre_module = trim($row[1] ?? '');
                    $type_module_fr = trim($row[2] ?? '');
                    $heures_theoriques = trim($row[3] ?? '');
                    $heures_pratiques = trim($row[4] ?? '');
                    $heures_evaluation = trim($row[5] ?? '');
                    $statut = trim($row[6] ?? '');
                    $description = trim($row[7] ?? '');
                    $observation = trim($row[8] ?? '');

                    // Normalisation de la casse pour type_module_fr
                    $type_module_fr = $type_module_fr ? ucfirst(strtolower($type_module_fr)) : '';

                    // Log détaillé pour type_module_fr
                    Log::debug('Traitement de la ligne Excel', [
                        'line' => $index + 2,
                        'row' => $row,
                        'type_module_fr_raw' => $row[2] ?? 'N/A',
                        'type_module_fr_normalized' => $type_module_fr,
                    ]);

                    if (empty($code)) {
                        throw new \Exception("Le champ 'Code' est requis.");
                    }
                    if (empty($titre_module)) {
                        throw new \Exception("Le champ 'Titre DocumentProgrammeSpecialite' est requis.");
                    }

                    $existingModule = Module::where('code', $code)
                        ->where('programme_id', $programme_id)
                        ->whereNull('deleted_at')
                        ->first();
                    if ($existingModule) {
                        throw new \Exception("Un module avec le code '{$code}' existe déjà pour ce programme.");
                    }

                    // Validation souple pour type_module_fr
                    $validTypes = ['Spécifique', 'Général', 'Stage'];
                    if ($type_module_fr && !in_array($type_module_fr, $validTypes)) {
                        Log::warning('Type de module non valide', [
                            'type_module_fr' => $type_module_fr,
                            'valid_types' => $validTypes,
                        ]);
                        $type_module_fr = null; // Défaut à null si non valide
                    }

                    if ($heures_theoriques && !is_numeric($heures_theoriques)) {
                        throw new \Exception('Les heures théoriques doivent être un nombre.');
                    }

                    if ($heures_pratiques && !is_numeric($heures_pratiques)) {
                        throw new \Exception('Les heures pratiques doivent être un nombre.');
                    }

                    if ($heures_evaluation && !is_numeric($heures_evaluation)) {
                        throw new \Exception("Les heures d'évaluation doivent être un nombre.");
                    }

                    if ($statut && !in_array($statut, ['Actif', 'Inactif'])) {
                        throw new \Exception("Le statut doit être l'un des suivants : Actif, Inactif.");
                    }

                    // Log avant la création du module
                    Log::debug('Création du module', [
                        'code' => $code,
                        'type_module_fr' => $type_module_fr ?: 'N/A',
                        'type_module_ar' => $type_module_fr ? $this->mapTypeModuleAr($type_module_fr) : 'N/A',
                    ]);

                    Module::create([
                        'code' => $code,
                        'titre_module' => $titre_module,
                        'type_module_fr' => $type_module_fr ?: null,
                        'type_module_ar' => $type_module_fr ? $this->mapTypeModuleAr($type_module_fr) : null,
                        'heures_theoriques' => $heures_theoriques ? (int) $heures_theoriques : null,
                        'heures_pratiques' => $heures_pratiques ? (int) $heures_pratiques : null,
                        'heures_evaluation' => $heures_evaluation ? (int) $heures_evaluation : null,
                        'programme_id' => $programme_id,
                        'statut' => $statut ?: 'Actif',
                        'description' => $description ?: null,
                        'observation' => $observation ?: null,
                    ]);
                } catch (\Exception $e) {
                    $errorLines[] = [
                        'line' => $index + 2,
                        'message' => $e->getMessage(),
                        'data' => $row,
                    ];
                    Log::error('Erreur lors du traitement de la ligne', [
                        'line' => $index + 2,
                        'error' => $e->getMessage(),
                        'row' => $row,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Importation terminée.',
                'error_lines' => $errorLines,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'importation des modules : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur s\'est produite lors de l\'importation : ' . $e->getMessage(),
            ], 500);
        }
    }

    private function mapTypeModuleAr($type_module_fr)
    {
        $mapping = [
            'Spécifique' => 'تقني',
            'Général' => 'عام',
            'Stage' => 'تربص',
        ];
        return $mapping[$type_module_fr] ?? $type_module_fr;
    }
}
