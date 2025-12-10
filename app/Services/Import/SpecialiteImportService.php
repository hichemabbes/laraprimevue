<?php

namespace App\Services\Import;

use App\Models\brillants\Specialites\Secteurs\Secteur;
use App\Models\brillants\Specialites\SousSecteurs\SousSecteur;
use App\Models\brillants\Specialites\Specialite;
use App\Models\Liste;
use Exception;

class SpecialiteImportService extends ImportService
{
    public function __construct()
    {
        parent::__construct('specialites');
    }

    protected function importRow($row)
    {
        // Liste statique des statuts basée sur la migration
        $statutNamesFr = ['Active', 'Annulée', 'Remplacée'];

        // Récupérer les options dynamiques depuis la table listes
        $diplomas = Liste::where('nom_fr', 'Diplomes')->first()->options ?? [];
        $standardisations = Liste::where('nom_fr', 'Standardisation')->first()->options ?? [];

        $diplomaNamesFr = collect($diplomas)->pluck('nom_fr')->toArray();
        $standardisationNamesAr = collect($standardisations)->pluck('nom_ar')->toArray();

        // Extraction des données de la ligne
        $code = trim($row['code'] ?? '');
        $nomFr = trim($row['nom_fr'] ?? '');
        $nomAr = trim($row['nom_ar'] ?? '');
        $secteurCode = trim($row['code_secteur'] ?? '');
        $sousSecteurCode = trim($row['code_sous_secteur'] ?? '');
        $standardisationAr = trim($row['type'] ?? '');
        $diplomeFr = trim($row['diplome_fr'] ?? '');
        $dateCreation = trim($row['date_creation'] ?? '');
        $statut = trim($row['statut'] ?? '');
        $observation = trim($row['observation'] ?? '');
        $dateAnnulation = trim($row['date_annulation'] ?? '');

        // Validations
        if (empty($code)) {
            throw new Exception("Le champ 'Code' est requis.");
        }
        if (empty($nomAr)) {
            throw new Exception("Le champ 'Nom (AR)' est requis.");
        }
        if (empty($standardisationAr)) {
            throw new Exception("Le champ 'Type' est requis.");
        }
        if (! in_array($standardisationAr, $standardisationNamesAr)) {
            throw new Exception("Le champ 'Type' doit être l'une des valeurs suivantes : ".implode(', ', $standardisationNamesAr).'.');
        }
        if (Specialite::where('code', $code)->whereNull('deleted_at')->exists()) {
            throw new Exception("Une spécialité avec le code '{$code}' existe déjà.");
        }

        // Validation du secteur
        $secteur = null;
        if ($standardisationAr !== 'جديد') {
            if (empty($secteurCode)) {
                throw new Exception("Le champ 'Code Secteur' est requis pour les types 'مقيس' et 'غير مقيس'.");
            }
            $secteur = Secteur::where('code', $secteurCode)
                ->where('standardisation_ar', $standardisationAr)
                ->whereNull('deleted_at')
                ->first();
            if (! $secteur) {
                throw new Exception("Aucun secteur de type '{$standardisationAr}' avec le code '{$secteurCode}' n'existe.");
            }
        }

        // Validation du sous-secteur
        $sousSecteur = null;
        if ($standardisationAr === 'مقيس') {
            if (empty($sousSecteurCode)) {
                throw new Exception("Le champ 'Code Sous-Secteur' est requis pour le type 'مقيس'.");
            }
            $sousSecteur = SousSecteur::where('code', $sousSecteurCode)
                ->where('secteur_id', $secteur->id)
                ->whereNull('deleted_at')
                ->first();
            if (! $sousSecteur) {
                throw new Exception("Le sous-secteur avec le code '{$sousSecteurCode}' n'existe pas pour ce secteur.");
            }
        }

        // Validation du diplôme
        if (empty($diplomeFr)) {
            throw new Exception("Le champ 'Diplôme (FR)' est requis.");
        }
        if (! in_array($diplomeFr, $diplomaNamesFr)) {
            throw new Exception("Le diplôme '{$diplomeFr}' n'existe pas dans la liste des diplômes.");
        }
        $diplome = collect($diplomas)->firstWhere('nom_fr', $diplomeFr);

        // Validation du statut
        if (empty($statut)) {
            throw new Exception("Le champ 'Statut' est requis.");
        }
        if (! in_array($statut, $statutNamesFr)) {
            throw new Exception("Le statut '{$statut}' n'existe pas dans la liste des statuts.");
        }

        // Mappage de standardisation_fr
        $standardisation = collect($standardisations)->firstWhere('nom_ar', $standardisationAr);
        $standardisationFr = $standardisation ? $standardisation['nom_fr'] : null;

        // Création de la spécialité
        Specialite::create([
            'code' => $code,
            'nom_fr' => $nomFr ?: null,
            'nom_ar' => $nomAr,
            'secteur_id' => $secteur ? $secteur->id : null,
            'sous_secteur_id' => $sousSecteur ? $sousSecteur->id : null,
            'standardisation_ar' => $standardisationAr,
            'standardisation_fr' => $standardisationFr,
            'diplome_fr' => $diplome['nom_fr'],
            'diplome_ar' => $diplome['nom_ar'],
            'date_creation' => $dateCreation ?: null,
            'statut' => $statut,
            'observation' => $observation ?: null,
            'date_annulation' => $dateAnnulation ?: null,
        ]);
    }
}
