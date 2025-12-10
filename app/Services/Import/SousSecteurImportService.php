<?php

namespace App\Services\Import;

use App\Models\brillants\Specialites\Secteurs\Secteur;
use App\Models\brillants\Specialites\SousSecteurs\SousSecteur;
use Exception;

class SousSecteurImportService extends ImportService
{
    public function __construct()
    {
        parent::__construct('sous_secteurs');
    }

    protected function importRow($row)
    {
        // Extract and trim row data
        $code = trim($row['code'] ?? '');
        $nom_fr = trim($row['nom_fr'] ?? '');
        $nom_ar = trim($row['nom_ar'] ?? '');
        $code_secteur = trim($row['code_secteur'] ?? '');
        $statut = trim($row['statut'] ?? '');
        $date_creation = trim($row['date_creation'] ?? '');
        $date_annulation = trim($row['date_annulation'] ?? '');
        $observation = trim($row['observation'] ?? '');

        // Validate required fields
        if (empty($code)) {
            throw new Exception("Le champ 'Code' est requis.");
        }
        if (empty($nom_ar)) {
            throw new Exception("Le champ 'Nom (AR)' est requis.");
        }
        if (empty($code_secteur)) {
            throw new Exception("Le champ 'Code Secteur' est requis.");
        }

        // Validate secteur existence and status
        $secteur = Secteur::where('code', $code_secteur)->first();
        if (! $secteur) {
            throw new Exception("Le secteur avec le code '{$code_secteur}' n'existe pas.");
        }
        if ($secteur->trashed()) {
            throw new Exception("Le secteur avec le code '{$code_secteur}' est supprimé.");
        }

        // Check for duplicate sous-secteur code within the same standardisation
        $existingSousSecteur = SousSecteur::where('code', $code)
            ->whereHas('secteur', function ($query) use ($secteur) {
                $query->where('standardisation_ar', $secteur->standardisation_ar);
            })
            ->whereNull('deleted_at')
            ->first();
        if ($existingSousSecteur) {
            throw new Exception("Un sous-secteur avec le code '{$code}' existe déjà pour cette standardisation.");
        }

        // Validate statut
        if ($statut && ! in_array($statut, ['Actif', 'Inactif'])) {
            throw new Exception("Le statut doit être l'un des suivants : Actif, Inactif.");
        }

        // Validate date formats
        if ($date_creation && ! \DateTime::createFromFormat('Y-m-d', $date_creation)) {
            throw new Exception('Format de date de création invalide (attendu : AAAA-MM-JJ).');
        }
        if ($date_annulation && ! \DateTime::createFromFormat('Y-m-d', $date_annulation)) {
            throw new Exception("Format de date d'annulation invalide (attendu : AAAA-MM-JJ).");
        }
        if ($date_creation && $date_annulation && $date_creation > $date_annulation) {
            throw new Exception("La date de création doit être antérieure ou égale à la date d'annulation.");
        }

        // Validate field lengths
        if (strlen($code) > 20) {
            throw new Exception('Le code ne doit pas dépasser 20 caractères.');
        }
        if ($nom_fr && strlen($nom_fr) > 100) {
            throw new Exception('Le nom en français ne doit pas dépasser 100 caractères.');
        }
        if (strlen($nom_ar) > 100) {
            throw new Exception('Le nom en arabe ne doit pas dépasser 100 caractères.');
        }
        if ($observation && strlen($observation) > 1000) {
            throw new Exception("L'observation ne doit pas dépasser 1000 caractères.");
        }

        // Create the sous-secteur
        SousSecteur::create([
            'code' => $code,
            'nom_fr' => $nom_fr ?: null,
            'nom_ar' => $nom_ar,
            'secteur_id' => $secteur->id,
            'statut' => $statut ?: null,
            'date_creation' => $date_creation ?: null,
            'date_annulation' => $date_annulation ?: null,
            'observation' => $observation ?: null,
        ]);
    }
}
