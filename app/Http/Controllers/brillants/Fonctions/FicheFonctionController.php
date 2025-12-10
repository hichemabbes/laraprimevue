<?php

namespace App\Http\Controllers\brillants\Fonctions;

use App\Http\Controllers\Controller;
use App\Models\ATFP\Grades\FicheFonction;
use App\Models\Liste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FicheFonctionController extends Controller
{
    /**
     * Liste des fonctions avec leurs fiches
     */
    public function listes()
    {
        $liste = Liste::where('nom_fr', 'Fonctions PersonnelsDirectionCentrale')->first();
        if (!$liste) {
            return response()->json(['error' => 'Liste des fonctions non trouvée'], 404);
        }

        $options = $liste->options ?? [];
        if (!is_array($options)) {
            return response()->json(['error' => 'Format des options invalide'], 500);
        }

        $fonctions = array_map(function ($option) use ($liste) {
            $fiches = FicheFonction::getFichesForFonction($option['nom_fr'])->toArray();
            $activeFiche = FicheFonction::getActiveFicheForFonction($option['nom_fr']);
            return [
                'nom_fr' => $option['nom_fr'],
                'nom_ar' => $option['nom_ar'],
                'statut' => $liste->statut,
                'description' => $liste->description,
                'active_fiche' => $activeFiche ? $activeFiche->toArray() : null,
                'fiches' => $fiches,
            ];
        }, $options);

        return response()->json($fonctions);
    }

    /**
     * Créer une nouvelle fiche
     */
    public function store(Request $request)
    {
        $validator = $this->validateRequest($request, false);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation des données',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Ensure the fiches_fonctions directory exists
        $directory = 'fiches_fonctions';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $data = $request->only([
            'fonction_fr',
            'fonction_ar',
            'date_debut',
            'date_fin',
            'statut_fr',
            'statut_ar',
            'observation',
        ]);

        if ($request->hasFile('fiche_pdf_fr')) {
            $data['fiche_pdf_fr'] = $request->file('fiche_pdf_fr')->store($directory, 'public');
        }
        if ($request->hasFile('fiche_pdf_ar')) {
            $data['fiche_pdf_ar'] = $request->file('fiche_pdf_ar')->store($directory, 'public');
        }

        // Si la nouvelle fiche est active, désactiver toutes les autres fiches pour cette fonction
        if ($data['statut_fr'] === 'Active') {
            FicheFonction::where('fonction_fr', $data['fonction_fr'])
                ->where('statut_fr', 'Active')
                ->update(['statut_fr' => 'Inactive', 'statut_ar' => 'غير نشط']);
        }

        $fiche = FicheFonction::create($data);

        return response()->json($fiche, 201);
    }

    /**
     * Mettre à jour une fiche
     */
    public function update(Request $request, $id)
    {
        Log::info('Update request received for ID: ' . $id, $request->all());
        $fiche = FicheFonction::findOrFail($id);

        $data = $request->only([
            'fonction_fr',
            'fonction_ar',
            'date_debut',
            'date_fin',
            'statut_fr',
            'statut_ar',
            'observation',
        ]);

        // Ensure the fiches_fonctions directory exists
        $directory = 'fiches_fonctions';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Gestion spécifique des fichiers
        if ($request->hasFile('fiche_pdf_fr')) {
            if ($fiche->fiche_pdf_fr) {
                Storage::disk('public')->delete($fiche->fiche_pdf_fr);
            }
            $data['fiche_pdf_fr'] = $request->file('fiche_pdf_fr')->store($directory, 'public');
        } elseif ($request->input('fiche_pdf_fr') === '') {
            if ($fiche->fiche_pdf_fr) {
                Storage::disk('public')->delete($fiche->fiche_pdf_fr);
            }
            $data['fiche_pdf_fr'] = null;
        } else {
            $data['fiche_pdf_fr'] = $fiche->fiche_pdf_fr;
        }

        if ($request->hasFile('fiche_pdf_ar')) {
            if ($fiche->fiche_pdf_ar) {
                Storage::disk('public')->delete($fiche->fiche_pdf_ar);
            }
            $data['fiche_pdf_ar'] = $request->file('fiche_pdf_ar')->store($directory, 'public');
        } elseif ($request->input('fiche_pdf_ar') === '') {
            if ($fiche->fiche_pdf_ar) {
                Storage::disk('public')->delete($fiche->fiche_pdf_ar);
            }
            $data['fiche_pdf_ar'] = null;
        } else {
            $data['fiche_pdf_ar'] = $fiche->fiche_pdf_ar;
        }

        // Validation
        $validator = $this->validateRequest($request, true, $fiche);
        if ($validator->fails()) {
            Log::error('Validation errors for update ID: ' . $id, $validator->errors()->toArray());
            return response()->json([
                'message' => 'Erreur de validation des données',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Si la fiche mise à jour est active, désactiver toutes les autres fiches pour cette fonction
        if ($data['statut_fr'] === 'Active') {
            FicheFonction::where('fonction_fr', $data['fonction_fr'])
                ->where('id', '!=', $id)
                ->where('statut_fr', 'Active')
                ->update(['statut_fr' => 'Inactive', 'statut_ar' => 'غير نشط']);
        }

        $fiche->update($data);

        return response()->json($fiche);
    }

    /**
     * Supprimer une fiche
     */
    public function destroy(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['error' => 'Mot de passe incorrect.'], 422);
        }

        $fiche = FicheFonction::findOrFail($id);

        if ($fiche->fiche_pdf_fr) {
            Storage::disk('public')->delete($fiche->fiche_pdf_fr);
        }
        if ($fiche->fiche_pdf_ar) {
            Storage::disk('public')->delete($fiche->fiche_pdf_ar);
        }

        $fiche->delete();

        return response()->json(['message' => 'Fiche supprimée avec succès.']);
    }

    /**
     * Prévisualiser une fiche PDF
     */
    public function preview($id)
    {
        $fiche = FicheFonction::findOrFail($id);
        $filePath = $fiche->fiche_pdf_fr ?: $fiche->fiche_pdf_ar;

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            return response()->json(['error' => 'Fichier non trouvé'], 404);
        }

        $file = Storage::disk('public')->get($filePath);
        $mimeType = Storage::disk('public')->mimeType($filePath);

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($filePath) . '"');
    }

    /**
     * Valider la requête
     */
    protected function validateRequest(Request $request, $isUpdate = false, $fiche = null)
    {
        $rules = [
            'fonction_fr' => 'required|string|max:255',
            'fonction_ar' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'statut_fr' => 'required|in:Active,Inactive',
            'statut_ar' => 'required|string|in:نشط,غير نشط',
            'observation' => 'nullable|string|max:1000',
        ];

        // Pour la création, au moins un fichier PDF est requis
        if (!$isUpdate) {
            $rules['fiche_pdf_fr'] = 'nullable|file|mimes:pdf|max:10240';
            $rules['fiche_pdf_ar'] = 'nullable|file|mimes:pdf|max:10240';
        } else {
            // Pour la mise à jour, vérifier si au moins un fichier existe ou est fourni
            $rules['fiche_pdf_fr'] = 'nullable|file|mimes:pdf|max:10240';
            $rules['fiche_pdf_ar'] = 'nullable|file|mimes:pdf|max:10240';
        }

        $validator = Validator::make($request->all(), $rules);

        // Validation personnalisée pour s'assurer qu'au moins un fichier PDF est présent
        $validator->after(function ($validator) use ($request, $isUpdate, $fiche) {
            $hasFr = $request->hasFile('fiche_pdf_fr') || ($isUpdate && $fiche && $fiche->fiche_pdf_fr);
            $hasAr = $request->hasFile('fiche_pdf_ar') || ($isUpdate && $fiche && $fiche->fiche_pdf_ar);

            if (!$hasFr && !$hasAr) {
                $validator->errors()->add('fiche_pdf_fr', 'Au moins un fichier PDF (FR ou AR) est requis.');
                $validator->errors()->add('fiche_pdf_ar', 'Au moins un fichier PDF (FR ou AR) est requis.');
            }
        });

        return $validator;
    }
}
