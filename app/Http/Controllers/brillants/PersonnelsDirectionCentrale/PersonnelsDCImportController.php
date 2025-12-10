<?php

namespace App\Http\Controllers\brillants\PersonnelsDirectionCentrale;

use App\Http\Controllers\Controller;
use App\Services\Import\PersonnelsAtfpImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PersonnelsDCImportController extends Controller
{
    protected $listsCache = [];

    public function importxls(Request $request)
    {
        Log::info('importxls appelé pour personnels ATFP');

        $request->validate(['file' => 'required|mimes:xls,xlsx|max:2048']);
        $file = $request->file('file');

        try {
            $importService = new PersonnelsAtfpImportService();
            $result = $importService->import($file);

            return response()->json([
                'success' => true,
                'message' => $result['error_count'] === 0 && $result['existing_count'] === 0
                    ? 'Importation réussie.'
                    : 'Importation terminée avec des erreurs.',
                'success_count' => $result['success_count'],
                'existing_count' => $result['existing_count'],
                'error_count' => $result['error_count'],
                'error_lines' => $result['error_lines'],
            ], 200);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'importation: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => "Erreur serveur: {$e->getMessage()}",
                'success_count' => 0,
                'existing_count' => 0,
                'error_count' => 0,
                'error_lines' => [],
            ], 500);
        }
    }
}
