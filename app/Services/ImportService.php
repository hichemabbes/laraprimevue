<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

// Import du service

class ImportService implements ToCollection, WithHeadingRow
{
    protected $tableName;

    protected $errors = [];

    protected $importErrorService;

    public function __construct($tableName, ImportErrorService $importErrorService)
    {
        $this->tableName = $tableName;
        $this->importErrorService = $importErrorService;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Logique de validation et d'importation
                $this->importRow($row);
            } catch (\Exception $e) {
                // Enregistrer les erreurs via ImportErrorService
                $this->importErrorService->logError(
                    $this->tableName,
                    $row,
                    $e->getMessage()
                );

                // Ajouter l'erreur à la liste des erreurs
                $this->errors[] = [
                    'row' => $row,
                    'message' => $e->getMessage(),
                ];
            }
        }
    }

    protected function importRow($row)
    {
        // Implémentez la logique spécifique à chaque table
        throw new \Exception('Méthode importRow() doit être implémentée dans la classe enfant.');
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
