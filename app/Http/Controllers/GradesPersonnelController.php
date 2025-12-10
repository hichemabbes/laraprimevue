<?php

namespace App\Http\Controllers;

use App\Models\ATFP\Grades\Grades;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradesPersonnelController extends Controller
{
    /**
     * Store a newly created grade personnel in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'personnel_id' => 'required|exists:personnels_centres,id',
            'filiere_fr' => 'required|string|max:255',
            'grade_fr' => 'required|string|max:255',
            'poste_fr' => 'required|string|max:255',
            'filiere_ar' => 'required|string|max:255',
            'grade_ar' => 'required|string|max:255',
            'poste_ar' => 'required|string|max:255',
            'categorie' => 'nullable|string|max:255',
            'echelle' => 'nullable|string|max:255',
            'echelon' => 'nullable|string|max:255',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'statut' => 'nullable|string|max:255',
            'observation_fr' => 'nullable|string',
            'observation_ar' => 'nullable|string',
        ]);

        // Check if the combination exists in the grades table
        $gradeExists = Grade::where([
            'filiere_fr' => $validated['filiere_fr'],
            'grade_fr' => $validated['grade_fr'],
            'poste_fr' => $validated['poste_fr'],
            'filiere_ar' => $validated['filiere_ar'],
            'grade_ar' => $validated['grade_ar'],
            'poste_ar' => $validated['poste_ar'],
        ])->exists();

        if (!$gradeExists) {
            return back()->withErrors(['error' => 'La combinaison de filière, grade et poste n\'existe pas dans la table grades.']);
        }

        // Create the record
        Grades::create($validated);

        return redirect()->route('grades_personnels.index')->with('success', 'Grade ajouté avec succès.');
    }
}
