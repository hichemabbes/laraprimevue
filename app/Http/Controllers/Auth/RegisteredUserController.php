<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DeletePassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Connecte un utilisateur.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if (! Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Identifiants invalides',
                ], 401);
            }

            $user = Auth::user();

            // Récupérer role_name
            $modelHasRole = \DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->where('model_has_roles.model_id', $user->id)
                ->select('roles.name as role_name')
                ->first();

            if (! $modelHasRole) {
                \Illuminate\Support\Facades\Log::warning('Aucun rôle trouvé pour l\'utilisateur', ['user_id' => $user->id]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucun rôle associé à cet utilisateur',
                ], 403);
            }

            // Définir les variables
            $roles = [$modelHasRole->role_name];

            // Générer le token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Log pour débogage
            \Illuminate\Support\Facades\Log::debug('Connexion réussie', [
                'user_id' => $user->id,
                'roles' => $roles,
            ]);

            return response()->json([
                'status' => 'success',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'nom_fr' => $user->nom_fr,
                    'prenom_fr' => $user->prenom_fr,
                    'roles' => $roles,
                ],
                'token' => $token,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Échec de la connexion', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? null,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la connexion',
            ], 500);
        }
    }

    /**
     * Vérifie le mot de passe de suppression.
     *
     * @throws \Exception
     */
    private function verifyDeletePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $passwordRecord = Cache::remember('delete_password', 3600, fn () => DeletePassword::first());
        if (! $passwordRecord) {
            Log::warning('Aucun mot de passe de suppression défini');
            throw new \Exception('Aucun mot de passe de suppression défini', 400);
        }
        Log::info('Vérification du mot de passe pour suppression utilisateur', [
            'input' => $request->password,
            'stored_hash' => $passwordRecord->password,
        ]);
        if (! $passwordRecord->verifyPassword($request->password)) {
            Log::warning('Mot de passe incorrect pour suppression utilisateur', ['input' => $request->password]);
            throw new \Exception('Mot de passe incorrect', 401);
        }
    }

    /**
     * Inscrit un SuperAdmin (uniquement si aucun utilisateur n'existe).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeSuperAdmin(Request $request)
    {
        try {
            $request->validate([
                'nom_fr' => 'required|string|max:100',
                'prenom_fr' => 'required|string|max:100',
                'email' => 'required|string|lowercase|email|max:255|unique:users,email',
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            if (User::count() === 0) {
                // Check if SuperAdmin role exists
                $role = Role::where('name', 'SuperAdmin')->first();

                // If role doesn't exist, create it
                if (!$role) {
                    $role = Role::create([
                        'name' => 'SuperAdmin',
                        'guard_name' => 'web',
                        'name_ar' => 'المسؤول الأعلى'
                    ]);
                }

                $user = User::create([
                    'nom_fr' => $request->nom_fr,
                    'prenom_fr' => $request->prenom_fr,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'nationalite_fr' => 'Tunisienne',
                    'nationalite_ar' => 'تونسية',
                ]);

                // Assigner le rôle avec les champs supplémentaires dans model_has_roles
                DB::table('model_has_roles')->insert([
                    'role_id' => $role->id,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $user->id,
                    'statut' => 'Actif',
                    'description' => 'Rôle Super Administrateur avec tous les privilèges.',
                    'observation' => 'Attribué lors de l’installation initiale du système.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $token = $user->createToken('auth_token')->plainTextToken;

                $user->load('roles');

                return response()->json([
                    'status' => 'success',
                    'message' => 'SuperAdmin inscrit avec succès',
                    'user' => [
                        'id' => $user->id,
                        'nom_fr' => $user->nom_fr,
                        'prenom_fr' => $request->prenom_fr,
                        'email' => $user->email,
                        'roles' => $user->roles->pluck('name')->toArray(),
                    ],
                    'token' => $token,
                ], 201);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Un SuperAdmin existe déjà',
            ], 403);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Échec de l\'inscription SuperAdmin : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de l\'inscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère tous les utilisateurs avec leurs rôles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            return response()->json(User::with('roles')->get(), 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des utilisateurs : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la récupération des utilisateurs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Inscrit un nouvel utilisateur.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nom_fr' => 'required|string|max:100',
                'prenom_fr' => 'required|string|max:100',
                'email' => 'required|string|lowercase|email|max:255|unique:users,email',
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => 'required|string|exists:roles,name',
                'matricule' => 'nullable|string|max:50|unique:users,matricule',
                'num_extrait_naissance' => 'nullable|string|max:20|unique:users,num_extrait_naissance',
                'cin' => 'nullable|string|max:20|unique:users,cin',
                'date_cin' => 'nullable|date',
                'lieu_cin_fr' => 'nullable|string|max:100',
                'lieu_cin_ar' => 'nullable|string|max:100',
                'date_naissance' => 'nullable|date',
                'lieu_naissance_fr' => 'nullable|string|max:100',
                'lieu_naissance_ar' => 'nullable|string|max:100',
                'nationalite_fr' => 'required|string|max:50',
                'genre_fr' => 'nullable|in:Homme,Femme,Autre',
            ]);

            $user = User::create([
                'nom_fr' => $request->nom_fr,
                'prenom_fr' => $request->prenom_fr,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'matricule' => $request->matricule,
                'num_extrait_naissance' => $request->num_extrait_naissance,
                'cin' => $request->cin,
                'date_cin' => $request->date_cin,
                'lieu_cin_fr' => $request->lieu_cin_fr,
                'lieu_cin_ar' => $request->lieu_cin_ar,
                'date_naissance' => $request->date_naissance,
                'lieu_naissance_fr' => $request->lieu_naissance_fr,
                'lieu_naissance_ar' => $request->lieu_naissance_ar,
                'nationalite_fr' => $request->nationalite_fr,
                'nationalite_ar' => 'تونسية',
                'genre_fr' => $request->genre_fr,
                'genre_ar' => $request->genre_fr === 'Homme' ? 'ذكر' : ($request->genre_fr === 'Femme' ? 'أنثى' : 'آخر'),
            ]);

            $user->assignRole($request->role);
            $token = $user->createToken('auth_token')->plainTextToken;

            $user->load('roles');

            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur inscrit avec succès',
                'user' => [
                    'id' => $user->id,
                    'nom_fr' => $user->nom_fr,
                    'prenom_fr' => $request->prenom_fr,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray(),
                ],
                'token' => $token,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Échec de l\'inscription utilisateur : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de l\'inscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Déconnecte l'utilisateur.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Déconnexion réussie',
            ]);
        } catch (\Exception $e) {
            Log::error('Échec de la déconnexion : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la déconnexion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour un utilisateur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'nom_fr' => 'required|string|max:100',
                'prenom_fr' => 'required|string|max:100',
                'email' => 'required|string|email|max:255|unique:users,email,'.$id,
                'role' => 'required|string|exists:roles,name',
                'matricule' => 'nullable|string|max:50|unique:users,matricule,'.$id,
                'num_extrait_naissance' => 'nullable|string|max:20|unique:users,num_extrait_naissance,'.$id,
                'cin' => 'nullable|string|max:20|unique:users,cin,'.$id,
                'date_cin' => 'nullable|date',
                'lieu_cin_fr' => 'nullable|string|max:100',
                'lieu_cin_ar' => 'nullable|string|max:100',
                'date_naissance' => 'nullable|date',
                'lieu_naissance_fr' => 'nullable|string|max:100',
                'lieu_naissance_ar' => 'nullable|string|max:100',
                'nationalite_fr' => 'required|string|max:50',
                'genre_fr' => 'nullable|in:Homme,Femme,Autre',
            ]);

            $user->update([
                'nom_fr' => $request->nom_fr,
                'prenom_fr' => $request->prenom_fr,
                'email' => $request->email,
                'matricule' => $request->matricule,
                'num_extrait_naissance' => $request->num_extrait_naissance,
                'cin' => $request->cin,
                'date_cin' => $request->date_cin,
                'lieu_cin_fr' => $request->lieu_cin_fr,
                'lieu_cin_ar' => $request->lieu_cin_ar,
                'date_naissance' => $request->date_naissance,
                'lieu_naissance_fr' => $request->lieu_naissance_fr,
                'lieu_naissance_ar' => $request->lieu_naissance_ar,
                'nationalite_fr' => $request->nationalite_fr,
                'nationalite_ar' => 'تونسية',
                'genre_fr' => $request->genre_fr,
                'genre_ar' => $request->genre_fr === 'Homme' ? 'ذكر' : ($request->genre_fr === 'Femme' ? 'أنثى' : 'آخر'),
            ]);

            $user->syncRoles([$request->role]);
            $user->load('roles');

            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur mis à jour',
                'user' => [
                    'id' => $user->id,
                    'nom_fr' => $user->nom_fr,
                    'prenom_fr' => $request->prenom_fr,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray(),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Échec de la mise à jour utilisateur : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la mise à jour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime un utilisateur.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $this->verifyDeletePassword($request);
            $user = User::findOrFail($id);
            $user->tokens()->delete();
            $user->roles()->detach();
            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur supprimé',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'utilisateur : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la suppression',
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
