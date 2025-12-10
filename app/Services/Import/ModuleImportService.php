<?php

namespace App\Services\Import;

use App\Models\brillants\Specialites\Modules\Module;
use App\Models\brillants\Specialites\Programmes\Programme;
use Exception;

class ModuleImportService extends ImportService
{
    public function __construct()
    {
        parent::__construct('modules');
    }

    protected function importRow($row, $programme_id)
    {
        $code = trim($row['code'] ?? '');
        $titre_module = trim($row['titre_module'] ?? '');
        $type_module_fr = trim($row['type_module_fr'] ?? '');
        $heures_theoriques = trim($row['heures_theoriques'] ?? '');
        $heures_pratiques = trim($row['heures_pratiques'] ?? '');
        $heures_evaluation = trim($row['heures_evaluation'] ?? '');
        $statut = trim($row['statut'] ?? '');
        $description = trim($row['description'] ?? '');
        $observation = trim($row['observation'] ?? '');

        // Normalisation de la casse pour type_module_fr
        $type_module_fr = $type_module_fr ? ucfirst(strtolower($type_module_fr)) : '';

        \Log::debug('ModuleImportService: Traitement de la ligne', [
            'code' => $code,
            'type_module_fr_raw' => $row['type_module_fr'] ?? 'N/A',
            'type_module_fr_normalized' => $type_module_fr,
        ]);

        if (empty($code)) {
            throw new Exception("Le champ 'Code' est requis.");
        }
        if (empty($titre_module)) {
            throw new Exception("Le champ 'Titre DocumentProgrammeSpecialite' est requis.");
        }

        $programme = Programme::find($programme_id);
        if (!$programme) {
            throw new Exception("Le programme avec l'ID '{$programme_id}' n'existe pas.");
        }
        if ($programme->trashed()) {
            throw new Exception("Le programme avec l'ID '{$programme_id}' est supprimé.");
        }

        $existingModule = Module::where('code', $code)
            ->where('programme_id', $programme_id)
            ->whereNull('deleted_at')
            ->first();
        if ($existingModule) {
            throw new Exception("Un module avec le code '{$code}' existe déjà pour ce programme.");
        }

        // Validation souple pour type_module_fr
        $validTypes = ['Spécifique', 'Général', 'Stage'];
        if ($type_module_fr && !in_array($type_module_fr, $validTypes)) {
            \Log::warning('Type de module non valide', [
                'type_module_fr' => $type_module_fr,
                'valid_types' => $validTypes,
            ]);
            $type_module_fr = null; // Défaut à null si non valide
        }

        if ($heures_theoriques && !is_numeric($heures_theoriques)) {
            throw new Exception('Les heures théoriques doivent être un nombre.');
        }

        if ($heures_pratiques && !is_numeric($heures_pratiques)) {
            throw new Exception('Les heures pratiques doivent être un nombre.');
        }

        if ($heures_evaluation && !is_numeric($heures_evaluation)) {
            throw new Exception("Les heures d'évaluation doivent être un nombre.");
        }

        if ($statut && !in_array($statut, ['Actif', 'Inactif'])) {
            throw new Exception("Le statut doit être l'un des suivants : Actif, Inactif.");
        }

        \Log::debug('ModuleImportService: Création du module', [
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
