<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CentresErrorExport implements FromArray, WithHeadings, WithStyles
{
    protected $errorLines;

    public function __construct(array $errorLines)
    {
        $this->errorLines = $errorLines;
    }

    public function array(): array
    {
        return array_map(function ($errorLine) {
            $data = explode('|', $errorLine['data']);
            Log::debug('Données extraites pour la ligne', [
                'line' => $errorLine['line'],
                'data' => $data,
                'message' => $errorLine['message'],
            ]);

            return array_merge($data, [$errorLine['message']]);
        }, $this->errorLines);
    }

    public function headings(): array
    {
        return [
            'Code',
            'Nom (FR)',
            'Nom (AR)',
            'Adresse (FR)',
            'Adresse (AR)',
            'Téléphone 1',
            'Téléphone 2',
            'Fax 1',
            'Fax 2',
            'Email',
            'Gouvernorat (FR)',
            'Type Centre (FR)',
            'Classe (FR)',
            'Économat (FR)',
            'État (FR)',
            'Statut (FR)',
            'Date Création',
            'Date Ouverture',
            'Date Fermeture',
            'Observation (FR)',
            'Erreur',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $rows = $this->array();
        $styles = [];

        // Champs obligatoires et leur indice de colonne
        $requiredFields = [
            'code' => 0,           // Colonne A
            'nom_fr' => 1,         // Colonne B
            'adresse_fr' => 3,     // Colonne D
            'gouvernorat_fr' => 10, // Colonne K
            'statut_fr' => 15,     // Colonne P
        ];

        foreach ($rows as $rowIndex => $row) {
            $errorMessage = $row[count($row) - 1]; // Message d'erreur
            $errorFields = $this->getErrorFields($errorMessage);

            // Vérifier chaque champ obligatoire
            foreach ($requiredFields as $field => $fieldIndex) {
                // Vérifier si la valeur est vide ou absente
                $cellValue = isset($row[$fieldIndex]) ? trim($row[$fieldIndex]) : '';
                if ($cellValue === '') {
                    $errorFields[] = $fieldIndex;
                }
            }

            // Ajouter les champs non obligatoires invalides (par exemple, email, téléphone)
            $errorFields = array_unique($errorFields);
            Log::debug('Champs en erreur identifiés pour la ligne', [
                'rowIndex' => $rowIndex + 2,
                'errorFields' => $errorFields,
                'errorMessage' => $errorMessage,
            ]);

            // Appliquer le style aux cellules des champs en erreur
            foreach ($errorFields as $fieldIndex) {
                $cell = $sheet->getCellByColumnAndRow($fieldIndex + 1, $rowIndex + 2); // +2 pour header et index base 1
                $styles[$cell->getCoordinate()] = [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFF0000'], // Rouge
                    ],
                    'font' => [
                        'color' => ['argb' => 'FFFFFFFF'], // Blanc
                    ],
                ];
            }

            // Style pour la colonne des messages d'erreur
            $errorCell = $sheet->getCellByColumnAndRow(22, $rowIndex + 2); // Colonne 22 pour "Erreur"
            $styles[$errorCell->getCoordinate()] = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFF0000'], // Rouge
                ],
                'font' => [
                    'color' => ['argb' => 'FFFFFFFF'], // Blanc
                ],
            ];
        }

        // Style pour l'en-tête
        $styles[1] = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFCCCCCC'], // Gris clair
            ],
        ];

        return $styles;
    }

    protected function getErrorFields(string $errorMessage): array
    {
        // Mappage des champs aux indices des colonnes
        $fieldMap = [
            'code' => 0,
            'nom_fr' => 1,
            'nom_ar' => 2,
            'adresse_fr' => 3,
            'adresse_ar' => 4,
            'telephone_1' => 5,
            'telephone_2' => 6,
            'fax_1' => 7,
            'fax_2' => 8,
            'email' => 9,
            'gouvernorat_fr' => 10,
            'type_centre_fr' => 11,
            'classe_fr' => 12,
            'economat_fr' => 13,
            'etat_fr' => 14,
            'statut_fr' => 15,
            'date_creation' => 16,
            'date_ouverture' => 17,
            'date_fermeture' => 18,
            'observation_fr' => 19,
        ];

        // Mappage des messages d'erreur aux champs, avec variations
        $errorFieldMapping = [
            'le code est obligatoire' => ['code'],
            'le code est obligatoire.' => ['code'],
            'ce code est déjà utilisé' => ['code'],
            'ce code est déjà utilisé.' => ['code'],
            'le nom en français est obligatoire' => ['nom_fr'],
            'le nom en français est obligatoire.' => ['nom_fr'],
            'l\'adresse en français est obligatoire' => ['adresse_fr'],
            'l\'adresse en français est obligatoire.' => ['adresse_fr'],
            'le gouvernorat est obligatoire' => ['gouvernorat_fr'],
            'le gouvernorat est obligatoire.' => ['gouvernorat_fr'],
            'le statut est obligatoire' => ['statut_fr'],
            'le statut est obligatoire.' => ['statut_fr'],
            'le téléphone 1 doit contenir entre 8 et 15 chiffres' => ['telephone_1'],
            'le téléphone 1 doit contenir entre 8 et 15 chiffres.' => ['telephone_1'],
            'le téléphone 2 doit contenir entre 8 et 15 chiffres' => ['telephone_2'],
            'le téléphone 2 doit contenir entre 8 et 15 chiffres.' => ['telephone_2'],
            'le fax 1 doit contenir entre 8 et 15 chiffres' => ['fax_1'],
            'le fax 1 doit contenir entre 8 et 15 chiffres.' => ['fax_1'],
            'le fax 2 doit contenir entre 8 et 15 chiffres' => ['fax_2'],
            'le fax 2 doit contenir entre 8 et 15 chiffres.' => ['fax_2'],
            'l\'email est invalide' => ['email'],
            'l\'email est invalide.' => ['email'],
            'la date d\'ouverture doit être postérieure ou égale à la date de création' => ['date_ouverture', 'date_creation'],
            'la date d\'ouverture doit être postérieure ou égale à la date de création.' => ['date_ouverture', 'date_creation'],
            'la date de fermeture doit être postérieure ou égale à la date d\'ouverture' => ['date_fermeture', 'date_ouverture'],
            'la date de fermeture doit être postérieure ou égale à la date d\'ouverture.' => ['date_fermeture', 'date_ouverture'],
            'le gouvernorat n\'est pas valide' => ['gouvernorat_fr'],
            'le gouvernorat n\'est pas valide.' => ['gouvernorat_fr'],
            'le type de centre n\'est pas valide' => ['type_centre_fr'],
            'le type de centre n\'est pas valide.' => ['type_centre_fr'],
            'la classe n\'est pas valide' => ['classe_fr'],
            'la classe n\'est pas valide.' => ['classe_fr'],
            'l\'économat n\'est pas valide' => ['economat_fr'],
            'l\'économat n\'est pas valide.' => ['economat_fr'],
            'l\'état n\'est pas valide' => ['etat_fr'],
            'l\'état n\'est pas valide.' => ['etat_fr'],
            'le statut n\'est pas valide' => ['statut_fr'],
            'le statut n\'est pas valide.' => ['statut_fr'],
            'liste gouvernorats non disponible' => ['gouvernorat_fr'],
            'liste gouvernorats non disponible.' => ['gouvernorat_fr'],
            'liste types centres non disponible' => ['type_centre_fr'],
            'liste types centres non disponible.' => ['type_centre_fr'],
            'liste classes centres non disponible' => ['classe_fr'],
            'liste classes centres non disponible.' => ['classe_fr'],
            'liste economats non disponible' => ['economat_fr'],
            'liste economats non disponible.' => ['economat_fr'],
            'liste etats centres non disponible' => ['etat_fr'],
            'liste etats centres non disponible.' => ['etat_fr'],
            'liste statuts centres non disponible' => ['statut_fr'],
            'liste statuts centres non disponible.' => ['statut_fr'],
            'erreurs de validation' => array_keys($fieldMap), // Colorer tous les champs pour les erreurs génériques
            'validation failed' => array_keys($fieldMap), // Cas générique supplémentaire
        ];

        $errorFields = [];

        // Normaliser le message d'erreur en minuscules pour une correspondance insensible à la casse
        $errorMessage = strtolower(trim($errorMessage));
        Log::debug('Message d\'erreur normalisé', ['message' => $errorMessage]);

        // Vérifier chaque message d'erreur possible
        foreach ($errorFieldMapping as $message => $fields) {
            if (strpos($errorMessage, strtolower($message)) !== false) {
                foreach ($fields as $field) {
                    if (isset($fieldMap[$field]) && ! in_array($fieldMap[$field], $errorFields)) {
                        $errorFields[] = $fieldMap[$field];
                    }
                }
            }
        }

        return array_unique($errorFields);
    }
}
