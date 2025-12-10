<?php

namespace App\Http\Controllers\brillants\Centres\Utilisateurs\Formateurs;

use App\Http\Controllers\Controller;
use App\Models\Centre\Personnels\Formateur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class FormateurController extends Controller
{
    public function indexFormateurs(Request $request)
    {
        $userId = $request->header('X-User-ID');
        $centreId = $request->header('X-Centre-ID');

        if (! $userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non identifié (X-User-ID manquant)',
            ], 400);
        }

        $authUser = User::with('roles', 'centres')->find($userId);
        if (! $authUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé',
            ], 404);
        }

        $isCentreRole = $authUser->roles->pluck('is_centre_role')->first() ?? 0;
        $queryCentreId = $isCentreRole && $centreId ? $centreId : null;

        $query = Formateur::with('user.roles', 'centres');

        if ($queryCentreId) {
            $query->whereHas('centres', function ($q) use ($queryCentreId) {
                $q->where('centres.id', $queryCentreId);
            });
        }

        return response()->json($query->get());
    }

    public function storeFormateur(Request $request)
    {
        try {
            $userId = $request->header('X-User-ID');
            $centreId = $request->header('X-Centre-ID');

            if (! $userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non identifié (X-User-ID manquant)',
                ], 400);
            }

            $authUser = User::with('roles', 'centres')->find($userId);
            if (! $authUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non trouvé',
                ], 404);
            }

            $isCentreRole = $authUser->roles->pluck('is_centre_role')->first() ?? 0;
            $defaultCentreId = $isCentreRole && $centreId ? $centreId : null;

            $request->validate([
                'nom_fr' => 'required|string|max:255',
                'prenom_fr' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:users,email',
                'matricule' => 'required|string|max:255|unique:formateurs,matricule',
                'nom_ar' => 'required|string|max:255',
                'prenom_ar' => 'nullable|string|max:255',
                'niveau_etude' => 'nullable|string|max:255',
                'specialite_diplome_fr' => 'nullable|string|max:255',
                'specialite_diplome_ar' => 'nullable|string|max:255',
                'matiere_enseigne_fr' => 'nullable|string|max:255',
                'matiere_enseigne_ar' => 'nullable|string|max:255',
                'date_prise_fonction' => 'nullable|date',
                'date_fin_mission' => 'nullable|date',
                'code_niveau' => 'nullable|string|max:255',
                'charge_horaire' => 'nullable|integer',
                'rabattement' => 'nullable|numeric',
                'code_secteur' => 'nullable|string|max:255',
                'code_sous_secteur' => 'nullable|string|max:255',
                'cin' => 'nullable|string|max:255',
                'date_naissance' => 'nullable|date',
                'lieu_naissance' => 'nullable|string|max:255',
                'adresse_fr' => 'nullable|string',
                'adresse_ar' => 'nullable|string',
                'gouvernorat' => 'nullable|string|max:255',
                'delegation' => 'nullable|string|max:255',
                'tele1' => 'nullable|string|max:255',
                'tele2' => 'nullable|string|max:255',
                'mail' => 'nullable|email|max:255',
                'etat_civil' => 'nullable|string|max:255',
                'formateur_conseiller' => 'nullable|string|max:255',
                'statut' => 'nullable|in:actif,inactif,en_conge',
                'type' => 'nullable|string|max:255',
                'centre_id' => $isCentreRole ? 'nullable|exists:centres,id' : 'required|exists:centres,id',
            ]);

            $centreIdToUse = $isCentreRole ? $defaultCentreId : $request->input('centre_id');
            if (! $centreIdToUse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Centre ID requis ou non valide',
                    'errors' => ['centre_id' => ['Le centre_id est obligatoire ou invalide.']],
                ], 422);
            }

            $generatedPassword = $request->nom_fr.'123***';

            $user = User::create([
                'name' => $request->nom_fr, // Utilisation de nom_fr comme name dans users
                'email' => $request->email,
                'password' => Hash::make($generatedPassword),
            ]);

            $role = Role::findByName('Formateur');
            $user->assignRole($role);

            if (! $isCentreRole) {
                $user->centres()->attach($centreIdToUse, [
                    'date_debut' => now(),
                    'date_fin' => null,
                ]);
            }

            $formateur = Formateur::create([
                'user_id' => $user->id,
                'matricule' => $request->matricule,
                'nom_fr' => $request->nom_fr,
                'prenom_fr' => $request->prenom_fr,
                'nom_ar' => $request->nom_ar,
                'prenom_ar' => $request->prenom_ar,
                'niveau_etude' => $request->niveau_etude,
                'specialite_diplome_fr' => $request->specialite_diplome_fr,
                'specialite_diplome_ar' => $request->specialite_diplome_ar,
                'matiere_enseigne_fr' => $request->matiere_enseigne_fr,
                'matiere_enseigne_ar' => $request->matiere_enseigne_ar,
                'date_prise_fonction' => $request->date_prise_fonction,
                'date_fin_mission' => $request->date_fin_mission,
                'code_niveau' => $request->code_niveau,
                'charge_horaire' => $request->charge_horaire,
                'rabattement' => $request->rabattement,
                'code_secteur' => $request->code_secteur,
                'code_sous_secteur' => $request->code_sous_secteur,
                'cin' => $request->cin,
                'date_naissance' => $request->date_naissance,
                'lieu_naissance' => $request->lieu_naissance,
                'adresse_fr' => $request->adresse_fr,
                'adresse_ar' => $request->adresse_ar,
                'gouvernorat' => $request->gouvernorat,
                'delegation' => $request->delegation,
                'tele1' => $request->tele1,
                'tele2' => $request->tele2,
                'mail' => $request->mail,
                'etat_civil' => $request->etat_civil,
                'formateur_conseiller' => $request->formateur_conseiller,
                'statut' => $request->statut ?? 'actif',
                'type' => $request->type,
            ]);

            $formateur->centres()->attach($centreIdToUse, [
                'date_debut' => now(),
                'date_fin' => null,
                'heures_enseignees' => 0,
                'is_principal' => false,
            ]);

            $formateur->load('user.roles', 'centres');

            return response()->json([
                'status' => 'success',
                'message' => 'Formateur créé avec succès',
                'formateur' => $formateur,
                'generated_password' => $generatedPassword,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la création du formateur',
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateFormateur(Request $request, $id)
    {
        try {
            $formateur = Formateur::findOrFail($id);
            $userId = $request->header('X-User-ID');
            $authUser = $userId ? User::with('roles', 'centres')->find($userId) : null;

            if (! $authUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non trouvé',
                ], 404);
            }

            $isCentreRole = $authUser->roles->pluck('is_centre_role')->first() ?? 0;
            $defaultCentreId = $isCentreRole ? ($authUser->centres->first()?->id ?? null) : null;

            $request->validate([
                'nom_fr' => 'required|string|max:255',
                'prenom_fr' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:users,email,'.$formateur->user_id,
                'matricule' => 'required|string|max:255|unique:formateurs,matricule,'.$formateur->id,
                'nom_ar' => 'required|string|max:255',
                'prenom_ar' => 'nullable|string|max:255',
                'niveau_etude' => 'nullable|string|max:255',
                'specialite_diplome_fr' => 'nullable|string|max:255',
                'specialite_diplome_ar' => 'nullable|string|max:255',
                'matiere_enseigne_fr' => 'nullable|string|max:255',
                'matiere_enseigne_ar' => 'nullable|string|max:255',
                'date_prise_fonction' => 'nullable|date',
                'date_fin_mission' => 'nullable|date',
                'code_niveau' => 'nullable|string|max:255',
                'charge_horaire' => 'nullable|integer',
                'rabattement' => 'nullable|numeric',
                'code_secteur' => 'nullable|string|max:255',
                'code_sous_secteur' => 'nullable|string|max:255',
                'cin' => 'nullable|string|max:255',
                'date_naissance' => 'nullable|date',
                'lieu_naissance' => 'nullable|string|max:255',
                'adresse_fr' => 'nullable|string',
                'adresse_ar' => 'nullable|string',
                'gouvernorat' => 'nullable|string|max:255',
                'delegation' => 'nullable|string|max:255',
                'tele1' => 'nullable|string|max:255',
                'tele2' => 'nullable|string|max:255',
                'mail' => 'nullable|email|max:255',
                'etat_civil' => 'nullable|string|max:255',
                'formateur_conseiller' => 'nullable|string|max:255',
                'statut' => 'nullable|in:actif,inactif,en_conge',
                'type' => 'nullable|string|max:255',
                'centre_id' => $isCentreRole ? 'nullable|exists:centres,id' : 'required|exists:centres,id',
            ]);

            $centreId = $request->input('centre_id', $defaultCentreId ?: $formateur->centres->first()->id);
            if (! $centreId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Centre ID requis ou non valide',
                    'errors' => ['centre_id' => ['Le centre_id est obligatoire ou invalide.']],
                ], 422);
            }

            $formateur->user->update([
                'name' => $request->nom_fr, // Mise à jour avec nom_fr
                'email' => $request->email,
            ]);

            $formateur->update([
                'matricule' => $request->matricule,
                'nom_fr' => $request->nom_fr,
                'prenom_fr' => $request->prenom_fr,
                'nom_ar' => $request->nom_ar,
                'prenom_ar' => $request->prenom_ar,
                'niveau_etude' => $request->niveau_etude,
                'specialite_diplome_fr' => $request->specialite_diplome_fr,
                'specialite_diplome_ar' => $request->specialite_diplome_ar,
                'matiere_enseigne_fr' => $request->matiere_enseigne_fr,
                'matiere_enseigne_ar' => $request->matiere_enseigne_ar,
                'date_prise_fonction' => $request->date_prise_fonction,
                'date_fin_mission' => $request->date_fin_mission,
                'code_niveau' => $request->code_niveau,
                'charge_horaire' => $request->charge_horaire,
                'rabattement' => $request->rabattement,
                'code_secteur' => $request->code_secteur,
                'code_sous_secteur' => $request->code_sous_secteur,
                'cin' => $request->cin,
                'date_naissance' => $request->date_naissance,
                'lieu_naissance' => $request->lieu_naissance,
                'adresse_fr' => $request->adresse_fr,
                'adresse_ar' => $request->adresse_ar,
                'gouvernorat' => $request->gouvernorat,
                'delegation' => $request->delegation,
                'tele1' => $request->tele1,
                'tele2' => $request->tele2,
                'mail' => $request->mail,
                'etat_civil' => $request->etat_civil,
                'formateur_conseiller' => $request->formateur_conseiller,
                'statut' => $request->statut ?? 'actif',
                'type' => $request->type,
            ]);

            if (! $isCentreRole && $centreId !== $formateur->centres->first()->id) {
                $formateur->user->centres()->sync([$centreId => [
                    'date_debut' => now(),
                    'date_fin' => null,
                ]]);
            }

            $formateur->centres()->sync([$centreId => [
                'date_debut' => now(),
                'date_fin' => null,
                'heures_enseignees' => $formateur->centres->first()->pivot->heures_enseignees ?? 0,
                'is_principal' => false,
            ]]);

            $formateur->load('user.roles', 'centres');

            return response()->json([
                'status' => 'success',
                'message' => 'Formateur mis à jour avec succès',
                'formateur' => $formateur,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la mise à jour du formateur',
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyFormateur($id)
    {
        try {
            $formateur = Formateur::findOrFail($id);
            $formateur->user->centres()->detach();
            $formateur->user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Formateur supprimé avec succès',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la suppression du formateur',
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }
}
