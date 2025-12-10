<?php

namespace App\Http\Controllers\brillants\Fonctions;

use App\Exports\GradesExport;
use App\Http\Controllers\Controller;
use App\Imports\GradesImport;
use App\Models\ATFP\Grades\Grades;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $grades = Grades::all();
            return response()->json(['data' => $grades], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des grades: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), Grades::$rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $grade = Grades::create($request->all());
            return response()->json(['grade' => $grade, 'message' => 'Grade créé avec succès'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la création du grade: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $grade = Grades::findOrFail($id);
            return response()->json(['grade' => $grade], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Grade non trouvé'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $grade = Grades::findOrFail($id);
            $validator = Validator::make($request->all(), Grades::$rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $grade->update($request->all());
            return response()->json(['grade' => $grade, 'message' => 'Grade mis à jour avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour du grade: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Add password verification logic here if needed
            $grade = Grades::findOrFail($id);
            $grade->delete();
            return response()->json(['message' => 'Grade supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la suppression du grade: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Import grades from XLS file
     */
    public function importXLS(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xls,xlsx',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $import = new GradesImport;
            Excel::import($import, $request->file('file'));
            $errorLines = $import->getErrorLines();

            return response()->json([
                'data' => ['error_lines' => $errorLines],
                'message' => $errorLines ? 'Import terminé avec erreurs' : 'Import réussi',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de l\'import: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export grades to XLS file
     */
    public function exportXLS()
    {
        try {
            return Excel::download(new GradesExport, 'grades.xlsx');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de l\'export: ' . $e->getMessage()], 500);
        }
    }
}
