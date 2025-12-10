<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Models\brillants\Specialites\Secteurs\Secteur;
use App\Models\brillants\Specialites\SousSecteurs\SousSecteur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SousSecteurImportController extends Controller
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
            array_shift($rows); // Supprimer l'en-tête

            $errorLines = [];

            foreach ($rows as $index => $row) {
                try {
                    $code = trim($row[0] ?? '');
                    $nom_fr = trim($row[1] ?? '');
                    $nom_ar = trim($row[2] ?? '');
                    $code_secteur = trim($row[3] ?? '');
                    $statut = trim($row[4] ?? '');
                    $date_creation = trim($row[5] ?? '');
                    $date_annulation = trim($row[6] ?? '');
                    $observation = trim($row[7] ?? '');

                    if (empty($code)) {
                        throw new \Exception("Le champ 'Code' est requis.");
                    }
                    if (empty($nom_ar)) {
                        throw new \Exception("Le champ 'Nom (AR)' est requis.");
                    }
                    if (empty($code_secteur)) {
                        throw new \Exception("Le champ 'Code Secteur' est requis.");
                    }

                    $secteur = Secteur::where('code', $code_secteur)->first();
                    if (! $secteur) {
                        throw new \Exception("Le secteur avec le code '{$code_secteur}' n'existe pas.");
                    }
                    if ($secteur->trashed()) {
                        throw new \Exception("Le secteur avec le code '{$code_secteur}' est supprimé.");
                    }

                    $existingSousSecteur = SousSecteur::where('code', $code)
                        ->whereHas('secteur', function ($query) use ($secteur) {
                            $query->where('standardisation_ar', $secteur->standardisation_ar);
                        })
                        ->whereNull('deleted_at')
                        ->first();
                    if ($existingSousSecteur) {
                        throw new \Exception("Un sous-secteur avec le code '{$code}' existe déjà pour cette standardisation.");
                    }

                    if ($statut && ! in_array($statut, ['Actif', 'Inactif'])) {
                        throw new \Exception("Le statut doit être l'un des suivants : Actif, Inactif.");
                    }

                    if ($date_creation && ! \DateTime::createFromFormat('Y-m-d', $date_creation)) {
                        throw new \Exception('Format de date de création invalide.');
                    }

                    if ($date_annulation && ! \DateTime::createFromFormat('Y-m-d', $date_annulation)) {
                        throw new \Exception("Format de date d'annulation invalide.");
                    }

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
                'message' => 'Importation terminée.',
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

    public function getSecteurByCode(Request $request)
    {
        $code = $request->query('code');
        $secteur = Secteur::where('code', $code)->first();
        if (! $secteur) {
            return response()->json(['message' => 'Secteur non trouvé'], 404);
        }

        return response()->json($secteur);
    }
}
