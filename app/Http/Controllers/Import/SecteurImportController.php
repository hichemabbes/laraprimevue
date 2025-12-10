<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Models\brillants\Specialites\Secteurs\Secteur;
use App\Models\ImportError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SecteurImportController extends Controller
{
    public function importxls(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:2048',
        ]);

        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Ignorer l'en-tête
            array_shift($rows);

            $errorLines = [];

            foreach ($rows as $index => $row) {
                try {
                    // Valider les données de la ligne
                    if (empty($row[0]) || empty($row[2]) || empty($row[4])) {
                        throw new \Exception('Données manquantes (code, nom_ar ou standardisation_ar requis).');
                    }

                    // Valider la longueur des champs
                    if (strlen($row[0]) > 20) {
                        throw new \Exception('Le code dépasse 20 caractères.');
                    }

                    // Valider standardisation_ar
                    if (! in_array($row[4], ['مقيس', 'غير مقيس'])) {
                        throw new \Exception("Standardisation_ar invalide. Doit être 'مقيس' ou 'غير مقيس'.");
                    }

                    // Valider statut
                    if (! empty($row[5]) && ! in_array($row[5], ['Actif', 'Inactif'])) {
                        throw new \Exception("Statut invalide. Doit être 'Actif' ou 'Inactif'.");
                    }

                    // Vérifier si un secteur actif avec le même code et standardisation_ar existe déjà
                    $existingSecteur = Secteur::where('code', $row[0])
                        ->where('standardisation_ar', $row[4])
                        ->whereNull('deleted_at')
                        ->first();

                    if ($existingSecteur) {
                        throw new \Exception("Un secteur avec le code '{$row[0]}' et la standardisation '{$row[4]}' existe déjà.");
                    }

                    // Mapping pour standardisation_fr
                    $standardisationFr = $row[4] === 'مقيس' ? 'Standardisé' : 'Non Standardisé';

                    // Créer un nouveau secteur
                    Secteur::create([
                        'code' => $row[0],
                        'nom_fr' => $row[1] ?? null,
                        'nom_ar' => $row[2],
                        'standardisation_fr' => $standardisationFr,
                        'standardisation_ar' => $row[4],
                        'statut' => $row[5] ?? null,
                        'date_creation' => $row[6] ? date('Y-m-d', strtotime($row[6])) : null,
                        'date_annulation' => $row[7] ? date('Y-m-d', strtotime($row[7])) : null,
                        'observation' => $row[8] ?? null,
                    ]);
                } catch (\Exception $e) {
                    $errorLines[] = [
                        'line' => $index + 2,
                        'message' => $e->getMessage(),
                        'data' => $row,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => empty($errorLines) ? 'Importation réussie.' : 'Importation terminée avec des erreurs.',
                'error_lines' => $errorLines,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'importation : '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Une erreur s\'est produite lors de l\'importation.',
            ], 500);
        }
    }

    // Lister toutes les erreurs d'importation
    public function importErrors()
    {
        $errors = ImportError::all();

        return response()->json($errors);
    }

    // Supprimer une erreur d'importation
    public function destroyImportError($id)
    {
        $error = ImportError::find($id);

        if (! $error) {
            return response()->json(['message' => 'Erreur non trouvée'], 404);
        }

        $error->delete();

        return response()->json(['message' => 'Erreur supprimée avec succès']);
    }
}
