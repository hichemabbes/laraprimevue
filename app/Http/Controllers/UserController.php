<?php
namespace App\Http\Controllers;

use App\Models\Centre\Centre;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a list of users.
     */
    public function index(Request $request)
    {
        try {
            $search = $request->query('search');
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);

            $users = User::with(['roles', 'centres'])
                ->when($search, function ($query, $search) {
                    $query->where('nom_fr', 'like', "%{$search}%")
                        ->orWhere('prenom_fr', 'like', "%{$search}%")
                        ->orWhere('nom_ar', 'like', "%{$search}%")
                        ->orWhere('prenom_ar', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('type_user_fr', 'like', "%{$search}%");
                })
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'data' => $users->items(),
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'message' => 'Utilisateurs récupérés avec succès.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des utilisateurs:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des utilisateurs.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all roles.
     */
    public function getRoles()
    {
        try {
            $roles = Role::select('id', 'name', 'name_ar', 'is_centre_role')->get();
            return response()->json([
                'data' => $roles,
                'message' => 'Rôles récupérés avec succès.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des rôles:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des rôles.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all centres.
     */
    public function getCentres()
    {
        try {
            $centres = Centre::select(['id', 'nom_fr', 'nom_ar'])->get();
            return response()->json([
                'data' => $centres,
                'message' => 'Centres récupérés avec succès.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des centres:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des centres.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            Log::info('Données reçues dans store:', $request->all());

            $validGenres = ['Homme', 'Femme', 'Autre'];
            $validTypeUsers = ['Personnel Direction Centrale', 'Personnel Centre', 'Stagiaire', 'Apprenti'];
            $validCentreStatuts = ['Centre officiel', 'Centre secondaire'];
            $validTypeMouvements = ['Affectation initiale', 'Transfert', 'Détachement'];

            $validator = Validator::make($request->all(), [
                'nom_fr' => ['required', 'string', 'max:255'],
                'prenom_fr' => ['required', 'string', 'max:255'],
                'nom_ar' => ['nullable', 'string', 'max:255'],
                'prenom_ar' => ['nullable', 'string', 'max:255'],
                'date_naissance' => ['nullable', 'date'],
                'lieu_naissance_fr' => ['nullable', 'string', 'max:255'],
                'lieu_naissance_ar' => ['nullable', 'string', 'max:255'],
                'nationalite_fr' => ['nullable', 'string', 'max:255'],
                'nationalite_ar' => ['nullable', 'string', 'max:255'],
                'genre_fr' => ['required', Rule::in($validGenres)],
                'genre_ar' => ['nullable', 'string', 'max:255'],
                'statut' => ['required', Rule::in(['Actif', 'Inactif'])],
                'adresse_fr' => ['nullable', 'string'],
                'adresse_ar' => ['nullable', 'string'],
                'gouvernorat_fr' => ['nullable', 'string', 'max:255'],
                'gouvernorat_ar' => ['nullable', 'string', 'max:255'],
                'delegation_fr' => ['nullable', 'string', 'max:255'],
                'delegation_ar' => ['nullable', 'string', 'max:255'],
                'telephone_1' => ['nullable', 'string', 'max:20'],
                'telephone_2' => ['nullable', 'string', 'max:20'],
                'observation_fr' => ['nullable', 'string'],
                'observation_ar' => ['nullable', 'string'],
                'photo' => ['nullable', 'string', 'regex:/^data:image\/(png|jpeg);base64,/'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'type_user_fr' => ['required', Rule::in($validTypeUsers)],
                'type_user_ar' => ['nullable', 'string', 'max:255'],
                'role' => ['required', 'exists:roles,id'],
                'centre' => ['nullable', 'exists:centres,id'],
                'centre_statut_fr' => ['nullable', Rule::in($validCentreStatuts)],
                'centre_statut_ar' => ['nullable', 'string', 'max:255'],
                'type_mouvement_fr' => ['nullable', Rule::in($validTypeMouvements)],
                'type_mouvement_ar' => ['nullable', 'string', 'max:255'],
                'pivot_observation_fr' => ['nullable', 'string'],
                'pivot_observation_ar' => ['nullable', 'string'],
                'date_debut' => ['nullable', 'date'],
                'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
                'pivot_statut' => ['nullable', Rule::in(['Actif', 'Inactif', 'Transféré'])],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation échouée',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Vérifier si le rôle nécessite un centre
            $role = Role::findOrFail($request->role);
            if ($role->is_centre_role && !$request->centre) {
                return response()->json([
                    'message' => 'Le centre est requis pour ce rôle.',
                    'errors' => ['centre' => ['Le centre est requis pour ce rôle.']],
                ], 422);
            }

            // Calculer la taille de la photo en Base64
            if ($request->photo) {
                $base64String = preg_replace('#^data:image/\w+;base64,#i', '', $request->photo);
                $base64Size = (strlen($base64String) * 3 / 4) / 1024 / 1024; // Taille en Mo
                if ($base64Size > 2) {
                    return response()->json([
                        'message' => 'La photo dépasse la taille maximale de 2 Mo.',
                        'errors' => ['photo' => ['La photo doit être inférieure à 2 Mo.']],
                    ], 422);
                }
            }

            $userData = $request->only([
                'nom_fr',
                'prenom_fr',
                'nom_ar',
                'prenom_ar',
                'date_naissance',
                'lieu_naissance_fr',
                'lieu_naissance_ar',
                'nationalite_fr',
                'nationalite_ar',
                'genre_fr',
                'genre_ar',
                'statut',
                'adresse_fr',
                'adresse_ar',
                'gouvernorat_fr',
                'gouvernorat_ar',
                'delegation_fr',
                'delegation_ar',
                'telephone_1',
                'telephone_2',
                'observation_fr',
                'observation_ar',
                'photo',
                'email',
                'type_user_fr',
                'type_user_ar',
            ]);

            // Définir les valeurs par défaut pour la nationalité si non fournies
            if (!isset($userData['nationalite_fr'])) {
                $userData['nationalite_fr'] = 'Tunisienne';
            }
            if (!isset($userData['nationalite_ar'])) {
                $userData['nationalite_ar'] = 'تونسية';
            }

            $userData['password'] = Hash::make($request->password);
            $userData['ajouter_par'] = Auth::id();

            $user = User::create($userData);

            // Assigner le rôle
            $user->assignRole($role->name);

            // Assigner le centre avec les champs pivot si fourni
            if ($request->centre) {
                $pivotData = [
                    'centre_statut_fr' => $request->centre_statut_fr,
                    'centre_statut_ar' => $request->centre_statut_ar,
                    'type_mouvement_fr' => $request->type_mouvement_fr,
                    'type_mouvement_ar' => $request->type_mouvement_ar,
                    'observation_fr' => $request->pivot_observation_fr,
                    'observation_ar' => $request->pivot_observation_ar,
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'statut' => $request->pivot_statut ?? 'Actif',
                    'updated_by' => Auth::id(),
                ];

                $user->centres()->attach($request->centre, $pivotData);
            }

            // Journalisation de la création
            activity()->performedOn($user)
                ->causedBy(auth()->user())
                ->log('Utilisateur créé');

            DB::commit();
            return response()->json([
                'data' => $user->load(['roles', 'centres']),
                'message' => 'Utilisateur créé avec succès.'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'utilisateur:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création de l\'utilisateur.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a specific user.
     */
    public function show($id)
    {
        try {
            $user = User::with(['roles', 'centres'])->findOrFail($id);
            return response()->json([
                'data' => $user,
                'message' => 'Utilisateur récupéré avec succès.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'utilisateur:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Utilisateur non trouvé.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update a user.
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $user = User::findOrFail($id);

            $validGenres = ['Homme', 'Femme', 'Autre'];
            $validTypeUsers = ['Personnel Direction Centrale', 'Personnel Centre', 'Stagiaire', 'Apprenti'];
            $validCentreStatuts = ['Centre officiel', 'Centre secondaire'];
            $validTypeMouvements = ['Affectation initiale', 'Transfert', 'Détachement'];

            $rules = [
                'nom_fr' => ['required', 'string', 'max:255'],
                'prenom_fr' => ['required', 'string', 'max:255'],
                'nom_ar' => ['nullable', 'string', 'max:255'],
                'prenom_ar' => ['nullable', 'string', 'max:255'],
                'date_naissance' => ['nullable', 'date'],
                'lieu_naissance_fr' => ['nullable', 'string', 'max:255'],
                'lieu_naissance_ar' => ['nullable', 'string', 'max:255'],
                'nationalite_fr' => ['nullable', 'string', 'max:255'],
                'nationalite_ar' => ['nullable', 'string', 'max:255'],
                'genre_fr' => ['required', Rule::in($validGenres)],
                'genre_ar' => ['nullable', 'string', 'max:255'],
                'statut' => ['required', Rule::in(['Actif', 'Inactif'])],
                'adresse_fr' => ['nullable', 'string'],
                'adresse_ar' => ['nullable', 'string'],
                'gouvernorat_fr' => ['nullable', 'string', 'max:255'],
                'gouvernorat_ar' => ['nullable', 'string', 'max:255'],
                'delegation_fr' => ['nullable', 'string', 'max:255'],
                'delegation_ar' => ['nullable', 'string', 'max:255'],
                'telephone_1' => ['nullable', 'string', 'max:20'],
                'telephone_2' => ['nullable', 'string', 'max:20'],
                'observation_fr' => ['nullable', 'string'],
                'observation_ar' => ['nullable', 'string'],
                'photo' => ['nullable', 'string', 'regex:/^data:image\/(png|jpeg);base64,/'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
                'type_user_fr' => ['required', Rule::in($validTypeUsers)],
                'type_user_ar' => ['nullable', 'string', 'max:255'],
                'role' => ['required', 'exists:roles,id'],
                'centre' => ['nullable', 'exists:centres,id'],
                'centre_statut_fr' => ['nullable', Rule::in($validCentreStatuts)],
                'centre_statut_ar' => ['nullable', 'string', 'max:255'],
                'type_mouvement_fr' => ['nullable', Rule::in($validTypeMouvements)],
                'type_mouvement_ar' => ['nullable', 'string', 'max:255'],
                'pivot_observation_fr' => ['nullable', 'string'],
                'pivot_observation_ar' => ['nullable', 'string'],
                'date_debut' => ['nullable', 'date'],
                'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
                'pivot_statut' => ['nullable', Rule::in(['Actif', 'Inactif', 'Transféré'])],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation échouée',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Vérifier si le rôle nécessite un centre
            $role = Role::findOrFail($request->role);
            if ($role->is_centre_role && !$request->centre) {
                return response()->json([
                    'message' => 'Le centre est requis pour ce rôle.',
                    'errors' => ['centre' => ['Le centre est requis pour ce rôle.']],
                ], 422);
            }

            // Calculer la taille de la photo en Base64
            if ($request->photo) {
                $base64String = preg_replace('#^data:image/\w+;base64,#i', '', $request->photo);
                $base64Size = (strlen($base64String) * 3 / 4) / 1024 / 1024; // Taille en Mo
                if ($base64Size > 2) {
                    return response()->json([
                        'message' => 'La photo dépasse la taille maximale de 2 Mo.',
                        'errors' => ['photo' => ['La photo doit être inférieure à 2 Mo.']],
                    ], 422);
                }
            }

            $userData = $request->only([
                'nom_fr',
                'prenom_fr',
                'nom_ar',
                'prenom_ar',
                'date_naissance',
                'lieu_naissance_fr',
                'lieu_naissance_ar',
                'nationalite_fr',
                'nationalite_ar',
                'genre_fr',
                'genre_ar',
                'statut',
                'adresse_fr',
                'adresse_ar',
                'gouvernorat_fr',
                'gouvernorat_ar',
                'delegation_fr',
                'delegation_ar',
                'telephone_1',
                'telephone_2',
                'observation_fr',
                'observation_ar',
                'photo',
                'email',
                'type_user_fr',
                'type_user_ar',
            ]);

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Mettre à jour le rôle
            $user->syncRoles([$role->name]);

            // Mettre à jour le centre avec les champs pivot
            if ($request->centre) {
                $pivotData = [
                    'centre_statut_fr' => $request->centre_statut_fr,
                    'centre_statut_ar' => $request->centre_statut_ar,
                    'type_mouvement_fr' => $request->type_mouvement_fr,
                    'type_mouvement_ar' => $request->type_mouvement_ar,
                    'observation_fr' => $request->pivot_observation_fr,
                    'observation_ar' => $request->pivot_observation_ar,
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'statut' => $request->pivot_statut ?? 'Actif',
                    'updated_by' => Auth::id(),
                ];

                $user->centres()->sync([$request->centre => $pivotData]);
            } else {
                $user->centres()->sync([]);
            }

            // Journalisation de la mise à jour
            activity()->performedOn($user)
                ->causedBy(auth()->user())
                ->log('Utilisateur mis à jour');

            DB::commit();
            return response()->json([
                'data' => $user->load(['roles', 'centres']),
                'message' => 'Utilisateur mis à jour avec succès.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'utilisateur:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour de l\'utilisateur.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a user.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Vérifier le mot de passe de l'utilisateur connecté
            if (!Hash::check($request->password, auth()->user()->password)) {
                return response()->json([
                    'message' => 'Mot de passe incorrect.',
                    'error' => 'Le mot de passe fourni est incorrect.',
                ], 403);
            }

            // Journalisation de la suppression
            activity()->performedOn($user)
                ->causedBy(auth()->user())
                ->log('Utilisateur supprimé');

            $user->delete();

            return response()->json([
                'message' => 'Utilisateur supprimé avec succès.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'utilisateur:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Une erreur est survenue lors de la suppression de l\'utilisateur.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user activities.
     */
    public function getUserActivities($id)
    {
        try {
            $user = User::findOrFail($id);
            $activities = Activity::where('subject_id', $user->id)
                ->where('subject_type', User::class)
                ->with(['causer' => fn($q) => $q->select('id', 'nom_fr', 'prenom_fr', 'photo')])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'title' => $activity->description,
                        'description' => $activity->description,
                        'date' => $activity->created_at->toISOString(),
                        'user' => $activity->causer ? [
                            'id' => $activity->causer->id,
                            'firstname' => $activity->causer->nom_fr,
                            'lastname' => $activity->causer->prenom_fr,
                            'photo' => $activity->causer->photo,
                        ] : null,
                    ];
                });

            return response()->json([
                'data' => $activities,
                'message' => 'Activités de l\'utilisateur récupérées avec succès.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des activités de l\'utilisateur:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des activités.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
