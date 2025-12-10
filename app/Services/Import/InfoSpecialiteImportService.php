<?php

namespace App\Services\Import;

use App\Models\Annees\AnneeFormation;
use App\Models\brillants\Specialites\InfoSpecialite;
use App\Models\brillants\Specialites\Specialite;
use Exception;

class InfoSpecialiteImportService extends ImportService
{
    public function __construct()
    {
        parent::__construct('infos_specialites');
    }

    protected function importRow($row)
    {
        // Vérification des champs requis
        if (! isset($row['Code']) || trim($row['Code']) === '') {
            throw new Exception("Le champ 'Code' est requis.");
        }
        if (! isset($row['Année']) || trim($row['Année']) === '') {
            throw new Exception("Le champ 'Année' est requis.");
        }

        // Vérification de l'existence de la spécialité
        $specialite = Specialite::where('code', trim($row['Code']))->first();
        if (! $specialite) {
            throw new Exception("La spécialité avec le code '{$row['Code']}' n'existe pas.");
        }

        // Vérification de l'existence de l'année
        $annee = AnneeFormation::where('intitule', trim($row['Année']))->first();
        if (! $annee) {
            throw new Exception("L'année '{$row['Année']}' n'existe pas.");
        }

        // Mappage des valeurs d'homologation
        $homologationMapping = [
            'Homologuée' => 'منظر',
            'Non Homologuée' => 'غير منظر',
        ];

        // Préparation des données
        $data = [
            'specialite_id' => $specialite->id,
            'annee_formation_id' => $annee->id,
            'homologation_fr' => isset($row['Homologation']) && in_array(trim($row['Homologation']), ['Homologuée', 'Non Homologuée']) ? trim($row['Homologation']) : null,
            'homologation_ar' => isset($row['Homologation']) && isset($homologationMapping[trim($row['Homologation'])]) ? $homologationMapping[trim($row['Homologation'])] : null,
            'heures_et' => isset($row['Heures Techniques']) && is_numeric($row['Heures Techniques']) && $row['Heures Techniques'] >= 0 ? (int) $row['Heures Techniques'] : null,
            'heures_eg' => isset($row['Heures Généraux']) && is_numeric($row['Heures Généraux']) && $row['Heures Généraux'] >= 0 ? (int) $row['Heures Généraux'] : null,
            'duree_formation' => isset($row['Durée de Formation']) && is_numeric($row['Durée de Formation']) && $row['Durée de Formation'] >= 0 && $row['Durée de Formation'] <= 9.9 ? (float) $row['Durée de Formation'] : null,
            'statut' => isset($row['Statut']) && in_array(trim($row['Statut']), ['Active', 'Non Active', 'Annulée', 'Remplacée']) ? trim($row['Statut']) : null,
            'observation' => isset($row['Observation']) ? trim($row['Observation']) : null,
        ];

        try {
            InfoSpecialite::updateOrCreate(
                [
                    'specialite_id' => $specialite->id,
                    'annee_formation_id' => $annee->id,
                ],
                $data
            );
        } catch (\Throwable $th) {
            throw new Exception("Erreur lors de la création ou mise à jour de l'enregistrement : ".$th->getMessage());
        }
    }
}
