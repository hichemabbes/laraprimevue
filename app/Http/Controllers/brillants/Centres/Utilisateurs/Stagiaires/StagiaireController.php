<?php

namespace App\Http\Controllers\brillants\Centres\Utilisateurs\Stagiaires;

use App\Http\Controllers\Controller;
use App\Models\Centre\Personnels\Stagiaire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class StagiaireController extends Controller
{
    public function indexStagiaires(Request $request)
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

        $isCentreRole = $authUser->roles->pluck('is_centre_role')->contains(true);
        $query = Stagiaire::with('user.roles', 'centre');

        if ($isCentreRole && $centreId) {
            $query->where('centre_id', $centreId);
        }

        return response()->json($query->get());
    }

    public function storeStagiaire(Request $request)
    {
        try {
            $userId = $request->header('X-User-ID');
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

            $isCentreRole = $authUser->roles->pluck('is_centre_role')->contains(true);
            $defaultCentreId = $isCentreRole ? ($authUser->centres->first()?->id ?? null) : null;
            $centreId = $request->input('centre_id', $defaultCentreId);

            Log::info('Création stagiaire - Données reçues:', $request->all());
            Log::info('isCentreRole: '.($isCentreRole ? 'true' : 'false').', centre_id: '.($centreId ?? 'null'));

            $request->validate([
                'name' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:users,email',
                'matricule' => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:255',
                'adresse' => 'nullable|string|max:255',
                'date_naissance' => 'nullable|date',
                'cin' => 'nullable|string|max:255',
                'formation' => 'nullable|string|max:255',
                'niveau_etude' => 'nullable|string|max:255',
                'statut' => 'nullable|in:actif,inactif,termine',
                'date_inscription' => 'nullable|date',
                'date_fin_formation' => 'nullable|date',
                'email_personnel' => 'nullable|email|max:255',
                'centre_id' => $isCentreRole ? 'nullable|exists:centres,id' : 'required|exists:centres,id',
            ]);

            if (! $centreId) {
                throw new \Exception('Aucun centre spécifié ou récupéré pour le stagiaire.');
            }

            $generatedPassword = $request->name.'123***';

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($generatedPassword),
            ]);
            Log::info('Utilisateur créé avec ID: '.$user->id);

            $role = Role::where('name', 'Stagiaire')->first();
            if (! $role) {
                throw new \Exception('Le rôle "Stagiaire" n\'existe pas dans la base de données.');
            }
            $user->assignRole($role);
            Log::info('Rôle "Stagiaire" assigné à l\'utilisateur ID: '.$user->id);

            // Ajout dans user_centre pour les utilisateurs centraux (isCentreRole = 0)
            if (! $isCentreRole) {
                $user->centres()->attach($centreId, [
                    'date_debut' => now(),
                    'date_fin' => null,
                ]);
                Log::info('Utilisateur ID: '.$user->id.' affecté au centre ID: '.$centreId.' dans user_centre');
            }

            $stagiaire = Stagiaire::create([
                'user_id' => $user->id,
                'centre_id' => $centreId,
                'matricule' => $request->matricule,
                'prenom' => $request->prenom,
                'telephone' => $request->telephone,
                'adresse' => $request->adresse,
                'date_naissance' => $request->date_naissance,
                'cin' => $request->cin,
                'formation' => $request->formation,
                'niveau_etude' => $request->niveau_etude,
                'statut' => $request->statut ?? 'actif',
                'date_inscription' => $request->date_inscription,
                'date_fin_formation' => $request->date_fin_formation,
                'email_personnel' => $request->email_personnel,
            ]);
            Log::info('Stagiaire créé avec ID: '.$stagiaire->id);

            $stagiaire->load('user.roles', 'centre');

            return response()->json([
                'status' => 'success',
                'message' => 'Stagiaire créé avec succès',
                'stagiaire' => $stagiaire,
                'generated_password' => $generatedPassword,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Échec création stagiaire: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la création du stagiaire',
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStagiaire(Request $request, $id)
    {
        try {
            $stagiaire = Stagiaire::findOrFail($id);
            $userId = $request->header('X-User-ID');
            $authUser = $userId ? User::with('roles', 'centres')->find($userId) : null;

            if (! $authUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non trouvé',
                ], 404);
            }

            $isCentreRole = $authUser->roles->pluck('is_centre_role')->contains(true);
            $defaultCentreId = $isCentreRole ? ($authUser->centres->first()?->id ?? null) : null;
            $centreId = $request->input('centre_id', $defaultCentreId ?: $stagiaire->centre_id);

            $request->validate([
                'name' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:users,email,'.$stagiaire->user_id,
                'matricule' => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:255',
                'adresse' => 'nullable|string|max:255',
                'date_naissance' => 'nullable|date',
                'cin' => 'nullable|string|max:255',
                'formation' => 'nullable|string|max:255',
                'niveau_etude' => 'nullable|string|max:255',
                'statut' => 'nullable|in:actif,inactif,termine',
                'date_inscription' => 'nullable|date',
                'date_fin_formation' => 'nullable|date',
                'email_personnel' => 'nullable|email|max:255',
                'centre_id' => $isCentreRole ? 'nullable|exists:centres,id' : 'required|exists:centres,id',
            ]);

            if (! $centreId) {
                throw new \Exception('Aucun centre spécifié ou récupéré pour le stagiaire.');
            }

            $stagiaire->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Mise à jour dans user_centre pour les utilisateurs centraux
            if (! $isCentreRole && $centreId !== $stagiaire->centre_id) {
                $stagiaire->user->centres()->sync([$centreId => [
                    'date_debut' => now(),
                    'date_fin' => null,
                ]]);
                Log::info('Centre mis à jour dans user_centre pour l\'utilisateur ID: '.$stagiaire->user_id.' avec centre_id: '.$centreId);
            }

            $stagiaire->update([
                'centre_id' => $centreId,
                'matricule' => $request->matricule,
                'prenom' => $request->prenom,
                'telephone' => $request->telephone,
                'adresse' => $request->adresse,
                'date_naissance' => $request->date_naissance,
                'cin' => $request->cin,
                'formation' => $request->formation,
                'niveau_etude' => $request->niveau_etude,
                'statut' => $request->statut ?? 'actif',
                'date_inscription' => $request->date_inscription,
                'date_fin_formation' => $request->date_fin_formation,
                'email_personnel' => $request->email_personnel,
            ]);

            $stagiaire->load('user.roles', 'centre');

            return response()->json([
                'status' => 'success',
                'message' => 'Stagiaire mis à jour avec succès',
                'stagiaire' => $stagiaire,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Échec mise à jour stagiaire: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la mise à jour du stagiaire',
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyStagiaire($id)
    {
        try {
            $stagiaire = Stagiaire::findOrFail($id);
            $stagiaire->user->centres()->detach(); // Supprime les affectations dans user_centre
            $stagiaire->user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Stagiaire supprimé avec succès',
            ]);
        } catch (\Exception $e) {
            Log::error('Échec suppression stagiaire: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la suppression du stagiaire',
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }
}
