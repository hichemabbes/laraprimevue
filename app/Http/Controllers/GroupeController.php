<?php

namespace App\Http\Controllers;

use App\Models\Groupe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupeController extends Controller
{
    public function index()
    {
        $groupes = Groupe::with(['programme', 'tuteur'])->get();

        return response()->json($groupes);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code_groupe' => 'required|string|max:50|unique:groupes',
            'nom_fr' => 'required|string|max:100',
            'nom_ar' => 'required|string|max:100',
            'programme_id' => 'required|exists:programmes,id',
            'tuteur_id' => 'required|exists:tuteurs,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $groupe = Groupe::create($request->all());

        return response()->json($groupe->load('programme', 'tuteur'), 201);
    }

    public function update(Request $request, $id)
    {
        $groupe = Groupe::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code_groupe' => 'required|string|max:50|unique:groupes,code_groupe,'.$id,
            'nom_fr' => 'required|string|max:100',
            'nom_ar' => 'required|string|max:100',
            'programme_id' => 'required|exists:programmes,id',
            'tuteur_id' => 'required|exists:tuteurs,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $groupe->update($request->all());

        return response()->json($groupe->load('programme', 'tuteur'));
    }

    public function destroy($id)
    {
        $groupe = Groupe::findOrFail($id);
        $groupe->delete();

        return response()->json(['message' => 'Groupe supprimé avec succès']);
    }

    public function importXls(Request $request)
    {
        // Logique d'importation XLS à implémenter (par exemple avec Maatwebsite\Excel)
        return response()->json(['message' => 'Importation non implémentée']);
    }
}
