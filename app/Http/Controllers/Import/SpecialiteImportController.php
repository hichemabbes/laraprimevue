<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Models\brillants\Specialites\Secteurs\Secteur;
use App\Models\brillants\Specialites\SousSecteurs\SousSecteur;
use App\Models\brillants\Specialites\Specialite;
use App\Models\Liste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SpecialiteImportController extends Controller
{
    public function importxls(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:2048',
        ]);

        $file = $request->file('file');

        try {
            // Liste statique des statuts basée sur la migration
            $statutNamesFr = ['Active', 'Annulée', 'Remplacée'];

            // Récupérer les options dynamiques depuis la table listes
            $diplomas = Liste::where('nom_fr', 'Diplomes')->first()->options ?? [];
            $standardisations = Liste::where('nom_fr', 'Standardisation')->first()->options ?? [];

            $diplomaNamesFr = collect($diplomas)->pluck('nom_fr')->toArray();
            $standardisationNamesAr = collect($standardisations)->pluck('nom_ar')->toArray();

            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            $header = array_shift($rows);

            $errorLines = [];

            foreach ($rows as $index => $row) {
                try {
                    $rowData = array_combine($header, array_map('trim', $row));

                    $code = $rowData['Code'] ?? '';
                    $nomFr = $rowData['Nom (FR)'] ?? '';
                    $nomAr = $rowData['Nom (AR)'] ?? '';
                    $secteurCode = $rowData['Code Secteur'] ?? '';
                    $sousSecteurCode = $rowData['Code Sous-Secteur'] ?? '';
                    $standardisationAr = $rowData['Type'] ?? '';
                    $diplomeFr = $rowData['Diplôme (FR)'] ?? '';
                    $dateCreation = $rowData['Date Création'] ?? '';
                    $statut = $rowData['Statut'] ?? '';
                    $observation = $rowData['Observation'] ?? '';
                    $dateAnnulation = $rowData['Date Annulation'] ?? '';

                    // Validations
                    if (empty($code)) {
                        throw new \Exception('Le code est requis.');
                    }
                    if (empty($nomAr)) {
                        throw new \Exception("Le champ 'Nom (AR)' est requis.");
                    }
                    if (empty($standardisationAr)) {
                        throw new \Exception("Le champ 'Type' est requis.");
                    }
                    if (! in_array($standardisationAr, $standardisationNamesAr)) {
                        throw new \Exception('Type de spécialité invalide. Valeurs autorisées : '.implode(', ', $standardisationNamesAr));
                    }
                    if (Specialite::where('code', $code)->whereNull('deleted_at')->exists()) {
                        throw new \Exception("Une spécialité avec le code '{$code}' existe déjà.");
                    }

                    // Validation du secteur
                    $secteur = null;
                    if ($standardisationAr !== 'جديد') {
                        if (empty($secteurCode)) {
                            throw new \Exception("Le champ 'Code Secteur' est requis pour les types 'مقيس' et 'غير مقيس'.");
                        }
                        $secteur = Secteur::where('code', $secteurCode)
                            ->where('standardisation_ar', $standardisationAr)
                            ->whereNull('deleted_at')
                            ->first();
                        if (! $secteur) {
                            throw new \Exception("Aucun secteur de type '{$standardisationAr}' avec le code '{$secteurCode}' n'existe.");
                        }
                    }

                    // Validation du sous-secteur
                    $sousSecteur = null;
                    if ($standardisationAr === 'مقيس') {
                        if (empty($sousSecteurCode)) {
                            throw new \Exception("Le champ 'Code Sous-Secteur' est requis pour le type 'مقيس'.");
                        }
                        $sousSecteur = SousSecteur::where('code', $sousSecteurCode)
                            ->where('secteur_id', $secteur->id)
                            ->whereNull('deleted_at')
                            ->first();
                        if (! $sousSecteur) {
                            throw new \Exception("Le sous-secteur avec le code '{$sousSecteurCode}' n'existe pas pour ce secteur.");
                        }
                    }

                    // Validation du diplôme
                    if (empty($diplomeFr)) {
                        throw new \Exception("Le champ 'Diplôme (FR)' est requis.");
                    }
                    if (! in_array($diplomeFr, $diplomaNamesFr)) {
                        throw new \Exception("Le diplôme '{$diplomeFr}' n'existe pas dans la liste des diplômes.");
                    }
                    $diplome = collect($diplomas)->firstWhere('nom_fr', $diplomeFr);

                    // Validation du statut
                    if (empty($statut)) {
                        throw new \Exception("Le champ 'Statut' est requis.");
                    }
                    if (! in_array($statut, $statutNamesFr)) {
                        throw new \Exception("Le statut '{$statut}' n'existe pas dans la liste des statuts.");
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
                } catch (\Exception $e) {
                    $errorLines[] = [
                        'line' => $index + 2,
                        'message' => $e->getMessage(),
                        'data' => implode(',', array_map('trim', $row)),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Importation terminée.',
                'error_lines' => $errorLines,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'importation : '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Une erreur s\'est produite lors de l\'importation.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function importErrors()
    {
        // Cette méthode semble obsolète car les erreurs sont renvoyées directement dans la réponse
        return response()->json([], 200);
    }

    public function destroyImportError($id)
    {
        // Cette méthode semble obsolète car les erreurs ne sont pas stockées dans une table séparée
        return response()->json(['message' => 'Fonctionnalité non implémentée'], 404);
    }
}
