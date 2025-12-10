<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Models\Centre\Centre;
use App\Models\Liste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CentreImportController extends Controller
{
    protected $listsCache = [];

    public function importxls(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xls,xlsx|max:2048']);
        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            array_shift($rows); // Remove header

            $errorLines = [];
            $successCount = 0;
            $existingCount = 0;
            $listFields = [
                'gouvernorat_fr' => ['index' => 10, 'listName' => 'Gouvernorats'],
                'type_centre_fr' => ['index' => 11, 'listName' => 'Types Centres'],
                'classe_fr' => ['index' => 12, 'listName' => 'Classes Centres'],
                'economat_fr' => ['index' => 13, 'listName' => 'Economats'],
                'etat_fr' => ['index' => 14, 'listName' => 'Etats Centres'],
                'statut_fr' => ['index' => 15, 'listName' => 'Statuts Centres'],
            ];

            foreach ($listFields as $field => $config) {
                $this->listsCache[$config['listName']] = Liste::where('nom_fr', $config['listName'])
                    ->where('statut', 'Actif')
                    ->first();
            }

            foreach ($rows as $index => $row) {
                try {
                    if (empty($row[0])) {
                        throw new \Exception('Le code est obligatoire.');
                    }
                    if (Centre::where('code', $row[0])->whereNull('deleted_at')->exists()) {
                        $existingCount++;
                        $errorLines[] = [
                            'line' => $index + 2,
                            'message' => "Ce code '{$row[0]}' est déjà utilisé.",
                            'data' => implode('|', array_map(fn ($item) => is_null($item) ? '' : (string) $item, array_pad($row, 20, ''))),
                        ];

                        continue;
                    }
                    if (empty($row[1])) {
                        throw new \Exception('Le nom en français est obligatoire.');
                    }
                    if (empty($row[3])) {
                        throw new \Exception("L'adresse en français est obligatoire.");
                    }
                    if (empty($row[10])) {
                        throw new \Exception('Le gouvernorat est obligatoire.');
                    }
                    if (empty($row[15])) {
                        throw new \Exception('Le statut est obligatoire.');
                    }

                    if (! empty($row[5])) {
                        $row[5] = preg_replace('/[^+\d]/', '', $row[5]);
                        if (! preg_match('/^\+?\d{8,15}$/', $row[5])) {
                            throw new \Exception('Le téléphone 1 doit contenir entre 8 et 15 chiffres.');
                        }
                    }
                    if (! empty($row[6])) {
                        $row[6] = preg_replace('/[^+\d]/', '', $row[6]);
                        if (! preg_match('/^\+?\d{8,15}$/', $row[6])) {
                            throw new \Exception('Le téléphone 2 doit contenir entre 8 et 15 chiffres.');
                        }
                    }
                    if (! empty($row[7])) {
                        $row[7] = preg_replace('/[^+\d]/', '', $row[7]);
                        if (! preg_match('/^\+?\d{8,15}$/', $row[7])) {
                            throw new \Exception('Le fax 1 doit contenir entre 8 et 15 chiffres.');
                        }
                    }
                    if (! empty($row[8])) {
                        $row[8] = preg_replace('/[^+\d]/', '', $row[8]);
                        if (! preg_match('/^\+?\d{8,15}$/', $row[8])) {
                            throw new \Exception('Le fax 2 doit contenir entre 8 et 15 chiffres.');
                        }
                    }
                    if (! empty($row[9]) && ! filter_var($row[9], FILTER_VALIDATE_EMAIL)) {
                        throw new \Exception("L'email est invalide.");
                    }

                    $dateCreation = ! empty($row[16]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[16]) : null;
                    $dateOuverture = ! empty($row[17]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[17]) : null;
                    $dateFermeture = ! empty($row[18]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[18]) : null;

                    if ($dateOuverture && $dateCreation && $dateOuverture < $dateCreation) {
                        throw new \Exception("La date d'ouverture doit être postérieure ou égale à la date de création.");
                    }
                    if ($dateFermeture && $dateOuverture && $dateFermeture < $dateOuverture) {
                        throw new \Exception('La date de fermeture doit être postérieure ou égale à la date d’ouverture.');
                    }

                    foreach ($listFields as $field => $config) {
                        if (! empty($row[$config['index']])) {
                            $row[$config['index']] = $this->mapListField($row[$config['index']], $config['listName'], $field);
                        }
                    }

                    $gouvernorat_ar = $this->getArabicValue($row[10], 'Gouvernorats');
                    $type_centre_ar = $this->getArabicValue($row[11], 'Types Centres');
                    $classe_ar = $this->getArabicValue($row[12], 'Classes Centres');
                    $economat_ar = $this->getArabicValue($row[13], 'Economats');
                    $etat_ar = $this->getArabicValue($row[14], 'Etats Centres');
                    $statut_ar = $this->getArabicValue($row[15], 'Statuts Centres');

                    Centre::create([
                        'code' => $row[0],
                        'nom_fr' => $row[1],
                        'nom_ar' => $row[2] ?? null,
                        'adresse_fr' => $row[3],
                        'adresse_ar' => $row[4] ?? null,
                        'telephone_1' => $row[5] ?? null,
                        'telephone_2' => $row[6] ?? null,
                        'fax_1' => $row[7] ?? null,
                        'fax_2' => $row[8] ?? null,
                        'email' => $row[9] ?? null,
                        'gouvernorat_fr' => $row[10],
                        'gouvernorat_ar' => $gouvernorat_ar,
                        'type_centre_fr' => $row[11] ?? null,
                        'type_centre_ar' => $type_centre_ar,
                        'classe_fr' => $row[12] ?? null,
                        'classe_ar' => $classe_ar,
                        'economat_fr' => $row[13] ?? null,
                        'economat_ar' => $economat_ar,
                        'etat_fr' => $row[14] ?? null,
                        'etat_ar' => $etat_ar,
                        'statut_fr' => $row[15],
                        'statut_ar' => $statut_ar,
                        'date_creation' => $dateCreation ? $dateCreation->format('Y-m-d') : null,
                        'date_ouverture' => $dateOuverture ? $dateOuverture->format('Y-m-d') : null,
                        'date_fermeture' => $dateFermeture ? $dateFermeture->format('Y-m-d') : null,
                        'observation_fr' => $row[19] ?? null,
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errorLines[] = [
                        'line' => $index + 2,
                        'message' => $e->getMessage(),
                        'data' => implode('|', array_map(fn ($item) => is_null($item) ? '' : (string) $item, array_pad($row, 20, ''))),
                    ];
                    Log::error('Erreur à la ligne '.($index + 2).": {$e->getMessage()}", ['row' => $row]);
                }
            }

            $errorCount = count($errorLines) - $existingCount;

            return response()->json([
                'success' => true,
                'message' => $errorCount === 0 && $existingCount === 0 ? 'Importation réussie.' : 'Importation terminée avec des erreurs.',
                'success_count' => $successCount,
                'existing_count' => $existingCount,
                'error_count' => $errorCount,
                'error_lines' => $errorLines,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'importation: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => "Erreur serveur: {$e->getMessage()}",
                'success_count' => 0,
                'existing_count' => 0,
                'error_count' => 0,
                'error_lines' => [],
            ], 500);
        }
    }

    protected function mapListField($value, $listName, $field)
    {
        $liste = $this->listsCache[$listName];
        if (! $liste || empty($liste->options)) {
            Log::warning("Liste '$listName' introuvable ou vide pour '$field'.", ['value' => $value]);
            throw new \Exception("Liste '$listName' non disponible.");
        }

        $value = trim($value);
        foreach ($liste->options as $option) {
            if (strtolower($option['nom_fr']) === strtolower($value)) {
                return $option['nom_fr'];
            }
        }

        Log::warning("Valeur '$value' invalide pour '$field' dans '$listName'.", ['options' => $liste->options]);
        throw new \Exception("Valeur invalide pour $field.");
    }

    protected function getArabicValue($value, $listName)
    {
        if (empty($value)) {
            return null;
        }

        $liste = $this->listsCache[$listName];
        if (! $liste || empty($liste->options)) {
            return null;
        }

        $value = trim($value);
        foreach ($liste->options as $option) {
            if (strtolower($option['nom_fr']) === strtolower($value)) {
                return $option['nom_ar'];
            }
        }

        return null;
    }
}
