<?php

namespace App\Services\Import;

use App\Models\brillants\Specialites\Secteurs\Secteur;
use Exception;

class SecteurImportService extends ImportService
{
    public function __construct()
    {
        parent::__construct('secteurs');
    }

    protected function importRow($row)
    {
        // Validation des données
        if (empty($row['code'])) {
            throw new Exception("Le champ 'code' est requis.");
        }
        if (strlen($row['code']) > 20) {
            throw new Exception('Le code dépasse 20 caractères.');
        }

        if (empty($row['nom_ar'])) {
            throw new Exception("Le champ 'nom_ar' est requis.");
        }

        if (empty($row['standardisation_ar'])) {
            throw new Exception("Le champ 'standardisation_ar' est requis.");
        }
        if (! in_array($row['standardisation_ar'], ['مقيس', 'غير مقيس'])) {
            throw new Exception("Le champ 'standardisation_ar' doit être 'مقيس' ou 'غير مقيس'.");
        }

        // Valider statut
        if (! empty($row['statut']) && ! in_array($row['statut'], ['Actif', 'Inactif'])) {
            throw new Exception("Le champ 'statut' doit être 'Actif' ou 'Inactif'.");
        }

        // Vérifier si un secteur actif avec le même code et standardisation_ar existe déjà
        $existingSecteur = Secteur::where('code', $row['code'])
            ->where('standardisation_ar', $row['standardisation_ar'])
            ->whereNull('deleted_at')
            ->first();

        if ($existingSecteur) {
            throw new Exception("Un secteur avec le code '{$row['code']}' et la standardisation '{$row['standardisation_ar']}' existe déjà.");
        }

        // Mapping pour standardisation_fr
        $standardisationFr = $row['standardisation_ar'] === 'مقيس' ? 'Standardisé' : 'Non Standardisé';

        // Création du secteur
        Secteur::create([
            'code' => $row['code'],
            'nom_fr' => $row['nom_fr'] ?? null,
            'nom_ar' => $row['nom_ar'],
            'standardisation_fr' => $standardisationFr,
            'standardisation_ar' => $row['standardisation_ar'],
            'statut' => $row['statut'] ?? null,
            'date_creation' => ! empty($row['date_creation']) ? date('Y-m-d', strtotime($row['date_creation'])) : null,
            'date_annulation' => ! empty($row['date_annulation']) ? date('Y-m-d', strtotime($row['date_annulation'])) : null,
            'observation' => $row['observation'] ?? null,
        ]);
    }
}
