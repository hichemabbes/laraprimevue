<?php

namespace App\Http\Controllers\brillants\Centres\Utilisateurs\Personnels;

use App\Http\Controllers\Controller;
use App\Models\Centre\Centre;
use App\Models\Centre\Utilisateurs\Personnels\UserCentre;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PersonnelsController extends Controller
{
    /**
     * Fetch form data (users, centres) for the frontend.
     */
    public function getFormData()
    {
        try {
            $users = User::select('id', 'matricule', 'nom_fr', 'prenom_fr')
                ->whereNotNull('matricule')
                ->whereNull('deleted_at')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'matricule' => $user->matricule,
                        'display_name' => trim("{$user->nom_fr} {$user->prenom_fr} ({$user->matricule})"),
                    ];
                });

            $centres = Centre::select('id', 'nom_fr', 'nom_ar')->get();

            $data = [
                'users' => $users,
                'centres' => $centres,
            ];

            Log::info('Form data retrieved successfully', [
                'users_count' => $users->count(),
                'centres_count' => $centres->count(),
            ]);

            return response()->json($data, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des données du formulaire: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Erreur lors de la récupération des données.'], 500);
        }
    }

    /**
     * Afficher la liste des affectations utilisateur-centre.
     */
    public function index(Request $request)
    {
        try {
            $query = UserCentre::with([
                'user' => function ($query) {
                    $query->select('id', 'nom_fr', 'prenom_fr', 'matricule');
                },
                'centre' => function ($query) {
                    $query->select('id', 'nom_fr', 'nom_ar');
                }
            ])->whereNull('deleted_at');

            $userCentres = $query->get()->map(function ($userCentre) {
                return [
                    'id' => $userCentre->id,
                    'user_id' => $userCentre->user_id,
                    'centre_id' => $userCentre->centre_id,
                    'qualite_fr' => $userCentre->qualite_fr,
                    'qualite_ar' => $userCentre->qualite_ar,
                    'etablissement_origine_fr' => $userCentre->etablissement_origine_fr,
                    'etablissement_origine_ar' => $userCentre->etablissement_origine_ar,
                    'centre_statut_fr' => $userCentre->centre_statut_fr,
                    'centre_statut_ar' => $userCentre->centre_statut_ar,
                    'observation_fr' => $userCentre->observation_fr,
                    'observation_ar' => $userCentre->observation_ar,
                    'date_debut' => $userCentre->date_debut,
                    'date_fin' => $userCentre->date_fin,
                    'statut' => $userCentre->statut,
                    'user' => $userCentre->user ? [
                        'id' => $userCentre->user->id,
                        'nom_fr' => $userCentre->user->nom_fr,
                        'prenom_fr' => $userCentre->user->prenom_fr,
                        'matricule' => $userCentre->user->matricule,
                    ] : null,
                    'centre' => $userCentre->centre ? [
                        'id' => $userCentre->centre->id,
                        'nom_fr' => $userCentre->centre->nom_fr,
                        'nom_ar' => $userCentre->centre->nom_ar,
                    ] : null,
                ];
            });

            return response()->json($userCentres, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des affectations utilisateur-centre: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur serveur lors de la récupération des données.'], 500);
        }
    }

    /**
     * Créer une nouvelle affectation utilisateur-centre.
     */
    public function store(Request $request)
    {
        $validQualites = ['Personnel (ATFP)', 'Personnel (Externe)'];
        $validCentreStatuts = ['Centre officiel', 'Centre secondaire'];

        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
            'centre_id' => ['required', 'exists:centres,id'],
            'qualite_fr' => ['nullable', Rule::in($validQualites)],
            'qualite_ar' => ['nullable', 'string', 'max:255'],
            'etablissement_origine_fr' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn() => $request->qualite_fr === 'Personnel (Externe)')],
            'etablissement_origine_ar' => ['nullable', 'string', 'max:255'],
            'centre_statut_fr' => ['nullable', Rule::in($validCentreStatuts)],
            'centre_statut_ar' => ['nullable', 'string', 'max:255'],
            'observation_fr' => ['nullable', 'string'],
            'observation_ar' => ['nullable', 'string'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'statut' => ['required', Rule::in(['Actif', 'Inactif'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $userCentre = UserCentre::create($request->all());

            $userCentre->load([
                'user' => function ($query) {
                    $query->select('id', 'nom_fr', 'prenom_fr', 'matricule');
                },
                'centre' => function ($query) {
                    $query->select('id', 'nom_fr', 'nom_ar');
                }
            ]);

            DB::commit();

            return response()->json([
                'id' => $userCentre->id,
                'user_id' => $userCentre->user_id,
                'centre_id' => $userCentre->centre_id,
                'qualite_fr' => $userCentre->qualite_fr,
                'qualite_ar' => $userCentre->qualite_ar,
                'etablissement_origine_fr' => $userCentre->etablissement_origine_fr,
                'etablissement_origine_ar' => $userCentre->etablissement_origine_ar,
                'centre_statut_fr' => $userCentre->centre_statut_fr,
                'centre_statut_ar' => $userCentre->centre_statut_ar,
                'observation_fr' => $userCentre->observation_fr,
                'observation_ar' => $userCentre->observation_ar,
                'date_debut' => $userCentre->date_debut,
                'date_fin' => $userCentre->date_fin,
                'statut' => $userCentre->statut,
                'user_matricule' => $userCentre->user ? $userCentre->user->matricule : null,
                'centre_nom_fr' => $userCentre->centre ? $userCentre->centre->nom_fr : null,
                'centre_nom_ar' => $userCentre->centre ? $userCentre->centre->nom_ar : null,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'affectation: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la création de l\'affectation.'], 500);
        }
    }

    /**
     * Afficher une affectation spécifique.
     */
    public function show($id)
    {
        try {
            $userCentre = UserCentre::with([
                'user' => function ($query) {
                    $query->select('id', 'nom_fr', 'prenom_fr', 'matricule');
                },
                'centre' => function ($query) {
                    $query->select('id', 'nom_fr', 'nom_ar');
                }
            ])->findOrFail($id);

            return response()->json([
                'id' => $userCentre->id,
                'user_id' => $userCentre->user_id,
                'centre_id' => $userCentre->centre_id,
                'qualite_fr' => $userCentre->qualite_fr,
                'qualite_ar' => $userCentre->qualite_ar,
                'etablissement_origine_fr' => $userCentre->etablissement_origine_fr,
                'etablissement_origine_ar' => $userCentre->etablissement_origine_ar,
                'centre_statut_fr' => $userCentre->centre_statut_fr,
                'centre_statut_ar' => $userCentre->centre_statut_ar,
                'observation_fr' => $userCentre->observation_fr,
                'observation_ar' => $userCentre->observation_ar,
                'date_debut' => $userCentre->date_debut,
                'date_fin' => $userCentre->date_fin,
                'statut' => $userCentre->statut,
                'user' => $userCentre->user ? [
                    'id' => $userCentre->user->id,
                    'nom_fr' => $userCentre->user->nom_fr,
                    'prenom_fr' => $userCentre->user->prenom_fr,
                    'matricule' => $userCentre->user->matricule,
                ] : null,
                'centre' => $userCentre->centre ? [
                    'id' => $userCentre->centre->id,
                    'nom_fr' => $userCentre->centre->nom_fr,
                    'nom_ar' => $userCentre->centre->nom_ar,
                ] : null,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'affectation: ' . $e->getMessage());
            return response()->json(['message' => 'Affectation non trouvée.'], 404);
        }
    }

    /**
     * Mettre à jour une affectation utilisateur-centre.
     */
    public function update(Request $request, $id)
    {
        $validQualites = ['Personnel (ATFP)', 'Personnel (Externe)'];
        $validCentreStatuts = ['Centre officiel', 'Centre secondaire'];

        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
            'centre_id' => ['required', 'exists:centres,id'],
            'qualite_fr' => ['nullable', Rule::in($validQualites)],
            'qualite_ar' => ['nullable', 'string', 'max:255'],
            'etablissement_origine_fr' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn() => $request->qualite_fr === 'Personnel (Externe)')],
            'etablissement_origine_ar' => ['nullable', 'string', 'max:255'],
            'centre_statut_fr' => ['nullable', Rule::in($validCentreStatuts)],
            'centre_statut_ar' => ['nullable', 'string', 'max:255'],
            'observation_fr' => ['nullable', 'string'],
            'observation_ar' => ['nullable', 'string'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'statut' => ['required', Rule::in(['Actif', 'Inactif'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $userCentre = UserCentre::findOrFail($id);
            $userCentre->update($request->all());

            $userCentre->load([
                'user' => function ($query) {
                    $query->select('id', 'nom_fr', 'prenom_fr', 'matricule');
                },
                'centre' => function ($query) {
                    $query->select('id', 'nom_fr', 'nom_ar');
                }
            ]);

            DB::commit();

            return response()->json([
                'id' => $userCentre->id,
                'user_id' => $userCentre->user_id,
                'centre_id' => $userCentre->centre_id,
                'qualite_fr' => $userCentre->qualite_fr,
                'qualite_ar' => $userCentre->qualite_ar,
                'etablissement_origine_fr' => $userCentre->etablissement_origine_fr,
                'etablissement_origine_ar' => $userCentre->etablissement_origine_ar,
                'centre_statut_fr' => $userCentre->centre_statut_fr,
                'centre_statut_ar' => $userCentre->centre_statut_ar,
                'observation_fr' => $userCentre->observation_fr,
                'observation_ar' => $userCentre->observation_ar,
                'date_debut' => $userCentre->date_debut,
                'date_fin' => $userCentre->date_fin,
                'statut' => $userCentre->statut,
                'user_matricule' => $userCentre->user ? $userCentre->user->matricule : null,
                'centre_nom_fr' => $userCentre->centre ? $userCentre->centre->nom_fr : null,
                'centre_nom_ar' => $userCentre->centre ? $userCentre->centre->nom_ar : null,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'affectation: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour de l\'affectation.'], 500);
        }
    }

    /**
     * Supprimer une affectation utilisateur-centre.
     */
    public function destroy(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            if (!password_verify($request->password, $user->password)) {
                return response()->json(['error' => 'Mot de passe incorrect.'], 401);
            }

            $userCentre = UserCentre::findOrFail($id);
            $userCentre->delete();

            DB::commit();

            return response()->json(['message' => 'Affectation supprimée avec succès.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'affectation: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression de l\'affectation.'], 500);
        }
    }
}
