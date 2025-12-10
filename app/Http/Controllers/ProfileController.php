<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function getProfileData(Request $request)
    {
        try {
            Log::info('Appel de getProfileData pour endpoint /api/profile/data');

            $user = Auth::user();
            if (! $user) {
                Log::warning('Utilisateur non authentifié pour /api/profile/data');

                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non authentifié',
                ], 401);
            }

            $userId = $user->id;
            $user = User::with('roles')->find($userId);
            $role = $user->roles->first()->name ?? 'SuperAdmin';
            Log::info("Rôle détecté pour user_id {$userId}: {$role}");

            return response()->json([
                'status' => 'success',
                'user' => [
                    'id' => $user->id,
                    'nom_fr' => $user->nom_fr,
                    'prenom_fr' => $user->prenom_fr,
                    'email' => $user->email,
                    'nationalite_fr' => $user->nationalite_fr,
                    'genre_fr' => $user->genre_fr,
                    'matricule' => $user->matricule,
                    'cin' => $user->cin,
                    'date_naissance' => $user->date_naissance,
                ],
                'profile' => null,
                'centre' => null,
                'role' => $role,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération du profil pour user_id {$userId}: {$e->getMessage()}");

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la récupération des données du profil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            $user = Auth::user();
            if (! $user) {
                Log::warning('Utilisateur non authentifié pour updatePassword');

                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non authentifié',
                ], 401);
            }

            $userId = $user->id;

            if (! Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Le mot de passe actuel est incorrect',
                ], 422);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            Log::info("Mot de passe mis à jour pour user_id {$userId}");

            return response()->json([
                'status' => 'success',
                'message' => 'Mot de passe mis à jour avec succès',
            ], 200);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la mise à jour du mot de passe pour user_id {$userId}: {$e->getMessage()}");

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la mise à jour du mot de passe',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            if (! $user) {
                Log::warning('Utilisateur non authentifié pour updateProfile');

                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non authentifié',
                ], 401);
            }

            $userId = $user->id;

            $request->validate([
                'nom_fr' => 'required|string|max:100',
                'prenom_fr' => 'required|string|max:100',
                'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
                'telephone' => 'nullable|string|max:20',
                'adresse_fr' => 'nullable|string|max:255',
            ]);

            // Mettre à jour uniquement les champs de la table users
            $user->update([
                'nom_fr' => $request->nom_fr,
                'prenom_fr' => $request->prenom_fr,
                'email' => $request->email,
                // Les champs telephone et adresse_fr ne sont pas dans users, donc ignorés
            ]);

            $role = $user->roles->first()->name ?? 'SuperAdmin';
            Log::info("Profil mis à jour pour user_id {$userId}, rôle: {$role}");

            return response()->json([
                'status' => 'success',
                'message' => 'Profil mis à jour avec succès',
                'user' => $user->fresh(['roles']),
                'profile' => null,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la mise à jour du profil pour user_id {$userId}: {$e->getMessage()}");

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la mise à jour du profil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
