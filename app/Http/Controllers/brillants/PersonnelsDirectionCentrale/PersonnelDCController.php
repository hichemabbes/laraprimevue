<?php
namespace App\Http\Controllers\brillants\PersonnelsDirectionCentrale;
use App\Http\Controllers\ATFP\Utilisateurs\PersonnelsDirectionCentrale\Excel;
use App\Http\Controllers\ATFP\Utilisateurs\PersonnelsDirectionCentrale\PersonnelAtfpExport;
use App\Http\Controllers\ATFP\Utilisateurs\PersonnelsDirectionCentrale\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\Liste;
use App\Models\User;
use App\Models\Utilisateurs\PersonnelsDirectionCentrale\PersonnelDC;
use App\Services\Import\PersonnelsAtfpImportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;

class PersonnelDCController extends Controller
{

    public function indexWithOptions(Request $request)
    {
        Log::info('indexWithOptions appelé pour personnels ATFP', [
            'per_page' => $request->input('per_page', 25),
            'user' => auth()->user() ? auth()->user()->id : 'non authentifié',
        ]);

        try {
            $perPage = $request->input('per_page', 25);

            // Charger les personnels avec les relations nécessaires
            $personnels = PersonnelDC::with([
                'user.roles' => function ($query) {
                    $query->select('roles.id', 'roles.name', 'roles.name_ar');
                },
                'user.addedBy' => function ($query) {
                    $query->select('id', 'prenom_fr', 'nom_fr');
                }
            ])
                ->join('users', 'personnels_direction_centrale.user_id', '=', 'users.id')
                ->leftJoin('users as added_by_users', 'users.ajouter_par', '=', 'added_by_users.id')
                ->select(
                    'personnels_direction_centrale.*',
                    'users.id as user_id',
                    'users.nom_fr',
                    'users.prenom_fr',
                    'users.nom_ar',
                    'users.prenom_ar',
                    'users.date_naissance',
                    'users.lieu_naissance_fr',
                    'users.lieu_naissance_ar',
                    'users.nationalite_fr',
                    'users.nationalite_ar',
                    'users.genre_fr',
                    'users.genre_ar',
                    'users.statut',
                    'users.adresse_fr',
                    'users.adresse_ar',
                    'users.gouvernorat_fr',
                    'users.gouvernorat_ar',
                    'users.delegation_fr',
                    'users.delegation_ar',
                    'users.telephone_1',
                    'users.telephone_2',
                    'users.observation_fr',
                    'users.observation_ar',
                    'users.photo',
                    'users.email',
                    'users.ajouter_par',
                    'users.created_at as user_created_at',
                    'users.updated_at as user_updated_at',
                    'added_by_users.prenom_fr as added_by_prenom_fr',
                    'added_by_users.nom_fr as added_by_nom_fr'
                )
                ->whereNull('users.deleted_at')
                ->orderBy('personnels_direction_centrale.id', 'asc')
                ->paginate($perPage);

            // Formater les données pour inclure explicitement les rôles
            $formattedPersonnels = $personnels->getCollection()->map(function ($personnel) {
                return [
                    'id' => $personnel->id,
                    'user_id' => $personnel->user_id,
                    'nom_fr' => $personnel->nom_fr,
                    'prenom_fr' => $personnel->prenom_fr,
                    'nom_ar' => $personnel->nom_ar,
                    'prenom_ar' => $personnel->prenom_ar,
                    'cin' => $personnel->cin,
                    'matricule' => $personnel->matricule,
                    'date_cin' => $personnel->date_cin,
                    'lieu_cin_fr' => $personnel->lieu_cin_fr,
                    'lieu_cin_ar' => $personnel->lieu_cin_ar,
                    'date_naissance' => $personnel->user->date_naissance,
                    'lieu_naissance_fr' => $personnel->user->lieu_naissance_fr,
                    'lieu_naissance_ar' => $personnel->user->lieu_naissance_ar,
                    'nationalite_fr' => $personnel->user->nationalite_fr,
                    'nationalite_ar' => $personnel->user->nationalite_ar,
                    'genre_fr' => $personnel->user->genre_fr,
                    'genre_ar' => $personnel->user->genre_ar,
                    'etat_civil_fr' => $personnel->etat_civil_fr,
                    'etat_civil_ar' => $personnel->etat_civil_ar,
                    'adresse_fr' => $personnel->user->adresse_fr,
                    'adresse_ar' => $personnel->user->adresse_ar,
                    'gouvernorat_fr' => $personnel->user->gouvernorat_fr,
                    'gouvernorat_ar' => $personnel->user->gouvernorat_ar,
                    'delegation_fr' => $personnel->user->delegation_fr,
                    'delegation_ar' => $personnel->user->delegation_ar,
                    'telephone_1' => $personnel->user->telephone_1,
                    'telephone_2' => $personnel->user->telephone_2,
                    'statut' => $personnel->user->statut,
                    'observation_fr' => $personnel->user->observation_fr,
                    'observation_ar' => $personnel->user->observation_ar,
                    'photo' => $personnel->user->photo,
                    'email' => $personnel->user->email,
                    'ajouter_par' => $personnel->user->ajouter_par,
                    'qualite_fr' => $personnel->qualite_fr,
                    'qualite_ar' => $personnel->qualite_ar,
                    'date_recrutement' => $personnel->date_recrutement,
                    'date_fin_service' => $personnel->date_fin_service,
                    'etablissement_origine_fr' => $personnel->etablissement_origine_fr,
                    'etablissement_origine_ar' => $personnel->etablissement_origine_ar,
                    'mission_fr' => $personnel->mission_fr,
                    'mission_ar' => $personnel->mission_ar,
                    'cause_inactivite_fr' => $personnel->cause_inactivite_fr,
                    'cause_inactivite_ar' => $personnel->cause_inactivite_ar,
                    'roles' => $personnel->user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'name_ar' => $role->name_ar,
                        ];
                    })->toArray(),
                    'added_by_prenom_fr' => $personnel->added_by_prenom_fr,
                    'added_by_nom_fr' => $personnel->added_by_nom_fr,
                    'created_at' => $personnel->user_created_at,
                    'updated_at' => $personnel->user_updated_at,
                ];
            });

            // Remplacer la collection paginée par la collection formatée
            $personnels->setCollection($formattedPersonnels);

            $lists = Liste::whereIn('nom_fr', ['Gouvernorats'])
                ->where('statut', 'Actif')
                ->get()
                ->pluck('options', 'nom_fr');

            Log::info('Données renvoyées', [
                'personnels_count' => $personnels->count(),
                'lists' => $lists->toArray(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'personnels' => $personnels,
                    'lists' => $lists,
                ],
                'message' => 'Liste des personnels ATFP et options récupérée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in indexWithOptions:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showWithOptions($id)
    {
        Log::info('showWithOptions appelé pour personnel ID:', ['id' => $id]);
        try {
            $personnel = PersonnelDC::with([
                'user.roles' => function ($query) {
                    $query->select('roles.id', 'roles.name', 'roles.name_ar');
                }
            ])->findOrFail($id);
            $lists = Liste::whereIn('nom_fr', ['Gouvernorats', 'Rôles'])
                ->where('statut', 'Actif')
                ->get()
                ->mapWithKeys(function ($list) {
                    return [$list->nom_fr => $list->nom_fr === 'Rôles' ? Role::select('id', 'name', 'name_ar')->get() : $list->options];
                });
            $userData = [
                'id' => $personnel->id,
                'user_id' => $personnel->user_id,
                'nom_fr' => $personnel->user->nom_fr,
                'prenom_fr' => $personnel->user->prenom_fr,
                'nom_ar' => $personnel->user->nom_ar,
                'prenom_ar' => $personnel->user->prenom_ar,
                'cin' => $personnel->cin,
                'matricule' => $personnel->matricule,
                'date_cin' => $personnel->date_cin,
                'lieu_cin_fr' => $personnel->lieu_cin_fr,
                'lieu_cin_ar' => $personnel->lieu_cin_ar,
                'date_naissance' => $personnel->user->date_naissance,
                'lieu_naissance_fr' => $personnel->user->lieu_naissance_fr,
                'lieu_naissance_ar' => $personnel->user->lieu_naissance_ar,
                'nationalite_fr' => $personnel->user->nationalite_fr,
                'nationalite_ar' => $personnel->user->nationalite_ar,
                'genre_fr' => $personnel->user->genre_fr,
                'genre_ar' => $personnel->user->genre_ar,
                'etat_civil_fr' => $personnel->etat_civil_fr,
                'etat_civil_ar' => $personnel->etat_civil_ar,
                'adresse_fr' => $personnel->user->adresse_fr,
                'adresse_ar' => $personnel->user->adresse_ar,
                'gouvernorat_fr' => $personnel->user->gouvernorat_fr,
                'gouvernorat_ar' => $personnel->user->gouvernorat_ar,
                'delegation_fr' => $personnel->user->delegation_fr,
                'delegation_ar' => $personnel->user->delegation_ar,
                'telephone_1' => $personnel->user->telephone_1,
                'telephone_2' => $personnel->user->telephone_2,
                'statut' => $personnel->user->statut,
                'observation_fr' => $personnel->user->observation_fr,
                'observation_ar' => $personnel->user->observation_ar,
                'photo' => $personnel->user->photo,
                'email' => $personnel->user->email,
                'ajouter_par' => $personnel->user->ajouter_par,
                'qualite_fr' => $personnel->qualite_fr,
                'qualite_ar' => $personnel->qualite_ar,
                'date_recrutement' => $personnel->date_recrutement,
                'etablissement_origine_fr' => $personnel->etablissement_origine_fr,
                'etablissement_origine_ar' => $personnel->etablissement_origine_ar,
                'mission_fr' => $personnel->mission_fr,
                'mission_ar' => $personnel->mission_ar,
                'roles' => $personnel->user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'name_ar' => $role->name_ar,
                    ];
                })->toArray(),
            ];
            return response()->json([
                'success' => true,
                'data' => [
                    'personnel' => $userData,
                    'lists' => $lists,
                    'roles' => $personnel->user->roles->pluck('id')->toArray(),
                ],
                'message' => 'Détails du personnel ATFP et options récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in showWithOptions:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getUserRoles($id)
    {
        try {
            $personnel = PersonnelDC::findOrFail($id);
            $roles = $personnel->user->roles()->select('roles.id', 'roles.name', 'roles.name_ar')->get();
            Log::info('Fetched roles for user:', ['user_id' => $personnel->user_id, 'roles' => $roles->toArray()]);
            return response()->json([
                'success' => true,
                'data' => $roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'name_ar' => $role->name_ar,
                    ];
                })->toArray(),
                'message' => 'Rôles du personnel récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getUserRoles:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des rôles',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function index()
    {
        try {
            $personnels = PersonnelDC::with([
                'user.roles' => function ($query) {
                    $query->select('roles.id', 'roles.name', 'roles.name_ar');
                },
                'user.addedBy' => function ($query) {
                    $query->select('id', 'prenom_fr', 'nom_fr');
                }
            ])
                ->join('users', 'personnels_direction_centrale.user_id', '=', 'users.id')
                ->select('personnels_direction_centrale.*')
                ->whereNull('users.deleted_at')
                ->orderBy('personnels_direction_centrale.id', 'asc')
                ->paginate(25);

            // Formater les données pour inclure explicitement les rôles
            $formattedPersonnels = $personnels->getCollection()->map(function ($personnel) {
                return [
                    'id' => $personnel->id,
                    'user_id' => $personnel->user_id,
                    'nom_fr' => $personnel->user->nom_fr,
                    'prenom_fr' => $personnel->user->prenom_fr,
                    'nom_ar' => $personnel->user->nom_ar,
                    'prenom_ar' => $personnel->user->prenom_ar,
                    'cin' => $personnel->cin,
                    'matricule' => $personnel->matricule,
                    'roles' => $personnel->user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'name_ar' => $role->name_ar,
                        ];
                    })->toArray(),
                    // Ajouter d'autres champs si nécessaire
                ];
            });

            $personnels->setCollection($formattedPersonnels);

            return response()->json([
                'success' => true,
                'data' => [
                    'personnels' => $personnels,
                ],
                'message' => 'Liste des personnels ATFP récupérée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('PersonnelController::index - Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $personnel = PersonnelDC::with([
                'user.roles' => function ($query) {
                    $query->select('roles.id', 'roles.name', 'roles.name_ar');
                }
            ])->findOrFail($id);

            $userData = [
                'id' => $personnel->id,
                'user_id' => $personnel->user_id,
                'nom_fr' => $personnel->user->nom_fr,
                'prenom_fr' => $personnel->user->prenom_fr,
                'nom_ar' => $personnel->user->nom_ar,
                'prenom_ar' => $personnel->user->prenom_ar,
                'cin' => $personnel->cin,
                'matricule' => $personnel->matricule,
                'roles' => $personnel->user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'name_ar' => $role->name_ar,
                    ];
                })->toArray(),
                // Ajouter d'autres champs si nécessaire
            ];

            return response()->json([
                'success' => true,
                'data' => $userData,
                'message' => 'Détails du personnel ATFP récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in show:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        Log::info('Store request data:', $request->all());
        $genreMap = [
            'Homme' => 'ذكر',
            'Femme' => 'أنثى',
            'Autre' => 'أخرى',
        ];
        $etatCivilMap = [
            'Célibataire' => 'أعزب(اء)',
            'Marié' => 'متزوج(ة)',
            'Divorcé' => 'مطلق(ة)',
            'Veuf' => 'أرمل(ة)',
        ];
        $qualiteMap = [
            'Personnel (ATFP)' => 'عون الوكالة',
            'Personnel (Externe)' => 'عون من خارج الوكالة',
        ];
        $validator = Validator::make($request->all(), [
            // Champs de l'utilisateur
            'nom_fr' => 'required|string|max:255',
            'prenom_fr' => 'required|string|max:255',
            'nom_ar' => 'nullable|string|max:255',
            'prenom_ar' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date|before:today',
            'lieu_naissance_fr' => 'nullable|string|max:255',
            'lieu_naissance_ar' => 'nullable|string|max:255',
            'nationalite_fr' => 'nullable|string|max:255',
            'nationalite_ar' => 'nullable|string|max:255',
            'genre_fr' => 'required|in:Homme,Femme,Autre',
            'genre_ar' => 'nullable|string|max:255',
            'adresse_fr' => 'nullable|string|max:16777215',
            'adresse_ar' => 'nullable|string|max:16777215',
            'gouvernorat_fr' => 'nullable|string|max:255',
            'gouvernorat_ar' => 'nullable|string|max:255',
            'delegation_fr' => 'nullable|string|max:255',
            'delegation_ar' => 'nullable|string|max:255',
            'telephone_1' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'telephone_2' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'observation_fr' => 'nullable|string|max:16777215',
            'observation_ar' => 'nullable|string|max:16777215',
            'photo' => [
                'nullable',
                'string',
                'max:16777215',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        if (!preg_match('/^data:image\/(png|jpeg|jpg|gif|bmp|webp);base64,/', $value)) {
                            $fail('Le format de la photo doit être PNG, JPEG, JPG, GIF, BMP ou WEBP.');
                        } else {
                            $base64String = preg_replace('/^data:image\/(png|jpeg|jpg|gif|bmp|webp);base64,/', '', $value);
                            $decoded = base64_decode($base64String, true);
                            if ($decoded === false) {
                                $fail('La chaîne Base64 de la photo est invalide.');
                            } else {
                                $sizeInBytes = strlen($decoded);
                                if ($sizeInBytes > 2 * 1024 * 1024) {
                                    $fail('La photo ne doit pas dépasser 2 Mo.');
                                }
                            }
                        }
                    }
                },
            ],
            'email' => 'required|string|email|max:255|unique:users,email',
            'type_user_fr' => 'required|in:Personnel Direction Centrale',
            'type_user_ar' => 'required|string|max:255',
            // Champs spécifiques au personnel DC
            'cin' => 'required|string|size:8|unique:personnels_direction_centrale,cin',
            'matricule' => 'required|string|max:20|unique:personnels_direction_centrale,matricule',
            'date_cin' => 'nullable|date|before:today',
            'lieu_cin_fr' => 'nullable|string|max:255',
            'lieu_cin_ar' => 'nullable|string|max:255',
            'etat_civil_fr' => 'nullable|string|max:255',
            'etat_civil_ar' => 'nullable|string|max:255',
            'qualite_fr' => 'required|in:Personnel (ATFP),Personnel (Externe)',
            'qualite_ar' => 'nullable|string|max:255',
            'date_recrutement' => 'nullable|date|before_or_equal:today',
            'etablissement_origine_fr' => 'required_if:qualite_fr,Personnel (Externe)|nullable|string|max:255',
            'etablissement_origine_ar' => 'nullable|string|max:255',
            'mission_fr' => 'nullable|string|max:16777215',
            'mission_ar' => 'nullable|string|max:16777215',
            'roles' => 'required|array|min:1',
            'roles.*.id' => 'exists:roles,id,guard_name,web',
            'roles.*.est_officiel' => 'required|in:0,1',
        ], [
            'nom_fr.required' => 'Le nom en français est requis.',
            'prenom_fr.required' => 'Le prénom en français est requis.',
            'cin.required' => 'Le CIN est requis.',
            'cin.size' => 'Le CIN doit contenir exactement 8 chiffres.',
            'cin.unique' => 'Ce CIN est déjà utilisé.',
            'matricule.required' => 'Le matricule est requis.',
            'matricule.unique' => 'Ce matricule est déjà utilisé.',
            'qualite_fr.required' => 'La qualité est requise.',
            'etablissement_origine_fr.required_if' => 'L\'établissement d\'origine est requis pour le personnel externe.',
            'email.required' => 'L\'email est requis.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'roles.required' => 'Au moins un rôle doit être sélectionné.',
        ]);
        if ($validator->fails()) {
            Log::error('Validation failed in store:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation échouée'
            ], 422);
        }
        try {
            $data = $validator->validated();
            // Map genre_ar, etat_civil_ar, qualite_ar
            $data['genre_ar'] = isset($data['genre_fr']) ? ($genreMap[$data['genre_fr']] ?? null) : null;
            $data['etat_civil_ar'] = isset($data['etat_civil_fr']) ? ($etatCivilMap[$data['etat_civil_fr']] ?? null) : null;
            $data['qualite_ar'] = isset($data['qualite_fr']) ? ($qualiteMap[$data['qualite_fr']] ?? null) : null;
            // Set default nationality values if not provided
            $data['nationalite_fr'] = $data['nationalite_fr'] ?? 'Tunisienne';
            $data['nationalite_ar'] = $data['nationalite_ar'] ?? 'تونسية';
            // Set default user type if not provided
            $data['type_user_fr'] = $data['type_user_fr'] ?? 'Personnel Direction Centrale';
            $data['type_user_ar'] = $data['type_user_ar'] ?? 'عون الإدارة المركزية';
            // Set statut to Actif by default
            $data['statut'] = 'Actif';
            // Generate password automatically
            $generatedPassword = ucfirst(strtolower($data['prenom_fr'])) . '123***';
            // Validate governorate if provided
            if (!empty($data['gouvernorat_fr'])) {
                $liste = Liste::where('nom_fr', 'Gouvernorats')->where('statut', 'Actif')->first();
                if ($liste && !empty($liste->options)) {
                    $option = collect($liste->options)->firstWhere('nom_fr', $data['gouvernorat_fr']);
                    if (!$option) {
                        Log::warning('Valeur invalide pour gouvernorat_fr:', ['value' => $data['gouvernorat_fr']]);
                        return response()->json([
                            'success' => false,
                            'message' => 'Valeur invalide pour gouvernorat',
                            'errors' => ['gouvernorat_fr' => ['Valeur invalide pour gouvernorat']]
                        ], 422);
                    }
                    $data['gouvernorat_fr'] = $option['nom_fr'];
                    $data['gouvernorat_ar'] = $option['nom_ar'] ?? $data['gouvernorat_ar'];
                } else {
                    Log::warning('Liste Gouvernorats introuvable ou vide.');
                    return response()->json([
                        'success' => false,
                        'message' => 'Liste Gouvernorats non disponible',
                        'errors' => ['gouvernorat_fr' => ['Liste Gouvernorats non disponible']]
                    ], 422);
                }
            }
            // Store photo as base64
            $photo = !empty($data['photo']) && str_starts_with($data['photo'], 'data:image/') ? $data['photo'] : null;
            // Create user
            $user = User::create([
                'nom_fr' => $data['nom_fr'],
                'prenom_fr' => $data['prenom_fr'],
                'nom_ar' => $data['nom_ar'] ?? null,
                'prenom_ar' => $data['prenom_ar'] ?? null,
                'date_naissance' => $data['date_naissance'] ?? null,
                'lieu_naissance_fr' => $data['lieu_naissance_fr'] ?? null,
                'lieu_naissance_ar' => $data['lieu_naissance_ar'] ?? null,
                'nationalite_fr' => $data['nationalite_fr'],
                'nationalite_ar' => $data['nationalite_ar'],
                'genre_fr' => $data['genre_fr'],
                'genre_ar' => $data['genre_ar'],
                'statut' => $data['statut'],
                'adresse_fr' => $data['adresse_fr'] ?? null,
                'adresse_ar' => $data['adresse_ar'] ?? null,
                'gouvernorat_fr' => $data['gouvernorat_fr'] ?? null,
                'gouvernorat_ar' => $data['gouvernorat_ar'] ?? null,
                'delegation_fr' => $data['delegation_fr'] ?? null,
                'delegation_ar' => $data['delegation_ar'] ?? null,
                'telephone_1' => $data['telephone_1'] ?? null,
                'telephone_2' => $data['telephone_2'] ?? null,
                'observation_fr' => $data['observation_fr'] ?? null,
                'observation_ar' => $data['observation_ar'] ?? null,
                'photo' => $photo,
                'email' => $data['email'],
                'password' => Hash::make($generatedPassword),
                'type_user_fr' => $data['type_user_fr'],
                'type_user_ar' => $data['type_user_ar'],
                'ajouter_par' => auth()->id(),
            ]);
            // Manually assign roles to model_has_roles
            foreach ($data['roles'] as $roleData) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $roleData['id'],
                    'model_type' => 'App\Models\User',
                    'model_id' => $user->id,
                    'centre_id' => null,
                    'date_debut' => Carbon::now(),
                    'date_fin' => null,
                    'est_officiel' => $roleData['est_officiel'] ?? 1,
                    'statut' => 'Actif',
                    'description' => null,
                    'observation' => null,
                    'assigned_by' => auth()->id(),
                    'desactivated_by' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            // Create personnel DC record
            $personnelDC = PersonnelDC::create([
                'user_id' => $user->id,
                'matricule' => $data['matricule'],
                'cin' => $data['cin'],
                'date_cin' => $data['date_cin'] ?? null,
                'lieu_cin_fr' => $data['lieu_cin_fr'] ?? null,
                'lieu_cin_ar' => $data['lieu_cin_ar'] ?? null,
                'etat_civil_fr' => $data['etat_civil_fr'] ?? null,
                'etat_civil_ar' => $data['etat_civil_ar'] ?? null,
                'qualite_fr' => $data['qualite_fr'],
                'qualite_ar' => $data['qualite_ar'],
                'date_recrutement' => $data['date_recrutement'] ?? null,
                'etablissement_origine_fr' => $data['etablissement_origine_fr'] ?? null,
                'etablissement_origine_ar' => $data['etablissement_origine_ar'] ?? null,
                'mission_fr' => $data['mission_fr'] ?? null,
                'mission_ar' => $data['mission_ar'] ?? null,
                'observation_fr' => $data['observation_fr'] ?? null,
                'observation_ar' => $data['observation_ar'] ?? null,
            ]);
            return response()->json([
                'success' => true,
                'data' => $personnelDC->load(['user.roles' => function ($query) {
                    $query->select('roles.id', 'roles.name', 'roles.name_ar');
                }]),
                'message' => 'Personnel ATFP créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error in store:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        Log::info('Update request data:', $request->all());
        $genreMap = [
            'Homme' => 'ذكر',
            'Femme' => 'أنثى',
            'Autre' => 'أخرى',
        ];
        $etatCivilMap = [
            'Célibataire' => 'أعزب(اء)',
            'Marié' => 'متزوج(ة)',
            'Divorcé' => 'مطلق(ة)',
            'Veuf' => 'أرمل(ة)',
        ];
        $qualiteMap = [
            'Personnel (ATFP)' => 'عون الوكالة',
            'Personnel (Externe)' => 'عون من خارج الوكالة',
        ];
        $personnelDC = PersonnelDC::findOrFail($id);
        $user = $personnelDC->user;
        $validator = Validator::make($request->all(), [
            // Champs de l'utilisateur
            'nom_fr' => 'required|string|max:255',
            'prenom_fr' => 'required|string|max:255',
            'nom_ar' => 'nullable|string|max:255',
            'prenom_ar' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date|before:today',
            'lieu_naissance_fr' => 'nullable|string|max:255',
            'lieu_naissance_ar' => 'nullable|string|max:255',
            'nationalite_fr' => 'nullable|string|max:255',
            'nationalite_ar' => 'nullable|string|max:255',
            'genre_fr' => 'required|in:Homme,Femme,Autre',
            'genre_ar' => 'nullable|string|max:255',
            'adresse_fr' => 'nullable|string|max:16777215',
            'adresse_ar' => 'nullable|string|max:16777215',
            'gouvernorat_fr' => 'nullable|string|max:255',
            'gouvernorat_ar' => 'nullable|string|max:255',
            'delegation_fr' => 'nullable|string|max:255',
            'delegation_ar' => 'nullable|string|max:255',
            'telephone_1' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'telephone_2' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'observation_fr' => 'nullable|string|max:16777215',
            'observation_ar' => 'nullable|string|max:16777215',
            'photo' => [
                'nullable',
                'string',
                'max:16777215',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        if (!preg_match('/^data:image\/(png|jpeg|jpg|gif|bmp|webp);base64,/', $value)) {
                            $fail('Le format de la photo doit être PNG, JPEG, JPG, GIF, BMP ou WEBP.');
                        } else {
                            $base64String = preg_replace('/^data:image\/(png|jpeg|jpg|gif|bmp|webp);base64,/', '', $value);
                            $decoded = base64_decode($base64String, true);
                            if ($decoded === false) {
                                $fail('La chaîne Base64 de la photo est invalide.');
                            } else {
                                $sizeInBytes = strlen($decoded);
                                if ($sizeInBytes > 2 * 1024 * 1024) {
                                    $fail('La photo ne doit pas dépasser 2 Mo.');
                                }
                            }
                        }
                    }
                },
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'type_user_fr' => 'required|in:Personnel Direction Centrale',
            'type_user_ar' => 'required|string|max:255',
            // Champs spécifiques au personnel DC
            'cin' => [
                'required',
                'string',
                'size:8',
                Rule::unique('personnels_direction_centrale')->ignore($personnelDC->id),
            ],
            'matricule' => [
                'required',
                'string',
                'max:20',
                Rule::unique('personnels_direction_centrale')->ignore($personnelDC->id),
            ],
            'date_cin' => 'nullable|date|before:today',
            'lieu_cin_fr' => 'nullable|string|max:255',
            'lieu_cin_ar' => 'nullable|string|max:255',
            'etat_civil_fr' => 'nullable|string|max:255',
            'etat_civil_ar' => 'nullable|string|max:255',
            'qualite_fr' => 'required|in:Personnel (ATFP),Personnel (Externe)',
            'qualite_ar' => 'nullable|string|max:255',
            'date_recrutement' => 'nullable|date|before_or_equal:today',
            'etablissement_origine_fr' => 'required_if:qualite_fr,Personnel (Externe)|nullable|string|max:255',
            'etablissement_origine_ar' => 'nullable|string|max:255',
            'mission_fr' => 'nullable|string|max:16777215',
            'mission_ar' => 'nullable|string|max:16777215',
            'date_fin_service' => 'nullable|date|after_or_equal:date_recrutement',
            'cause_inactivite_fr' => 'required_if:statut,Inactif|nullable|string|max:65535',
            'cause_inactivite_ar' => 'nullable|string|max:65535',
            // Champs pour les rôles
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);
        if ($validator->fails()) {
            Log::error('Validation failed in update:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation échouée'
            ], 422);
        }
        try {
            $data = $validator->validated();
            // Map genre_ar, etat_civil_ar, qualite_ar
            $data['genre_ar'] = isset($data['genre_fr']) ? ($genreMap[$data['genre_fr']] ?? null) : null;
            $data['etat_civil_ar'] = isset($data['etat_civil_fr']) ? ($etatCivilMap[$data['etat_civil_fr']] ?? null) : null;
            $data['qualite_ar'] = isset($data['qualite_fr']) ? ($qualiteMap[$data['qualite_fr']] ?? null) : null;
            // Set default nationality values if not provided
            $data['nationalite_fr'] = $data['nationalite_fr'] ?? 'Tunisienne';
            $data['nationalite_ar'] = $data['nationalite_ar'] ?? 'تونسية';
            // Set default user type if not provided
            $data['type_user_fr'] = $data['type_user_fr'] ?? 'Personnel Direction Centrale';
            $data['type_user_ar'] = $data['type_user_ar'] ?? 'عون الإدارة المركزية';
            // Validate governorate if provided
            if (!empty($data['gouvernorat_fr'])) {
                $liste = Liste::where('nom_fr', 'Gouvernorats')->where('statut', 'Actif')->first();
                if ($liste && !empty($liste->options)) {
                    $option = collect($liste->options)->firstWhere('nom_fr', $data['gouvernorat_fr']);
                    if (!$option) {
                        Log::warning('Valeur invalide pour gouvernorat_fr:', ['value' => $data['gouvernorat_fr']]);
                        return response()->json([
                            'success' => false,
                            'message' => 'Valeur invalide pour gouvernorat',
                            'errors' => ['gouvernorat_fr' => ['Valeur invalide pour gouvernorat']]
                        ], 422);
                    }
                    $data['gouvernorat_fr'] = $option['nom_fr'];
                    $data['gouvernorat_ar'] = $option['nom_ar'] ?? $data['gouvernorat_ar'];
                } else {
                    Log::warning('Liste Gouvernorats introuvable ou vide.');
                    return response()->json([
                        'success' => false,
                        'message' => 'Liste Gouvernorats non disponible',
                        'errors' => ['gouvernorat_fr' => ['Liste Gouvernorats non disponible']]
                    ], 422);
                }
            }
            // Store photo as base64
            $photo = !empty($data['photo']) && str_starts_with($data['photo'], 'data:image/') ? $data['photo'] : $user->photo;
            // Update user
            $updateUserData = [
                'nom_fr' => $data['nom_fr'],
                'prenom_fr' => $data['prenom_fr'],
                'nom_ar' => $data['nom_ar'] ?? $user->nom_ar,
                'prenom_ar' => $data['prenom_ar'] ?? $user->prenom_ar,
                'date_naissance' => $data['date_naissance'] ?? $user->date_naissance,
                'lieu_naissance_fr' => $data['lieu_naissance_fr'] ?? $user->lieu_naissance_fr,
                'lieu_naissance_ar' => $data['lieu_naissance_ar'] ?? $user->lieu_naissance_ar,
                'nationalite_fr' => $data['nationalite_fr'],
                'nationalite_ar' => $data['nationalite_ar'],
                'genre_fr' => $data['genre_fr'],
                'genre_ar' => $data['genre_ar'],
                'adresse_fr' => $data['adresse_fr'] ?? $user->adresse_fr,
                'adresse_ar' => $data['adresse_ar'] ?? $user->adresse_ar,
                'gouvernorat_fr' => $data['gouvernorat_fr'] ?? $user->gouvernorat_fr,
                'gouvernorat_ar' => $data['gouvernorat_ar'] ?? $user->gouvernorat_ar,
                'delegation_fr' => $data['delegation_fr'] ?? $user->delegation_fr,
                'delegation_ar' => $data['delegation_ar'] ?? $user->delegation_ar,
                'telephone_1' => $data['telephone_1'] ?? $user->telephone_1,
                'telephone_2' => $data['telephone_2'] ?? $user->telephone_2,
                'observation_fr' => $data['observation_fr'] ?? $user->observation_fr,
                'observation_ar' => $data['observation_ar'] ?? $user->observation_ar,
                'photo' => $photo,
                'email' => $data['email'],
                'type_user_fr' => $data['type_user_fr'],
                'type_user_ar' => $data['type_user_ar'],
            ];
            if (!empty($data['password'])) {
                $updateUserData['password'] = Hash::make($data['password']);
            }
            $user->update($updateUserData);
            // Manually update roles in model_has_roles
            DB::table('model_has_roles')->where('model_id', $user->id)->where('model_type', 'App\Models\User')->delete();
            foreach ($data['roles'] as $roleId) {
                $roleData = is_array($roleId) ? $roleId : ['id' => $roleId];
                DB::table('model_has_roles')->insert([
                    'role_id' => $roleData['id'],
                    'model_type' => 'App\Models\User',
                    'model_id' => $user->id,
                    'centre_id' => null,
                    'date_debut' => Carbon::now(),
                    'date_fin' => null,
                    'est_officiel' => $roleData['est_officiel'] ?? 1,
                    'statut' => 'Actif',
                    'description' => null,
                    'observation' => null,
                    'assigned_by' => auth()->id(),
                    'desactivated_by' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            // Update personnel DC record
            $personnelDC->update([
                'matricule' => $data['matricule'],
                'cin' => $data['cin'],
                'date_cin' => $data['date_cin'] ?? $personnelDC->date_cin,
                'lieu_cin_fr' => $data['lieu_cin_fr'] ?? $personnelDC->lieu_cin_fr,
                'lieu_cin_ar' => $data['lieu_cin_ar'] ?? $personnelDC->lieu_cin_ar,
                'etat_civil_fr' => $data['etat_civil_fr'] ?? $personnelDC->etat_civil_fr,
                'etat_civil_ar' => $data['etat_civil_ar'] ?? $personnelDC->etat_civil_ar,
                'qualite_fr' => $data['qualite_fr'],
                'qualite_ar' => $data['qualite_ar'],
                'date_recrutement' => $data['date_recrutement'] ?? $personnelDC->date_recrutement,
                'etablissement_origine_fr' => $data['etablissement_origine_fr'] ?? $personnelDC->etablissement_origine_fr,
                'etablissement_origine_ar' => $data['etablissement_origine_ar'] ?? $personnelDC->etablissement_origine_ar,
                'mission_fr' => $data['mission_fr'] ?? $personnelDC->mission_fr,
                'mission_ar' => $data['mission_ar'] ?? $personnelDC->mission_ar,
                'date_fin_service' => $data['date_fin_service'] ?? $personnelDC->date_fin_service,
                'cause_inactivite_fr' => $data['cause_inactivite_fr'] ?? $personnelDC->cause_inactivite_fr,
                'cause_inactivite_ar' => $data['cause_inactivite_ar'] ?? $personnelDC->cause_inactivite_ar,
                'observation_fr' => $data['observation_fr'] ?? $personnelDC->observation_fr,
                'observation_ar' => $data['observation_ar'] ?? $personnelDC->observation_ar,
            ]);
            return response()->json([
                'success' => true,
                'data' => $personnelDC->load(['user.roles' => function ($query) {
                    $query->select('roles.id', 'roles.name', 'roles.name_ar');
                }]),
                'message' => 'Personnel DC mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in update:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkMatricule(Request $request)
    {
        Log::info('checkMatricule appelé pour matricule:', ['matricule' => $request->matricule]);
        try {
            $validator = Validator::make($request->all(), [
                'matricule' => 'required|string|max:50|unique:personnels_direction_centrale,matricule',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'exists' => true,
                    'message' => 'Le matricule existe déjà.',
                ], 422);
            }
            return response()->json([
                'success' => true,
                'exists' => false,
                'message' => 'Le matricule est disponible.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in checkMatricule:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du matricule',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function checkCin(Request $request)
    {
        Log::info('checkCin appelé pour CIN:', ['cin' => $request->cin]);
        try {
            $validator = Validator::make($request->all(), [
                'cin' => 'required|string|size:8|unique:personnels_direction_centrale,cin',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'exists' => true,
                    'message' => 'Le CIN existe déjà.',
                ], 422);
            }
            return response()->json([
                'success' => true,
                'exists' => false,
                'message' => 'Le CIN est disponible.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in checkCin:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du CIN',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function checkEmail(Request $request)
    {
        Log::info('checkEmail appelé pour email:', [
            'email' => $request->email,
            'user_id' => $request->user_id
        ]);
        try {
            $rules = [
                'email' => [
                    'required',
                    'email',
                    'max:255',
                ],
            ];
            // Si un user_id est fourni (mode édition), ignorer cet utilisateur dans la règle d'unicité
            if ($request->has('user_id')) {
                $rules['email'][] = Rule::unique('users', 'email')->ignore($request->user_id);
            } else {
                $rules['email'][] = 'unique:users,email';
            }
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'exists' => true,
                    'message' => 'L\'email existe déjà.',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return response()->json([
                'success' => true,
                'exists' => false,
                'message' => 'L\'email est disponible.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in checkEmail:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification de l\'email',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy(Request $request, $id)
    {
        Log::info('destroy appelé pour personnel ID:', ['id' => $id]);
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
            ]);
            if ($validator->fails()) {
                Log::error('Validation failed in destroy:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Mot de passe requis pour confirmation',
                    'errors' => $validator->errors(),
                ], 422);
            }
            if (!Hash::check($request->password, auth()->user()->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mot de passe incorrect',
                ], 403);
            }
            $personnel = PersonnelDC::findOrFail($id);
            $user = $personnel->user;
            if ($user->photo) {
                Storage::disk('public')->delete(str_replace(Storage::url(''), '', $user->photo));
            }
            $personnel->delete();
            $user->delete();
            Log::info('Personnel ATFP supprimé avec succès', ['id' => $id]);
            return response()->json([
                'success' => true,
                'message' => 'Personnel ATFP supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in destroy:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bulk(Request $request)
    {
        Log::info('bulk appelé avec action:', ['action' => $request->input('action'), 'user_id' => auth()->id()]);
        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:import,export,import-line',
            'file' => 'required_if:action,import|file|mimes:xlsx,xls|max:2048',
            'lineData' => 'required_if:action,import-line|array',
            'lineData.nom_fr' => 'nullable|string|max:255',
            'lineData.prenom_fr' => 'nullable|string|max:255',
            'lineData.nom_ar' => 'nullable|string|max:255',
            'lineData.prenom_ar' => 'nullable|string|max:255',
            'lineData.cin' => 'nullable|string|size:8|regex:/^[A-Za-z0-9]{8}$/|unique:personnels_direction_centrale,cin',
            'lineData.matricule' => 'nullable|string|max:20|unique:personnels_direction_centrale,matricule',
            'lineData.date_cin' => [
                'nullable',
                'date_format:Y-m-d',
                'before:today',
            ],
            'lineData.lieu_cin_fr' => 'nullable|string|max:255',
            'lineData.lieu_cin_ar' => 'nullable|string|max:255',
            'lineData.date_naissance' => 'nullable|date_format:Y-m-d|before:today',
            'lineData.lieu_naissance_fr' => 'nullable|string|max:255',
            'lineData.lieu_naissance_ar' => 'nullable|string|max:255',
            'lineData.nationalite_fr' => 'nullable|string|max:255',
            'lineData.nationalite_ar' => 'nullable|string|max:255',
            'lineData.date_recrutement' => [
                'nullable',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'lineData.genre_fr' => 'nullable|in:Homme,Femme,Autre',
            'lineData.adresse_fr' => 'nullable|string|max:16777215',
            'lineData.adresse_ar' => 'nullable|string|max:16777215',
            'lineData.gouvernorat_fr' => 'nullable|string|max:255',
            'lineData.delegation_fr' => 'nullable|string|max:255',
            'lineData.delegation_ar' => 'nullable|string|max:255',
            'lineData.telephone_1' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'lineData.telephone_2' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'lineData.etat_civil_fr' => 'nullable|string|max:255',
            'lineData.email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'lineData.roles' => 'required|array|min:1',
            'lineData.roles.*' => 'exists:roles,name,guard_name,web',
        ], [
            'action.required' => 'L\'action est obligatoire.',
            'action.in' => 'L\'action doit être import, export ou import-line.',
            'file.required_if' => 'Un fichier est requis pour l\'importation.',
            'file.mimes' => 'Le fichier doit être de type xlsx ou xls.',
            'file.max' => 'Le fichier ne doit pas dépasser 2 Mo.',
            'lineData.required_if' => 'Les données de ligne sont requises pour l\'importation de ligne.',
            'lineData.email.required' => 'L\'email est requis.',
            'lineData.email.email' => 'L\'email est invalide.',
            'lineData.email.unique' => 'Cet email est déjà utilisé.',
            'lineData.roles.required' => 'Au moins un rôle doit être sélectionné.',
            'lineData.cin.unique' => 'Ce CIN est déjà utilisé.',
            'lineData.cin.size' => 'Le CIN doit contenir exactement 8 caractères alphanumériques.',
            'lineData.matricule.unique' => 'Ce matricule est déjà utilisé.',
            'lineData.date_cin.date_format' => 'La date CIN doit être au format AAAA-MM-JJ et valide.',
            'lineData.date_cin.before' => 'La date CIN doit être antérieure à aujourd\'hui.',
            'lineData.date_naissance.date_format' => 'La date de naissance doit être au format AAAA-MM-JJ et valide.',
            'lineData.date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'lineData.date_recrutement.date_format' => 'La date de recrutement doit être au format AAAA-MM-JJ et valide.',
            'lineData.date_recrutement.before_or_equal' => 'La date de recrutement doit être antérieure ou égale à aujourd\'hui.',
            'lineData.telephone_1.regex' => 'Le numéro de téléphone 1 doit contenir entre 8 et 15 chiffres.',
            'lineData.telephone_2.regex' => 'Le numéro de téléphone 2 doit contenir entre 8 et 15 chiffres.',
        ]);
        if ($validator->fails()) {
            Log::warning('Erreurs de validation dans bulk:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors(),
            ], 422);
        }
        $action = $request->input('action');
        if ($action === 'import') {
            $file = $request->file('file');
            $importService = new PersonnelsAtfpImportService;
            try {
                $result = $importService->import($file);
                Log::info('Importation terminée:', [
                    'success_count' => $result['success_count'],
                    'existing_count' => $result['existing_count'],
                    'error_count' => $result['error_count'],
                    'error_lines' => $result['error_lines'],
                ]);
                return response()->json([
                    'success' => true,
                    'message' => empty($result['error_lines']) ? 'Importation terminée avec succès.' : 'Importation terminée avec des erreurs.',
                    'success_count' => $result['success_count'],
                    'existing_count' => $result['existing_count'],
                    'error_count' => $result['error_count'],
                    'error_lines' => $result['error_lines'],
                ], 200);
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'importation:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de l\'importation.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        } elseif ($action === 'import-line') {
            $lineData = $request->input('lineData');
            Log::info('Données reçues pour import-line:', ['lineData' => $lineData]);
            // Validation des relations entre les dates
            $lineValidator = Validator::make($lineData, [
                'date_cin' => [
                    'nullable',
                    'date_format:Y-m-d',
                    'before:today',
                    function ($attribute, $value, $fail) use ($lineData) {
                        if ($value && isset($lineData['date_naissance']) && $lineData['date_naissance'] && $value < $lineData['date_naissance']) {
                            $fail('La date CIN doit être postérieure ou égale à la date de naissance.');
                        }
                    },
                ],
                'date_recrutement' => [
                    'nullable',
                    'date_format:Y-m-d',
                    'before_or_equal:today',
                ],
            ], [
                'date_cin.date_format' => 'La date CIN doit être au format AAAA-MM-JJ et valide.',
                'date_cin.before' => 'La date CIN doit être antérieure à aujourd\'hui.',
                'date_recrutement.date_format' => 'La date de recrutement doit être au format AAAA-MM-JJ et valide.',
                'date_recrutement.before_or_equal' => 'La date de recrutement doit être antérieure ou égale à aujourd\'hui.',
            ]);
            if ($lineValidator->fails()) {
                Log::warning('Erreurs de validation des dates dans import-line:', $lineValidator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation dans les données de la ligne.',
                    'errors' => $lineValidator->errors(),
                ], 422);
            }
            $importService = new PersonnelsAtfpImportService;
            try {
                $result = $importService->importRow($lineData);
                Log::info('Importation d\'une ligne terminée:', ['result' => $result instanceof \App\Models\User ? $result->toArray() : $result]);
                return response()->json([
                    'success' => true,
                    'message' => 'Ligne importée avec succès.',
                    'data' => $result instanceof \App\Models\User ? $result->toArray() : $result,
                ], 200);
            } catch (ValidationException $e) {
                Log::error('Erreur de validation lors de l\'importation de la ligne:', [
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                    'lineData' => $lineData,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation échouée.',
                    'errors' => $e->errors(),
                ], 422);
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'importation de la ligne:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'lineData' => $lineData,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'importation de la ligne : ' . $e->getMessage(),
                ], 500);
            }
        } elseif ($action === 'export') {
            Log::info('Exportation des personnels initiée');
            try {
                return Excel::download(new PersonnelAtfpExport, 'personnels_atfp_export.xlsx');
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'exportation:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'exportation des données.',
                ], 500);
            }
        }
        // Cas par défaut
        Log::warning('Action non reconnue dans bulk:', ['action' => $action]);
        return response()->json([
            'success' => false,
            'message' => 'Action non reconnue.',
        ], 400);
    }
    public function exportXLS()
    {
        Log::info('exportXLS appelé pour personnels ATFP');
        try {
            // Récupérer toutes les données de la table personnels_direction_centrale avec leurs utilisateurs
            $personnels = PersonnelDC::with('user.roles')->get();
            // Créer un nouveau classeur Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            // Définir les en-têtes des colonnes
            $headers = [
                'ID', 'Nom Fr', 'Prénom Fr', 'Nom Ar', 'Prénom Ar', 'Matricule', 'CIN', 'Date CIN',
                'Lieu CIN Fr', 'Lieu CIN Ar', 'Date Naissance', 'Lieu Naissance Fr', 'Lieu Naissance Ar',
                'Nationalité Fr', 'Nationalité Ar', 'Date Recrutement', 'Genre Fr',
                'Genre Ar', 'Statut', 'Adresse Fr',
                'Adresse Ar', 'Gouvernorat Fr', 'Gouvernorat Ar', 'Délégation Fr', 'Délégation Ar',
                'Téléphone 1', 'Téléphone 2', 'État Civil Fr', 'État Civil Ar', 'Qualité Fr',
                'Qualité Ar', 'Établissement Origine Fr', 'Établissement Origine Ar', 'Mission Fr',
                'Mission Ar', 'Observation Fr', 'Observation Ar', 'Email', 'Rôles'
            ];
            // Ajouter les en-têtes à la première ligne
            $sheet->fromArray($headers, null, 'A1');
            // Remplir les données
            $row = 2; // Commencer à la ligne 2 pour laisser la première ligne aux en-têtes
            foreach ($personnels as $personnel) {
                $roles = $personnel->user->roles->pluck('name')->implode(', '); // Concaténer les noms des rôles
                $sheet->fromArray([
                    $personnel->id,
                    $personnel->user->nom_fr ?? '-',
                    $personnel->user->prenom_fr ?? '-',
                    $personnel->user->nom_ar ?? '-',
                    $personnel->user->prenom_ar ?? '-',
                    $personnel->matricule ?? '-',
                    $personnel->cin ?? '-',
                    $personnel->date_cin ? $personnel->date_cin->format('Y-m-d') : '-',
                    $personnel->lieu_cin_fr ?? '-',
                    $personnel->lieu_cin_ar ?? '-',
                    $personnel->user->date_naissance ? $personnel->user->date_naissance->format('Y-m-d') : '-',
                    $personnel->user->lieu_naissance_fr ?? '-',
                    $personnel->user->lieu_naissance_ar ?? '-',
                    $personnel->user->nationalite_fr ?? '-',
                    $personnel->user->nationalite_ar ?? '-',
                    $personnel->date_recrutement ? $personnel->date_recrutement->format('Y-m-d') : '-',
                    $personnel->user->genre_fr ?? '-',
                    $personnel->user->genre_ar ?? '-',
                    $personnel->user->statut ?? '-',
                    $personnel->user->adresse_fr ?? '-',
                    $personnel->user->adresse_ar ?? '-',
                    $personnel->user->gouvernorat_fr ?? '-',
                    $personnel->user->gouvernorat_ar ?? '-',
                    $personnel->user->delegation_fr ?? '-',
                    $personnel->user->delegation_ar ?? '-',
                    $personnel->user->telephone_1 ?? '-',
                    $personnel->user->telephone_2 ?? '-',
                    $personnel->etat_civil_fr ?? '-',
                    $personnel->etat_civil_ar ?? '-',
                    $personnel->qualite_fr ?? '-',
                    $personnel->qualite_ar ?? '-',
                    $personnel->etablissement_origine_fr ?? '-',
                    $personnel->etablissement_origine_ar ?? '-',
                    $personnel->mission_fr ?? '-',
                    $personnel->mission_ar ?? '-',
                    $personnel->observation_fr ?? '-',
                    $personnel->observation_ar ?? '-',
                    $personnel->user->email ?? '-',
                    $roles ?: '-'
                ], null, 'A' . $row);
                $row++;
            }
            // Ajuster automatiquement la largeur des colonnes
            foreach (range('A', 'AM') as $column) { // Ajusté pour le nombre de colonnes réduit
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            // Créer le fichier Excel
            $writer = new Xlsx($spreadsheet);
            $filename = 'personnels_atfp_' . now()->format('Ymd_His') . '.xlsx';
            // Enregistrer le fichier dans un flux temporaire
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();
            // Retourner la réponse avec le fichier Excel
            return response($content, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'exportation XLS :', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'exportation des utilisateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function downloadTemplate()
    {
        Log::info('downloadTemplate appelé pour personnels ATFP');
        try {
            // Vider les caches pour garantir une récupération en temps réel
            Cache::forget('roles_for_dropdown');
            Cache::forget('gouvernorats_for_dropdown');
            // Créer un nouveau classeur Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Modèle Importation');
            // Définir les en-têtes des colonnes
            $headers = [
                'Nom Fr', 'Prénom Fr', 'Nom Ar', 'Prénom Ar', 'Matricule', 'CIN', 'Date CIN',
                'Lieu CIN Fr', 'Lieu CIN Ar', 'Date Naissance', 'Lieu Naissance Fr', 'Lieu Naissance Ar',
                'Nationalité Fr', 'Nationalité Ar', 'Date Recrutement', 'Genre Fr',
                'Adresse Fr', 'Adresse Ar', 'Gouvernorat Fr', 'Délégation Fr', 'Délégation Ar',
                'Téléphone 1', 'Téléphone 2', 'État Civil Fr', 'Qualité Fr', 'Établissement Origine Fr',
                'Mission Fr', 'Email', 'Rôles'
            ];
            // Ajouter les en-têtes à la première ligne
            $sheet->fromArray($headers, null, 'A1');
            // Récupérer tous les rôles avec is_centre_role = 0 et statut = 'actif'
            $roles = Role::withoutGlobalScopes()
                ->where('guard_name', 'web')
                ->where('is_centre_role', 0)
                ->where('statut', 'Actif')
                ->where('name', '!=', 'SuperAdmin')
                ->pluck('name')
                ->toArray();
            Log::info('Rôles récupérés pour la liste déroulante:', ['roles' => $roles, 'count' => count($roles)]);
            // Vérifier si des rôles ont été récupérés
            if (empty($roles)) {
                Log::warning('Aucun rôle récupéré pour la liste déroulante. Utilisation d\'une liste vide.');
                $roles = ['Aucun rôle disponible'];
            }
            // Créer une nouvelle feuille pour stocker la liste des rôles
            $roleSheet = $spreadsheet->createSheet();
            $roleSheet->setTitle('ListeRôles');
            foreach ($roles as $index => $role) {
                $roleSheet->setCellValue('A' . ($index + 1), $role);
            }
            $roleSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
            // Ajouter une validation de liste déroulante pour la colonne Rôles (colonne AC)
            $validationRoles = $sheet->getCell('AC2')->getDataValidation();
            $validationRoles->setType(DataValidation::TYPE_LIST);
            $validationRoles->setErrorStyle(DataValidation::STYLE_STOP);
            $validationRoles->setAllowBlank(true);
            $validationRoles->setShowDropDown(true);
            $validationRoles->setFormula1('ListeRôles!$A$1:$A$' . count($roles));
            $validationRoles->setErrorTitle('Rôle invalide');
            $validationRoles->setError('Veuillez sélectionner un rôle dans la liste déroulante.');
            // Appliquer la validation à la plage AC2:AC1000
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell('AC' . $row)->setDataValidation(clone $validationRoles);
            }
            // Ajouter une liste déroulante pour la colonne Genre Fr (colonne O)
            $genres = ['Homme', 'Femme'];
            $genreSheet = $spreadsheet->createSheet();
            $genreSheet->setTitle('ListeGenres');
            foreach ($genres as $index => $genre) {
                $genreSheet->setCellValue('A' . ($index + 1), $genre);
            }
            $genreSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
            // Validation pour la colonne Genre Fr (O)
            $validationGenres = $sheet->getCell('O2')->getDataValidation();
            $validationGenres->setType(DataValidation::TYPE_LIST);
            $validationGenres->setErrorStyle(DataValidation::STYLE_STOP);
            $validationGenres->setAllowBlank(true);
            $validationGenres->setShowDropDown(true);
            $validationGenres->setFormula1('ListeGenres!$A$1:$A$' . count($genres));
            $validationGenres->setErrorTitle('Genre invalide');
            $validationGenres->setError('Veuillez sélectionner un genre dans la liste déroulante.');
            // Appliquer la validation à la plage O2:O1000
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell('O' . $row)->setDataValidation(clone $validationGenres);
            }
            // Ajouter une liste déroulante pour la colonne Qualité Fr (colonne Y)
            $qualites = ['Personnel (ATFP)', 'Personnel (Externe)'];
            $qualiteSheet = $spreadsheet->createSheet();
            $qualiteSheet->setTitle('ListeQualites');
            foreach ($qualites as $index => $qualite) {
                $qualiteSheet->setCellValue('A' . ($index + 1), $qualite);
            }
            $qualiteSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
            // Validation pour la colonne Qualité Fr (Y)
            $validationQualites = $sheet->getCell('Y2')->getDataValidation();
            $validationQualites->setType(DataValidation::TYPE_LIST);
            $validationQualites->setErrorStyle(DataValidation::STYLE_STOP);
            $validationQualites->setAllowBlank(true);
            $validationQualites->setShowDropDown(true);
            $validationQualites->setFormula1('ListeQualites!$A$1:$A$' . count($qualites));
            $validationQualites->setErrorTitle('Qualité invalide');
            $validationQualites->setError('Veuillez sélectionner une qualité dans la liste déroulante.');
            // Appliquer la validation à la plage Y2:Y1000
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell('Y' . $row)->setDataValidation(clone $validationQualites);
            }
            // Récupérer les gouvernorats depuis la table listes
            $gouvernoratsData = Liste::where('nom_fr', 'Gouvernorats')->first();
            $gouvernorats = [];
            if ($gouvernoratsData && !empty($gouvernoratsData->options)) {
                // Vérifier si options est une chaîne JSON ou un tableau
                $options = is_string($gouvernoratsData->options) ? json_decode($gouvernoratsData->options, true) : $gouvernoratsData->options;
                $gouvernorats = array_column($options, 'nom_fr');
            }
            Log::info('Gouvernorats récupérés pour la liste déroulante:', ['gouvernorats' => $gouvernorats, 'count' => count($gouvernorats)]);
            // Vérifier si des gouvernorats ont été récupérés
            if (empty($gouvernorats)) {
                Log::warning('Aucun gouvernorat récupéré pour la liste déroulante. Utilisation d\'une liste vide.');
                $gouvernorats = ['Aucun gouvernorat disponible'];
            }
            // Créer une nouvelle feuille pour stocker la liste des gouvernorats
            $gouvernoratSheet = $spreadsheet->createSheet();
            $gouvernoratSheet->setTitle('ListeGouvernorats');
            foreach ($gouvernorats as $index => $gouvernorat) {
                $gouvernoratSheet->setCellValue('A' . ($index + 1), $gouvernorat);
            }
            $gouvernoratSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
            // Validation pour la colonne Gouvernorat Fr (U)
            $validationGouvernorats = $sheet->getCell('U2')->getDataValidation();
            $validationGouvernorats->setType(DataValidation::TYPE_LIST);
            $validationGouvernorats->setErrorStyle(DataValidation::STYLE_STOP);
            $validationGouvernorats->setAllowBlank(true);
            $validationGouvernorats->setShowDropDown(true);
            $validationGouvernorats->setFormula1('ListeGouvernorats!$A$1:$A$' . count($gouvernorats));
            $validationGouvernorats->setErrorTitle('Gouvernorat invalide');
            $validationGouvernorats->setError('Veuillez sélectionner un gouvernorat dans la liste déroulante.');
            // Appliquer la validation à la plage U2:U1000
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell('U' . $row)->setDataValidation(clone $validationGouvernorats);
            }
            // Ajouter une liste déroulante pour la colonne État Civil Fr (colonne AA)
            $etatsCivils = ['Célibataire', 'Marié', 'Divorcé', 'Veuf'];
            $etatCivilSheet = $spreadsheet->createSheet();
            $etatCivilSheet->setTitle('ListeEtatsCivils');
            foreach ($etatsCivils as $index => $etatCivil) {
                $etatCivilSheet->setCellValue('A' . ($index + 1), $etatCivil);
            }
            $etatCivilSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
            // Validation pour la colonne État Civil Fr (AA)
            $validationEtatsCivils = $sheet->getCell('AA2')->getDataValidation();
            $validationEtatsCivils->setType(DataValidation::TYPE_LIST);
            $validationEtatsCivils->setErrorStyle(DataValidation::STYLE_STOP);
            $validationEtatsCivils->setAllowBlank(true);
            $validationEtatsCivils->setShowDropDown(true);
            $validationEtatsCivils->setFormula1('ListeEtatsCivils!$A$1:$A$' . count($etatsCivils));
            $validationEtatsCivils->setErrorTitle('État civil invalide');
            $validationEtatsCivils->setError('Veuillez sélectionner un état civil dans la liste déroulante.');
            // Appliquer la validation à la plage AA2:AA1000
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell('AA' . $row)->setDataValidation(clone $validationEtatsCivils);
            }
            // Ajuster automatiquement la largeur des colonnes
            foreach (range('A', 'AC') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            // Créer le fichier Excel
            $writer = new Xlsx($spreadsheet);
            $filename = 'PersonnelATFP.xlsx';
            // Enregistrer le fichier dans un flux temporaire
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();
            // Retourner la réponse avec le fichier Excel
            return response($content, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in downloadTemplate:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement du modèle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function lists()
    {
        Log::info('lists appelé pour personnels ATFP');
        try {
            $lists = Liste::whereIn('nom_fr', ['Gouvernorats'])
                ->where('statut', 'Actif')
                ->get()
                ->mapWithKeys(function ($list) {
                    return [$list->nom_fr => $list->options];
                });
            Log::info('Réponse complète des listes:', ['lists' => $lists->toArray()]);
            return response()->json([
                'success' => true,
                'data' => $lists,
                'message' => 'Listes récupérées avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in lists:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des listes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
