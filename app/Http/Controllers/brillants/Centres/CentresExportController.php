<?php

namespace App\Http\Controllers\brillants\Centres;

use App\Http\Controllers\Controller;
use App\Models\Centre\Centre;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CentresExportController extends Controller
{
    public function export()
    {
        try {
            // Charger le fichier modèle
            $templatePath = storage_path('app/templates/centres_model.xlsm');
            if (! file_exists($templatePath)) {
                return response()->json(['error' => 'Fichier modèle introuvable'], 404);
            }

            $spreadsheet = IOFactory::load($templatePath);
            $worksheet = $spreadsheet->getSheetByName('Centres');
            if (! $worksheet) {
                return response()->json(['error' => 'Feuille Centres introuvable'], 400);
            }

            // Récupérer les centres
            $centres = Centre::with('economat')->get();

            // Remplir la feuille à partir de la ligne 2
            $row = 2;
            foreach ($centres as $centre) {
                $worksheet->setCellValue('A'.$row, $centre->code);
                $worksheet->setCellValue('B'.$row, $centre->nom_fr);
                $worksheet->setCellValue('C'.$row, $centre->nom_ar);
                $worksheet->setCellValue('D'.$row, $centre->adresse_fr);
                $worksheet->setCellValue('E'.$row, $centre->adresse_ar);
                $worksheet->setCellValue('F'.$row, $centre->telephone_1);
                $worksheet->setCellValue('G'.$row, $centre->telephone_2);
                $worksheet->setCellValue('H'.$row, $centre->fax_1);
                $worksheet->setCellValue('I'.$row, $centre->fax_2);
                $worksheet->setCellValue('J'.$row, $centre->email);
                $worksheet->setCellValue('K'.$row, $centre->gouvernorat_fr);
                $worksheet->setCellValue('L'.$row, $centre->gouvernorat_ar);
                $worksheet->setCellValue('M'.$row, $centre->type);
                $worksheet->setCellValue('N'.$row, $centre->classe);
                $worksheet->setCellValue('O'.$row, $centre->economat->nom_fr ?? '');
                $worksheet->setCellValue('P'.$row, $centre->statut_fr);
                $worksheet->setCellValue('Q'.$row, $centre->statut_ar);
                $worksheet->setCellValue('R'.$row, $centre->date_creation ? $centre->date_creation->format('Y-m-d') : '');
                $worksheet->setCellValue('S'.$row, $centre->date_ouverture ? $centre->date_ouverture->format('Y-m-d') : '');
                $worksheet->setCellValue('T'.$row, $centre->date_fermeture ? $centre->date_fermeture->format('Y-m-d') : '');
                $worksheet->setCellValue('U'.$row, $centre->observation_fr);
                $worksheet->setCellValue('V'.$row, $centre->observation_ar);
                $row++;
            }

            // Préparer la réponse
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); // Changé de Xlsm à Xlsx
            $tempFile = tempnam(sys_get_temp_dir(), 'centres_export');
            $writer->save($tempFile);

            return response()->download($tempFile, 'centres_export.xlsm', [
                'Content-Type' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'exportation',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
