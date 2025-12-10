<?php

namespace App\Services;

use PDO;

class ImportErrorService
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function logError(string $tableName, array $rowData, string $errorMessage): void
    {
        $sql = 'INSERT INTO `import_errors` (table_name, row_data, error_message, created_at, updated_at)
                VALUES (:table_name, :row_data, :error_message, NOW(), NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':table_name' => $tableName,
            ':row_data' => json_encode($rowData),
            ':error_message' => $errorMessage,
        ]);
    }
}
