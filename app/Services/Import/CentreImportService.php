<?php

namespace App\Services\Import;

use App\Models\Centre\Centre;
use App\Models\Liste;
use Exception;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CentreImportService
{
    protected $listsCache = [];

    public function import($file)
    {
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            array_shift($rows);

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

            foreach ($listFields as $config) {
                $this->listsCache[$config['listName']] = Liste::where('nom_fr', $config['listName'])
                    ->where('statut', 'Actif')
                    ->first();
            }

            foreach ($rows as $index => $row) {
                try {
                    if (Centre::where('code', $row[0])->whereNull('deleted_at')->exists()) {
                        $existingCount++;
                        $errorLines[] = [
                            'line' => $index + 2,
                            'message' => "Ce code '{$row[0]}' est déjà utilisé.",
                            'data' => implode('|', array_map(fn ($item) => is_null($item) ? '' : (string) $item, array_pad($row, 20, ''))),
                        ];

                        continue;
                    }

                    $centreData = $this->mapRowToCentreData($row, $listFields);
                    $this->importRow($centreData);
                    $successCount++;
                } catch (Exception $e) {
                    $errorLines[] = [
                        'line' => $index + 2,
                        'message' => $e->getMessage(),
                        'data' => implode('|', array_map(fn ($item) => is_null($item) ? '' : (string) $item, array_pad($row, 20, ''))),
                    ];
                    Log::error('Erreur à la ligne '.($index + 2).": {$e->getMessage()}", ['row' => $row]);
                }
            }

            return [
                'success_count' => $successCount,
                'existing_count' => $existingCount,
                'error_count' => count($errorLines) - $existingCount,
                'error_lines' => $errorLines,
            ];
        } catch (Exception $e) {
            Log::error("Erreur lors de l'importation: {$e->getMessage()}");
            throw new Exception("Erreur serveur: {$e->getMessage()}");
        }
    }

    protected function mapRowToCentreData(array $row, array $listFields): array
    {
        $dateCreation = ! empty($row[16]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[16]) : null;
        $dateOuverture = ! empty($row[17]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[17]) : null;
        $dateFermeture = ! empty($row[18]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[18]) : null;

        $gouvernorat_ar = $this->getArabicValue($row[10] ?? null, 'Gouvernorats');
        $type_centre_ar = $this->getArabicValue($row[11] ?? null, 'Types Centres');
        $classe_ar = $this->getArabicValue($row[12] ?? null, 'Classes Centres');
        $economat_ar = $this->getArabicValue($row[13] ?? null, 'Economats');
        $etat_ar = $this->getArabicValue($row[14] ?? null, 'Etats Centres');
        $statut_ar = $this->getArabicValue($row[15] ?? null, 'Statuts Centres');

        return [
            'code' => $row[0] ?? null,
            'nom_fr' => $row[1] ?? null,
            'nom_ar' => $row[2] ?? null,
            'adresse_fr' => $row[3] ?? null,
            'adresse_ar' => $row[4] ?? null,
            'telephone_1' => $row[5] ?? null,
            'telephone_2' => $row[6] ?? null,
            'fax_1' => $row[7] ?? null,
            'fax_2' => $row[8] ?? null,
            'email' => $row[9] ?? null,
            'gouvernorat_fr' => $row[10] ?? null,
            'gouvernorat_ar' => $gouvernorat_ar,
            'type_centre_fr' => $row[11] ?? null,
            'type_centre_ar' => $type_centre_ar,
            'classe_fr' => $row[12] ?? null,
            'classe_ar' => $classe_ar,
            'economat_fr' => $row[13] ?? null,
            'economat_ar' => $economat_ar,
            'etat_fr' => $row[14] ?? null,
            'etat_ar' => $etat_ar,
            'statut_fr' => $row[15] ?? null,
            'statut_ar' => $statut_ar,
            'date_creation' => $dateCreation ? $dateCreation->format('Y-m-d') : null,
            'date_ouverture' => $dateOuverture ? $dateOuverture->format('Y-m-d') : null,
            'date_fermeture' => $dateFermeture ? $dateFermeture->format('Y-m-d') : null,
            'observation_fr' => $row[19] ?? null,
        ];
    }

    public function importRow(array $row): Centre
    {
        Log::info('importRow appelé avec données:', ['row' => $row]);

        if (! isset($row['code']) || empty(trim($row['code']))) {
            throw new Exception('Le code est obligatoire.');
        }
        if (Centre::where('code', $row['code'])->whereNull('deleted_at')->exists()) {
            throw new Exception("Ce code '{$row['code']}' est déjà utilisé.");
        }
        if (! isset($row['nom_fr']) || empty(trim($row['nom_fr']))) {
            throw new Exception('Le nom en français est obligatoire.');
        }
        if (! isset($row['adresse_fr']) || empty(trim($row['adresse_fr']))) {
            throw new Exception('L\'adresse en français est obligatoire.');
        }
        if (! isset($row['gouvernorat_fr']) || empty(trim($row['gouvernorat_fr']))) {
            throw new Exception('Le gouvernorat est obligatoire.');
        }
        if (! isset($row['statut_fr']) || empty(trim($row['statut_fr']))) {
            throw new Exception('Le statut est obligatoire.');
        }

        if (! empty($row['telephone_1'])) {
            $row['telephone_1'] = preg_replace('/[^+\d]/', '', $row['telephone_1']);
            if (! preg_match('/^\+?\d{8,15}$/', $row['telephone_1'])) {
                throw new Exception('Le téléphone 1 doit contenir entre 8 et 15 chiffres.');
            }
        }
        if (! empty($row['telephone_2'])) {
            $row['telephone_2'] = preg_replace('/[^+\d]/', '', $row['telephone_2']);
            if (! preg_match('/^\+?\d{8,15}$/', $row['telephone_2'])) {
                throw new Exception('Le téléphone 2 doit contenir entre 8 et 15 chiffres.');
            }
        }
        if (! empty($row['fax_1'])) {
            $row['fax_1'] = preg_replace('/[^+\d]/', '', $row['fax_1']);
            if (! preg_match('/^\+?\d{8,15}$/', $row['fax_1'])) {
                throw new Exception('Le fax 1 doit contenir entre 8 et 15 chiffres.');
            }
        }
        if (! empty($row['fax_2'])) {
            $row['fax_2'] = preg_replace('/[^+\d]/', '', $row['fax_2']);
            if (! preg_match('/^\+?\d{8,15}$/', $row['fax_2'])) {
                throw new Exception('Le fax 2 doit contenir entre 8 et 15 chiffres.');
            }
        }
        if (! empty($row['email']) && ! filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('L\'email est invalide.');
        }

        if (! empty($row['date_ouverture']) && ! empty($row['date_creation']) && $row['date_ouverture'] < $row['date_creation']) {
            throw new Exception('La date d\'ouverture doit être postérieure ou égale à la date de création.');
        }
        if (! empty($row['date_fermeture']) && ! empty($row['date_ouverture']) && $row['date_fermeture'] < $row['date_ouverture']) {
            throw new Exception('La date de fermeture doit être postérieure ou égale à la date d\'ouverture.');
        }

        $listFields = [
            'gouvernorat_fr' => 'Gouvernorats',
            'type_centre_fr' => 'Types Centres',
            'classe_fr' => 'Classes Centres',
            'economat_fr' => 'Economats',
            'etat_fr' => 'Etats Centres',
            'statut_fr' => 'Statuts Centres',
        ];

        foreach ($listFields as $field => $listName) {
            if (! empty($row[$field])) {
                $row[$field] = $this->mapListField($row[$field], $listName, $field);
                $row[$field.'_ar'] = $this->getArabicValue($row[$field], $listName);
            }
        }

        Log::info('Création du centre avec données:', ['row' => $row]);

        return Centre::create($row);
    }

    protected function mapListField(string $value, string $listName, string $field): string
    {
        $liste = $this->listsCache[$listName] ?? Liste::where('nom_fr', $listName)->where('statut', 'Actif')->first();
        if (! $liste || empty($liste->options)) {
            Log::warning("Liste '$listName' introuvable ou vide pour '$field'.", ['value' => $value]);
            throw new Exception("Liste '$listName' non disponible.");
        }

        $value = trim($value);
        foreach ($liste->options as $option) {
            if (strtolower($option['nom_fr']) === strtolower($value)) {
                return $option['nom_fr'];
            }
        }

        Log::warning("Valeur '$value' invalide pour '$field' dans '$listName'.", ['options' => $liste->options]);
        throw new Exception("Valeur invalide pour $field.");
    }

    protected function getArabicValue(?string $value, string $listName): ?string
    {
        if (empty($value)) {
            return null;
        }
        $liste = $this->listsCache[$listName] ?? Liste::where('nom_fr', $listName)->where('statut', 'Actif')->first();
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
