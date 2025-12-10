<?php
namespace App\Http\Controllers\brillants\ATFP\Utilisateurs\PersonnelsCentres;

use App\Http\Controllers\ATFP\Utilisateurs\PersonnelsCentres\Excel;
use App\Http\Controllers\ATFP\Utilisateurs\PersonnelsCentres\PersonnelAtfpExport;
use App\Http\Controllers\ATFP\Utilisateurs\PersonnelsCentres\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\ATFP\Utilisateurs\PersonnelsCentres\GradesPersonnel;
use App\Models\Centre\Centre;
use App\Models\Liste;
use App\Models\User;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use App\Models\Utilisateurs\UserCentre;
use App\Services\Import\PersonnelsCentresImportService;
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

class PersonnelCentreController extends Controller
{
    public function indexWithOptions(Request $request)
    {
        Log::info('indexWithOptions appelé pour personnels Centres', [
            'per_page' => $request->input('per_page', 25),
            'user' => auth()->user() ? auth()->user()->id : 'non authentifié',
        ]);
        try {
            $perPage = $request->input('per_page', 25);
            // Récupérer les utilisateurs avec leurs rôles, centres et personnelsCentres associés
            $users = User::with([
                'roles' => function ($query) {
                    $query->select('roles.id', 'roles.name', 'roles.name_ar');
                },
                'centres' => function ($query) {
                    $query->select('centres.id', 'centres.nom_fr', 'centres.nom_ar')
                        ->withPivot([
                            'centre_statut_fr',
                            'centre_statut_ar',
                            'observation_fr',
                            'observation_ar',
                            'date_debut',
                            'date_fin',
                            'statut',
                        ]);
                },
                'userCentres.personnelCentre',
                'userCentres.centre'
            ])
                ->leftJoin('users as added_by_users', 'users.ajouter_par', '=', 'added_by_users.id')
                ->select(
                    'users.*',
                    'added_by_users.prenom_fr as added_by_prenom_fr',
                    'added_by_users.nom_fr as added_by_nom_fr'
                )
                ->whereNull('users.deleted_at')
                ->orderBy('users.id', 'asc')
                ->paginate($perPage);

            // Récupérer les listes de gouvernorats et niveaux d'études
            $lists = Liste::whereIn('nom_fr', ['Gouvernorats', 'Niveaux Etudes PersonnelsDirectionCentrale'])
                ->where('statut', 'Actif')
                ->get()
                ->pluck('options', 'nom_fr');

            // Initialiser la liste des centres
            $centres = [];
            $user = auth()->user();
            if ($user) {
                // Récupérer is_centre_role depuis la table model_has_roles
                $modelHasRole = DB::table('model_has_roles')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('model_has_roles.model_type', 'App\\Models\\User')
                    ->where('model_has_roles.model_id', $user->id)
                    ->select('roles.is_centre_role')
                    ->first();

                $isCentreRole = $modelHasRole ? $modelHasRole->is_centre_role : 0;

                // Récupérer centre_id depuis users_centres
                $centre = DB::table('users_centres')
                    ->where('user_id', $user->id)
                    ->select('centre_id')
                    ->first();

                $centreId = $centre ? $centre->centre_id : null;

                // Charger les centres si isCentreRole est 0
                if (!$isCentreRole) {
                    $centres = Centre::select('id', 'nom_fr', 'nom_ar')->get();
                }
            } else {
                // Si l'utilisateur n'est pas authentifié, charger tous les centres par défaut
                $centres = Centre::select('id', 'nom_fr', 'nom_ar')->get();
            }

            Log::info('Données renvoyées', [
                'users_count' => $users->count(),
                'lists' => $lists->toArray(),
                'centres_count' => count($centres),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'personnels' => $users,
                    'lists' => $lists,
                    'centres' => $centres,
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

    public function getCentres()
    {
        try {
            $centres = Centre::select('id', 'nom_fr', 'nom_ar')->get();
            return response()->json([
                'success' => true,
                'data' => $centres,
                'message' => 'Centres récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des centres',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getNiveauxEtude()
    {
        try {
            // Récupérer la liste des niveaux d'étude
            $liste = Liste::where('nom_fr', 'Niveaux Etudes Personnels')
                ->where('statut', 'Actif')
                ->first();

            // Vérifier si la liste existe
            if (!$liste) {
                Log::warning('Liste niveau etude introuvable.', [
                    'nom_fr' => 'Niveaux Etudes Personnels'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Liste niveau etude non disponible',
                    'errors' => ['niveau_etude_fr' => ['Liste niveau etude non disponible']]
                ], 422);
            }

            // Vérifier si la liste a des options valides
            if (empty($liste->options)) {
                Log::warning('Liste niveau etude vide.', [
                    'nom_fr' => 'Niveaux Etudes Personnels'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Liste niveau etude non disponible',
                    'errors' => ['niveau_etude_fr' => ['Aucune option disponible pour les niveaux d\'étude']]
                ], 422);
            }

            // Retourner les options de la liste
            return response()->json([
                'success' => true,
                'data' => $liste->options,
                'message' => 'Niveaux d\'étude récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des niveaux d\'étude:', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des niveaux d\'étude',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Store request data:', $request->all());

        // Mappings pour les traductions
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

        $causeInactiviteMap = [
            'Mise à la retraite' => 'الإحالة على التقاعد',
            'Dispense / Exemption' => 'الإعفاء',
            'Révocation' => 'العزل',
            'Perte de la nationalité tunisienne' => 'فقدان الجنسية التونسية',
            'Perte des droits civils' => 'فقدان الحقوق المدنية',
            'Non-retour de l’agent après une période de détachement' => 'عدم رجوع العون إثر فترة إلحاق',
            'Non-prise de fonction' => 'عدم مباشرة',
            'Non-retour de l’agent après avoir accompli le service militaire' => 'عدم رجوع العون بعد قيامه بالخدمة الوطنية',
            'Non-retour de l’agent à son poste après la fin d’un congé pour création d’entreprise, après un avertissement' => 'عدم رجوع العون إلى عمله إثر انتهاء عطلة لبعث مؤسسة، وبعد التنبيه عليه',
        ];

        $qualiteMap = [
            'Personnel (ATFP)' => 'عون الوكالة',
            'Personnel (Externe)' => 'عون من خارج الوكالة',
        ];

        $centreStatutMap = [
            'Centre officiel' => 'المركز الأصلي',
            'Centre secondaire' => 'المركز الثانوي',
        ];

        $forConsMap = [
            'Formateur' => 'مكون',
            'Conseiller d\'apprentissage' => 'مستشار تدريب',
        ];

        $etatPersonnelMap = [
            'Titulaire' => 'عون قار',
            'Contractuel' => 'عون متعاقد',
            'Détaché' => 'عون ملحق',
            'Autre' => 'حالة أخرى',
        ];

        $statutMap = [
            'Actif' => 'نشط',
            'Inactif' => 'غير نشط',
        ];

        $filiereArMap = [
            'Agent Administratif' => 'إداري',
            'Agent de formation' => 'عون تكوين',
            'Corps para-pédagogique' => 'إطار شبه بيداغوجي',
            'Agent Technique' => 'فني',
            'Ouvrier' => 'عامل(ة)',
        ];

        // Règles de validation
        $validator = Validator::make($request->all(), [
            // Champs de la table users
            'nom_fr' => 'required|string|max:255',
            'prenom_fr' => 'required|string|max:255',
            'nom_ar' => 'required|string|max:255',
            'prenom_ar' => 'required|string|max:255',
            'cin' => 'required|string|size:8|unique:users,cin',
            'matricule' => 'required|string|max:20|unique:users,matricule',
            'date_cin' => 'nullable|date|before:today',
            'lieu_cin_fr' => 'nullable|string|max:255',
            'lieu_cin_ar' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date|before:today',
            'lieu_naissance_fr' => 'nullable|string|max:255',
            'lieu_naissance_ar' => 'nullable|string|max:255',
            'nationalite_fr' => 'nullable|string|max:255',
            'nationalite_ar' => 'nullable|string|max:255',
            'date_recrutement' => 'nullable|date|before_or_equal:today',
            'date_fin_service' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && $request->input('date_recrutement') && $value < $request->input('date_recrutement')) {
                        $fail('La date de fin de service doit être postérieure ou égale à la date de recrutement.');
                    }
                },
            ],
            'genre_fr' => 'required|in:Homme,Femme,Autre',
            'genre_ar' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request, $genreMap) {
                    if ($request->input('genre_fr') && $value !== ($genreMap[$request->input('genre_fr')] ?? null)) {
                        $fail('Le genre en arabe doit correspondre au genre en français.');
                    }
                },
            ],
            'statut' => 'required|in:Actif,Inactif',
            'cause_inactivite_fr' => [
                'nullable',
                'string',
                'max:16777215',
                Rule::requiredIf($request->input('statut') === 'Inactif'),
            ],
            'cause_inactivite_ar' => [
                'nullable',
                'string',
                'max:16777215',
                Rule::requiredIf($request->input('statut') === 'Inactif'),
                function ($attribute, $value, $fail) use ($request, $causeInactiviteMap) {
                    if ($request->input('statut') === 'Inactif' && $request->input('cause_inactivite_fr') && isset($causeInactiviteMap[$request->input('cause_inactivite_fr')]) && $value !== $causeInactiviteMap[$request->input('cause_inactivite_fr')]) {
                        $fail("La cause d'inactivité en arabe doit correspondre à la cause d'inactivité en français.");
                    }
                },
            ],
            'adresse_fr' => 'nullable|string|max:16777215',
            'adresse_ar' => 'nullable|string|max:16777215',
            'gouvernorat_fr' => 'nullable|string|max:255',
            'gouvernorat_ar' => 'nullable|string|max:255',
            'delegation_fr' => 'nullable|string|max:255',
            'delegation_ar' => 'nullable|string|max:255',
            'telephone_1' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'telephone_2' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'etat_civil_fr' => 'nullable|string|max:255',
            'etat_civil_ar' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request, $etatCivilMap) {
                    if ($request->input('etat_civil_fr') && $value !== ($etatCivilMap[$request->input('etat_civil_fr')] ?? null)) {
                        $fail("L'état civil en arabe doit correspondre à l'état civil en français.");
                    }
                },
            ],
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
            'password' => 'nullable|string|min:8|confirmed',

            // Validation des rôles avec centre_id
            'roles' => 'required|array|min:1',
            'roles.*.id' => 'required|exists:roles,id,guard_name,web',
            'roles.*.centre_id' => 'required|exists:centres,id',

            // Champs de la table users_centres
            'centre_id' => 'required|exists:centres,id',
            'qualite_fr' => 'required|string|max:255',
            'qualite_ar' => 'required|string|max:255',
            'etablissement_origine_fr' => [
                'required_if:qualite_fr,Personnel (Externe)',
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('qualite_fr') === 'Personnel (Externe)' && empty($value)) {
                        $fail("L'établissement d'origine est requis pour le personnel externe.");
                    }
                },
            ],
            'etablissement_origine_ar' => [
                'required_if:qualite_fr,Personnel (Externe)',
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('qualite_fr') === 'Personnel (Externe)' && empty($value)) {
                        $fail('المؤسسة الأصلية مطلوبة للعاملين من خارج الوكالة.');
                    }
                },
            ],
            'centre_statut_fr' => 'required|string|max:255',
            'centre_statut_ar' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => [
                'required_if:statut_personnel,Inactif',
                'nullable',
                'date',
                'after_or_equal:date_debut',
            ],
            'statut_affectation' => 'required|string|in:Actif,Inactif',
            'observation_fr_affectation' => 'nullable|string|max:1000',
            'observation_ar_affectation' => 'nullable|string|max:1000',

            // Champs de la table personnels_centres
            'etat_personnel_fr' => 'required|string|max:255',
            'etat_personnel_ar' => 'required|string|max:255',
            'niveau_etude_fr' => 'nullable|string|max:255',
            'niveau_etude_ar' => 'nullable|string|max:255',
            'specialite_diplome_fr' => 'nullable|string|max:255',
            'specialite_diplome_ar' => 'nullable|string|max:255',
            'code_niveau' => 'required|string|max:10',
            'for_cons_fr' => 'nullable|string|max:255',
            'for_cons_ar' => 'nullable|string|max:255',
            'statut_personnel' => 'required|string|in:Actif,Inactif',
            'observation_fr_personnel' => 'nullable|string|max:1000',
            'observation_ar_personnel' => 'nullable|string|max:1000',

            // Champs de la table grades_personnels
            'filiere_fr' => 'required|string|max:255',
            'filiere_ar' => 'nullable|string|max:255',
        ], [
            'centre_id.required' => 'Le centre est requis.',
            'centre_id.exists' => 'Le centre sélectionné est invalide.',
            'qualite_fr.required' => 'La qualité est requise.',
            'qualite_ar.required' => 'La qualité en arabe est requise.',
            'centre_statut_fr.required' => 'Le statut du centre est requis.',
            'centre_statut_ar.required' => 'Le statut du centre en arabe est requis.',
            'date_debut.required' => 'La date de début est requise.',
            'date_fin.required_if' => 'La date de fin est requise pour le personnel inactif.',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'statut_affectation.required' => 'Le statut d\'affectation est requis.',
            'etat_personnel_fr.required' => 'L\'état du personnel est requis.',
            'etat_personnel_ar.required' => 'L\'état du personnel en arabe est requis.',
            'code_niveau.required' => 'Le code du niveau est requis.',
            'for_cons_fr.max' => 'Le champ formateur/conseiller ne doit pas dépasser 255 caractères.',
            'for_cons_ar.max' => 'Le champ formateur/conseiller en arabe ne doit pas dépasser 255 caractères.',
            'statut_personnel.required' => 'Le statut du personnel est requis.',
            'filiere_fr.required' => 'La filière est requise.',
            'etablissement_origine_fr.required_if' => "L'établissement d'origine est requis pour le personnel externe.",
            'etablissement_origine_ar.required_if' => 'المؤسسة الأصلية مطلوبة للعاملين من خارج الوكالة.',
            'roles.*.id.required' => 'L\'ID du rôle est requis.',
            'roles.*.id.exists' => 'Le rôle sélectionné est invalide.',
            'roles.*.centre_id.required' => 'Le centre est requis pour chaque rôle.',
            'roles.*.centre_id.exists' => 'Le centre sélectionné est invalide.',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed in store:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation échouée'
            ], 422);
        }

        // Démarrer une transaction pour assurer l'intégrité des données
        DB::beginTransaction();
        try {
            $data = $validator->validated();

            // Mapper les champs traduits
            $data['genre_ar'] = isset($data['genre_fr']) ? ($genreMap[$data['genre_fr']] ?? null) : null;
            $data['etat_civil_ar'] = isset($data['etat_civil_fr']) ? ($etatCivilMap[$data['etat_civil_fr']] ?? null) : null;
            $data['cause_inactivite_ar'] = isset($data['cause_inactivite_fr']) ? ($causeInactiviteMap[$data['cause_inactivite_fr']] ?? $data['cause_inactivite_ar']) : null;
            $data['qualite_ar'] = isset($data['qualite_fr']) ? ($qualiteMap[$data['qualite_fr']] ?? null) : null;
            $data['centre_statut_ar'] = isset($data['centre_statut_fr']) ? ($centreStatutMap[$data['centre_statut_fr']] ?? null) : null;
            $data['for_cons_ar'] = isset($data['for_cons_fr']) ? ($forConsMap[$data['for_cons_fr']] ?? null) : null;
            $data['etat_personnel_ar'] = isset($data['etat_personnel_fr']) ? ($etatPersonnelMap[$data['etat_personnel_fr']] ?? null) : null;
            $data['filiere_ar'] = isset($data['filiere_fr']) ? ($filiereArMap[$data['filiere_fr']] ?? null) : null;

            // Valeurs par défaut
            $data['nationalite_fr'] = $data['nationalite_fr'] ?? 'Tunisienne';
            $data['nationalite_ar'] = $data['nationalite_ar'] ?? 'تونسية';

            // Gérer les champs d'établissement d'origine
            if ($request->input('qualite_fr') !== 'Personnel (Externe)') {
                $data['etablissement_origine_fr'] = null;
                $data['etablissement_origine_ar'] = null;
            }

            // Générer un mot de passe si non fourni
            if (empty($data['password'])) {
                $data['password'] = ucfirst($data['prenom_fr'] ?? 'user') . '123***';
                $data['password_confirmation'] = $data['password'];
            }

            // Valider le gouvernorat si fourni
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

            // Extraire les IDs des rôles
            $roleIds = collect($data['roles'])->pluck('id')->toArray();
            $roleNames = Role::whereIn('id', $roleIds)->where('guard_name', 'web')->pluck('name')->toArray();

            if (count($roleIds) !== count($roleNames)) {
                Log::error('Certains rôles sont introuvables:', ['role_ids' => $roleIds]);
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création du personnel',
                    'errors' => ['roles' => ['Un ou plusieurs rôles sont introuvables.']]
                ], 422);
            }

            // Traiter la photo
            $photo = !empty($data['photo']) && str_starts_with($data['photo'], 'data:image/') ? $data['photo'] : null;

            // Créer l'utilisateur
            $user = User::create([
                'nom_fr' => $data['nom_fr'],
                'prenom_fr' => $data['prenom_fr'],
                'nom_ar' => $data['nom_ar'],
                'prenom_ar' => $data['prenom_ar'],
                'cin' => $data['cin'],
                'matricule' => $data['matricule'],
                'date_cin' => $data['date_cin'] ?? null,
                'lieu_cin_fr' => $data['lieu_cin_fr'] ?? null,
                'lieu_cin_ar' => $data['lieu_cin_ar'] ?? null,
                'date_naissance' => $data['date_naissance'] ?? null,
                'lieu_naissance_fr' => $data['lieu_naissance_fr'] ?? null,
                'lieu_naissance_ar' => $data['lieu_naissance_ar'] ?? null,
                'nationalite_fr' => $data['nationalite_fr'],
                'nationalite_ar' => $data['nationalite_ar'],
                'date_recrutement' => $data['date_recrutement'] ?? null,
                'date_fin_service' => $data['date_fin_service'] ?? null,
                'genre_fr' => $data['genre_fr'],
                'genre_ar' => $data['genre_ar'],
                'statut' => $data['statut'],
                'cause_inactivite_fr' => $data['cause_inactivite_fr'] ?? null,
                'cause_inactivite_ar' => $data['cause_inactivite_ar'] ?? null,
                'adresse_fr' => $data['adresse_fr'] ?? null,
                'adresse_ar' => $data['adresse_ar'] ?? null,
                'gouvernorat_fr' => $data['gouvernorat_fr'] ?? null,
                'gouvernorat_ar' => $data['gouvernorat_ar'] ?? null,
                'delegation_fr' => $data['delegation_fr'] ?? null,
                'delegation_ar' => $data['delegation_ar'] ?? null,
                'telephone_1' => $data['telephone_1'] ?? null,
                'telephone_2' => $data['telephone_2'] ?? null,
                'etat_civil_fr' => $data['etat_civil_fr'] ?? null,
                'etat_civil_ar' => $data['etat_civil_ar'] ?? null,
                'observation_fr' => $data['observation_fr'] ?? null,
                'observation_ar' => $data['observation_ar'] ?? null,
                'photo' => $photo,
                'ajouter_par' => auth()->id(),
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'qualite_fr' => $data['qualite_fr'],
                'qualite_ar' => $data['qualite_ar'],
                'etablissement_origine_fr' => $data['etablissement_origine_fr'] ?? null,
                'etablissement_origine_ar' => $data['etablissement_origine_ar'] ?? null,
            ]);

            // Synchroniser les rôles
            $user->syncRoles($roleNames);

            // Mettre à jour les entrées dans model_has_roles avec le centre_id
            foreach ($data['roles'] as $roleData) {
                DB::table('model_has_roles')
                    ->where('model_type', 'App\Models\User')
                    ->where('model_id', $user->id)
                    ->where('role_id', $roleData['id'])
                    ->update([
                        'centre_id' => $roleData['centre_id'],
                        'date_debut' => $data['date_debut'] ?? null,
                        'date_fin' => $data['date_fin'] ?? null,
                        'est_officiel' => 0, // Valeur par défaut
                        'statut' => 'Actif', // Valeur par défaut
                        'assigned_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            // Créer l'entrée dans users_centres
            $userCentre = UserCentre::create([
                'user_id' => $user->id,
                'centre_id' => $data['centre_id'],
                'centre_statut_fr' => $data['centre_statut_fr'],
                'centre_statut_ar' => $data['centre_statut_ar'],
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'] ?? null,
                'statut' => $data['statut_affectation'],
                'observation_fr' => $data['observation_fr_affectation'] ?? null,
                'observation_ar' => $data['observation_ar_affectation'] ?? null,
            ]);

            // Créer l'entrée dans personnels_centres
            $personnelCentre = PersonnelCentre::create([
                'user_centre_id' => $userCentre->id,
                'etat_personnel_fr' => $data['etat_personnel_fr'],
                'etat_personnel_ar' => $data['etat_personnel_ar'],
                'niveau_etude_fr' => $data['niveau_etude_fr'] ?? null,
                'niveau_etude_ar' => $data['niveau_etude_ar'] ?? null,
                'specialite_diplome_fr' => $data['specialite_diplome_fr'] ?? null,
                'specialite_diplome_ar' => $data['specialite_diplome_ar'] ?? null,
                'code_niveau' => $data['code_niveau'],
                'for_cons_fr' => $data['for_cons_fr'] ?? null,
                'for_cons_ar' => $data['for_cons_ar'] ?? null,
                'statut' => $data['statut_personnel'],
                'observation_fr' => $data['observation_fr_personnel'] ?? null,
                'observation_ar' => $data['observation_ar_personnel'] ?? null,
            ]);

            // Créer l'entrée dans grades_personnels
            if (isset($data['filiere_fr'])) {
                GradesPersonnel::create([
                    'personnel_id' => $personnelCentre->id,
                    'filiere_fr' => $data['filiere_fr'],
                    'filiere_ar' => $data['filiere_ar'] ?? null,
                    'date_debut' => now(), // Date de début par défaut
                    'statut' => 'Actif', // Statut par défaut
                ]);
            }

            // Valider la transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $user->load(['roles' => function ($query) {
                    $query->select('roles.id', 'roles.name', 'roles.name_ar');
                }, 'centres' => function ($query) {
                    $query->select('centres.id', 'centres.nom_fr', 'centres.nom_ar')
                        ->withPivot([
                            'centre_statut_fr',
                            'centre_statut_ar',
                            'observation_fr',
                            'observation_ar',
                            'date_debut',
                            'date_fin',
                            'statut',
                        ]);
                }, 'userCentres.personnelCentre', 'userCentres.centre']),
                'message' => 'Personnel ATFP créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            DB::rollBack();
            Log::error('Error in store:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource with options.
     */
    public function showWithOptions(User $user)
    {
        Log::info('showWithOptions appelé pour personnel ID:', ['id' => $user->id]);
        try {
            $lists = Liste::whereIn('nom_fr', ['Gouvernorats', 'Rôles'])
                ->where('statut', 'Actif')
                ->get()
                ->mapWithKeys(function ($list) {
                    return [$list->nom_fr => $list->nom_fr === 'Rôles' ? Role::select('id', 'name', 'name_ar')->get() : $list->options];
                });

            $user->load(['roles' => function ($query) {
                $query->select('roles.id', 'roles.name', 'roles.name_ar');
            }]);

            $userData = [
                'id' => $user->id,
                'nom_fr' => $user->nom_fr,
                'prenom_fr' => $user->prenom_fr,
                'nom_ar' => $user->nom_ar,
                'prenom_ar' => $user->prenom_ar,
                'cin' => $user->cin,
                'matricule' => $user->matricule,
                'date_cin' => $user->date_cin,
                'lieu_cin_fr' => $user->lieu_cin_fr,
                'lieu_cin_ar' => $user->lieu_cin_ar,
                'date_naissance' => $user->date_naissance,
                'lieu_naissance_fr' => $user->lieu_naissance_fr,
                'lieu_naissance_ar' => $user->lieu_naissance_ar,
                'nationalite_fr' => $user->nationalite_fr,
                'nationalite_ar' => $user->nationalite_ar,
                'genre_fr' => $user->genre_fr,
                'etat_civil_fr' => $user->etat_civil_fr,
                'etat_civil_ar' => $user->etat_civil_ar,
                'adresse_fr' => $user->adresse_fr,
                'adresse_ar' => $user->adresse_ar,
                'gouvernorat_fr' => $user->gouvernorat_fr,
                'gouvernorat_ar' => $user->gouvernorat_ar,
                'delegation_fr' => $user->delegation_fr,
                'delegation_ar' => $user->delegation_ar,
                'telephone_1' => $user->telephone_1,
                'telephone_2' => $user->telephone_2,
                'statut' => $user->statut,
                'observation_fr' => $user->observation_fr,
                'observation_ar' => $user->observation_ar,
                'cause_inactivite_fr' => $user->cause_inactivite_fr,
                'cause_inactivite_ar' => $user->cause_inactivite_ar,
                'date_recrutement' => $user->date_recrutement,
                'date_fin_service' => $user->date_fin_service,
                'photo' => $user->photo,
                'email' => $user->email,
                'ajouter_par' => $user->ajouter_par,
                'qualite_fr' => $user->qualite_fr,
                'qualite_ar' => $user->qualite_ar,
                'etablissement_origine_fr' => $user->etablissement_origine_fr,
                'etablissement_origine_ar' => $user->etablissement_origine_ar,
                'roles' => $user->roles->map(function ($role) {
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
                    'roles' => $user->roles->pluck('id')->toArray(),
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

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Enable query logging
            DB::enableQueryLog();
            $users = User::with([
                'roles' => function ($query) {
                    $query->select('roles.id', 'roles.name', 'roles.name_ar');
                },
                'addedBy' => function ($query) {
                    $query->select('id', 'prenom_fr', 'nom_fr');
                }
            ])
                ->whereNull('users.deleted_at')
                ->orderBy('users.id', 'asc')
                ->paginate(25);

            // Log the raw SQL query
            Log::info('PersonnelController::index - Raw SQL Query:', [
                'query' => DB::getQueryLog(),
            ]);

            // Log specific fields for debugging
            Log::info('PersonnelController::index - Fetched users:', [
                'total' => $users->total(),
                'data' => $users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'ajouter_par' => $user->ajouter_par,
                        'added_by' => $user->addedBy ? [
                            'id' => $user->addedBy->id,
                            'prenom_fr' => $user->addedBy->prenom_fr,
                            'nom_fr' => $user->addedBy->nom_fr
                        ] : null,
                    ];
                })->toArray(),
            ]);

            // Log the full response before serialization
            Log::info('PersonnelController::index - Raw response data:', [
                'personnels' => $users->toArray(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'personnels' => $users,
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
        } finally {
            // Disable query logging
            DB::disableQueryLog();
        }
    }

    public function update(Request $request, User $user)
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

        $causeInactiviteMap = [
            'Mise à la retraite' => 'الإحالة على التقاعد',
            'Dispense / Exemption' => 'الإعفاء',
            'Révocation' => 'العزل',
            'Perte de la nationalité tunisienne' => 'فقدان الجنسية التونسية',
            'Perte des droits civils' => 'فقدان الحقوق المدنية',
            'Non-retour de l’agent après une période de détachement' => 'عدم رجوع العون إثر فترة إلحاق',
            'Non-prise de fonction' => 'عدم مباشرة',
            'Non-retour de l’agent après avoir accompli le service militaire' => 'عدم رجوع العون بعد قيامه بالخدمة الوطنية',
            'Non-retour de l’agent à son poste après la fin d’un congé pour création d’entreprise, après un avertissement' => 'عدم رجوع العون إلى عمله إثر انتهاء عطلة لبعث مؤسسة، وبعد التنبيه عليه',
        ];

        // Maps pour les nouveaux champs
        $qualiteMap = [
            'Personnel (ATFP)' => 'عون الوكالة',
            'Personnel (Externe)' => 'عون من خارج الوكالة',
        ];

        $centreStatutMap = [
            'Centre officiel' => 'المركز الأصلي',
            'Centre secondaire' => 'المركز الثانوي',
        ];

        $forConsMap = [
            'Formateur' => 'مكون',
            'Conseiller d\'apprentissage' => 'مستشار تدريب',
        ];

        $etatPersonnelMap = [
            'Titulaire' => 'عون قار',
            'Contractuel' => 'عون متعاقد',
            'Détaché' => 'عون ملحق',
            'Autre' => 'حالة أخرى',
        ];

        $statutMap = [
            'Actif' => 'نشط',
            'Inactif' => 'غير نشط',
        ];

        $validator = Validator::make($request->all(), [
            // Champs existants
            'nom_fr' => 'nullable|string|max:255',
            'prenom_fr' => 'nullable|string|max:255',
            'nom_ar' => 'nullable|string|max:255',
            'prenom_ar' => 'nullable|string|max:255',
            'cin' => [
                'nullable',
                'string',
                'size:8',
                Rule::unique('users')->ignore($user->id),
            ],
            'matricule' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users')->ignore($user->id),
            ],
            'date_cin' => 'nullable|date|before:today',
            'lieu_cin_fr' => 'nullable|string|max:255',
            'lieu_cin_ar' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date|before:today',
            'lieu_naissance_fr' => 'nullable|string|max:255',
            'lieu_naissance_ar' => 'nullable|string|max:255',
            'nationalite_fr' => 'nullable|string|max:255',
            'nationalite_ar' => 'nullable|string|max:255',
            'date_recrutement' => 'nullable|date|before_or_equal:today',
            'date_fin_service' => 'nullable|date|after_or_equal:date_recrutement',
            'genre_fr' => 'nullable|in:Homme,Femme,Autre',
            'genre_ar' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request, $genreMap) {
                    if ($request->input('genre_fr') && $value !== ($genreMap[$request->input('genre_fr')] ?? null)) {
                        $fail('Le genre en arabe doit correspondre au genre en français.');
                    }
                    if (!$request->input('genre_fr') && $value) {
                        $fail('Le genre en arabe ne peut pas être défini si le genre en français est vide.');
                    }
                },
            ],
            'statut' => 'required|in:Actif,Inactif',
            'cause_inactivite_fr' => [
                'nullable',
                'string',
                'max:16777215',
                Rule::requiredIf($request->input('statut') === 'Inactif'),
            ],
            'cause_inactivite_ar' => [
                'nullable',
                'string',
                'max:16777215',
                Rule::requiredIf($request->input('statut') === 'Inactif'),
                function ($attribute, $value, $fail) use ($request, $causeInactiviteMap) {
                    if ($request->input('statut') === 'Inactif' && $request->input('cause_inactivite_fr') && isset($causeInactiviteMap[$request->input('cause_inactivite_fr')]) && $value !== $causeInactiviteMap[$request->input('cause_inactivite_fr')]) {
                        $fail("La cause d'inactivité en arabe doit correspondre à la cause d'inactivité en français.");
                    }
                    if ($request->input('statut') === 'Inactif' && !$request->input('cause_inactivite_fr') && $value) {
                        $fail("La cause d'inactivité en arabe ne peut pas être définie si la cause en français est vide.");
                    }
                },
            ],
            'adresse_fr' => 'nullable|string|max:16777215',
            'adresse_ar' => 'nullable|string|max:16777215',
            'gouvernorat_fr' => 'nullable|string|max:255',
            'gouvernorat_ar' => 'nullable|string|max:255',
            'delegation_fr' => 'nullable|string|max:255',
            'delegation_ar' => 'nullable|string|max:255',
            'telephone_1' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'telephone_2' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'etat_civil_fr' => 'nullable|string|max:255',
            'etat_civil_ar' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request, $etatCivilMap) {
                    if ($request->input('etat_civil_fr') && $value !== ($etatCivilMap[$request->input('etat_civil_fr')] ?? null)) {
                        $fail("L'état civil en arabe doit correspondre à l'état civil en français.");
                    }
                    if (!$request->input('etat_civil_fr') && $value) {
                        $fail("L'état civil en arabe ne peut pas être défini si l'état civil en français est vide.");
                    }
                },
            ],
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
            'ajouter_par' => 'nullable|integer|exists:users,id',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id,guard_name,web',
            // Champs déplacés vers users
            'qualite_fr' => 'required|string|max:255',
            'qualite_ar' => 'required|string|max:255',
            'etablissement_origine_fr' => 'nullable|string|max:255',
            'etablissement_origine_ar' => 'nullable|string|max:255',
            // Champs pour users_centres
            'centre_id' => 'required|exists:centres,id',
            'centre_statut_fr' => 'required|string|max:255',
            'centre_statut_ar' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'statut_affectation' => 'required|string|in:Actif,Inactif',
            'observation_fr_affectation' => 'nullable|string|max:1000',
            'observation_ar_affectation' => 'nullable|string|max:1000',
            // Champs pour personnels_centres
            'etat_personnel_fr' => 'required|string|max:255',
            'etat_personnel_ar' => 'required|string|max:255',
            'niveau_etude_fr' => 'required|string|max:255',
            'niveau_etude_ar' => 'required|string|max:255',
            'specialite_diplome_fr' => 'nullable|string|max:255',
            'specialite_diplome_ar' => 'nullable|string|max:255',
            'code_niveau' => 'nullable|string|max:10',
            'for_cons_fr' => 'nullable|string|max:255',
            'for_cons_ar' => 'nullable|string|max:255',
            'statut_personnel' => 'required|string|in:Actif,Inactif',
            'observation_fr_personnel' => 'nullable|string|max:1000',
            'observation_ar_personnel' => 'nullable|string|max:1000',
        ], [
            // Messages d'erreur existants
            'nom_fr.max' => 'Le nom en français ne doit pas dépasser 255 caractères.',
            'prenom_fr.max' => 'Le prénom en français ne doit pas dépasser 255 caractères.',
            'nom_ar.max' => 'Le nom en arabe ne doit pas dépasser 255 caractères.',
            'prenom_ar.max' => 'Le prénom en arabe ne doit pas dépasser 255 caractères.',
            'cin.size' => 'Le CIN doit contenir exactement 8 chiffres.',
            'cin.unique' => 'Ce CIN est déjà utilisé.',
            'matricule.max' => 'Le matricule ne doit pas dépasser 20 caractères.',
            'matricule.unique' => 'Ce matricule est déjà utilisé.',
            'date_cin.before' => 'La date de délivrance du CIN doit être antérieure à aujourd\'hui.',
            'lieu_cin_fr.max' => 'Le lieu de délivrance du CIN en français ne doit pas dépasser 255 caractères.',
            'lieu_cin_ar.max' => 'Le lieu de délivrance du CIN en arabe ne doit pas dépasser 255 caractères.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'lieu_naissance_fr.max' => 'Le lieu de naissance en français ne doit pas dépasser 255 caractères.',
            'lieu_naissance_ar.max' => 'Le lieu de naissance en arabe ne doit pas dépasser 255 caractères.',
            'nationalite_fr.max' => 'La nationalité en français ne doit pas dépasser 255 caractères.',
            'nationalite_ar.max' => 'La nationalité en arabe ne doit pas dépasser 255 caractères.',
            'date_recrutement.before_or_equal' => 'La date de recrutement doit être aujourd\'hui ou antérieure.',
            'date_fin_service.after_or_equal' => 'La date de fin de service doit être postérieure ou égale à la date de recrutement.',
            'genre_fr.in' => 'Le genre en français doit être Homme, Femme ou Autre.',
            'statut.required' => 'Le statut est requis.',
            'statut.in' => 'Le statut doit être Actif ou Inactif.',
            'cause_inactivite_fr.required' => 'La cause d\'inactivité en français est requise lorsque le statut est Inactif.',
            'cause_inactivite_fr.max' => 'La cause d\'inactivité en français ne doit pas dépasser 16777215 caractères.',
            'cause_inactivite_ar.required' => 'La cause d\'inactivité en arabe est requise lorsque le statut est Inactif.',
            'cause_inactivite_ar.max' => 'La cause d\'inactivité en arabe ne doit pas dépasser 16777215 caractères.',
            'adresse_fr.max' => 'L\'adresse en français ne doit pas dépasser 16777215 caractères.',
            'adresse_ar.max' => 'L\'adresse en arabe ne doit pas dépasser 16777215 caractères.',
            'gouvernorat_fr.max' => 'Le gouvernorat en français ne doit pas dépasser 255 caractères.',
            'gouvernorat_ar.max' => 'Le gouvernorat en arabe ne doit pas dépasser 255 caractères.',
            'delegation_fr.max' => 'La délégation en français ne doit pas dépasser 255 caractères.',
            'delegation_ar.max' => 'La délégation en arabe ne doit pas dépasser 255 caractères.',
            'telephone_1.regex' => 'Le numéro de téléphone 1 doit contenir entre 8 et 15 chiffres.',
            'telephone_2.regex' => 'Le numéro de téléphone 2 doit contenir entre 8 et 15 chiffres.',
            'etat_civil_fr.max' => 'L\'état civil en français ne doit pas dépasser 255 caractères.',
            'etat_civil_ar.max' => 'L\'état civil en arabe ne doit pas dépasser 255 caractères.',
            'observation_fr.max' => 'L\'observation en français ne doit pas dépasser 16777215 caractères.',
            'observation_ar.max' => 'L\'observation en arabe ne doit pas dépasser 16777215 caractères.',
            'photo.max' => 'La photo ne doit pas dépasser 16777215 caractères.',
            'ajouter_par.exists' => 'L\'utilisateur qui ajoute doit exister.',
            'email.required' => 'L\'email est requis.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'email.email' => 'L\'email doit être valide.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'roles.required' => 'Au moins un rôle doit être sélectionné.',
            'roles.*.exists' => 'Un ou plusieurs rôles sont invalides.',
            // Messages d'erreur pour les nouveaux champs
            'centre_id.required' => 'Le centre est requis.',
            'centre_id.exists' => 'Le centre sélectionné est invalide.',
            'qualite_fr.required' => 'La qualité est requise.',
            'qualite_ar.required' => 'La qualité en arabe est requise.',
            'etablissement_origine_fr.max' => "L'établissement d'origine ne doit pas dépasser 255 caractères.",
            'etablissement_origine_ar.max' => "L'établissement d'origine en arabe ne doit pas dépasser 255 caractères.",
            'centre_statut_fr.required' => 'Le statut du centre est requis.',
            'centre_statut_ar.required' => 'Le statut du centre en arabe est requis.',
            'date_debut.required' => 'La date de début est requise.',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'statut_affectation.required' => 'Le statut d\'affectation est requis.',
            'observation_fr_affectation.max' => 'L\'observation d\'affectation ne doit pas dépasser 1000 caractères.',
            'observation_ar_affectation.max' => 'L\'observation d\'affectation en arabe ne doit pas dépasser 1000 caractères.',
            'etat_personnel_fr.required' => 'L\'état du personnel est requis.',
            'etat_personnel_ar.required' => 'L\'état du personnel en arabe est requis.',
            'niveau_etude_fr.required' => 'Le niveau d\'étude est requis.',
            'niveau_etude_ar.required' => 'Le niveau d\'étude en arabe est requis.',
            'specialite_diplome_fr.max' => 'La spécialité du diplôme ne doit pas dépasser 255 caractères.',
            'specialite_diplome_ar.max' => 'La spécialité du diplôme en arabe ne doit pas dépasser 255 caractères.',
            'code_niveau.max' => 'Le code ne doit pas dépasser 10 caractères.',
            'for_cons_fr.max' => 'Le champ formateur/conseiller ne doit pas dépasser 255 caractères.',
            'for_cons_ar.max' => 'Le champ formateur/conseiller en arabe ne doit pas dépasser 255 caractères.',
            'statut_personnel.required' => 'Le statut du personnel est requis.',
            'observation_fr_personnel.max' => 'L\'observation du personnel ne doit pas dépasser 1000 caractères.',
            'observation_ar_personnel.max' => 'L\'observation du personnel en arabe ne doit pas dépasser 1000 caractères.',
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

            // Map genre_ar, etat_civil_ar, and cause_inactivite_ar
            $data['genre_ar'] = isset($data['genre_fr']) ? ($genreMap[$data['genre_fr']] ?? null) : null;
            $data['etat_civil_ar'] = isset($data['etat_civil_fr']) ? ($etatCivilMap[$data['etat_civil_fr']] ?? null) : null;
            $data['cause_inactivite_ar'] = isset($data['cause_inactivite_fr']) ? ($causeInactiviteMap[$data['cause_inactivite_fr']] ?? $data['cause_inactivite_ar']) : null;

            // Map les nouveaux champs
            $data['qualite_ar'] = isset($data['qualite_fr']) ? ($qualiteMap[$data['qualite_fr']] ?? null) : null;
            $data['centre_statut_ar'] = isset($data['centre_statut_fr']) ? ($centreStatutMap[$data['centre_statut_fr']] ?? null) : null;
            $data['for_cons_ar'] = isset($data['for_cons_fr']) ? ($forConsMap[$data['for_cons_fr']] ?? null) : null;
            $data['etat_personnel_ar'] = isset($data['etat_personnel_fr']) ? ($etatPersonnelMap[$data['etat_personnel_fr']] ?? null) : null;

            // Set default nationality values if not provided
            $data['nationalite_fr'] = $data['nationalite_fr'] ?? 'Tunisienne';
            $data['nationalite_ar'] = $data['nationalite_ar'] ?? 'تونسية';

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

            // Convert role IDs to role names
            $roleIds = $data['roles'];
            $roleNames = Role::whereIn('id', $roleIds)->where('guard_name', 'web')->pluck('name')->toArray();
            if (count($roleIds) !== count($roleNames)) {
                Log::error('Certains rôles sont introuvables:', ['role_ids' => $roleIds]);
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour du personnel',
                    'errors' => ['roles' => ['Un ou plusieurs rôles sont introuvables.']]
                ], 422);
            }

            // Store photo as base64
            $photo = !empty($data['photo']) && str_starts_with($data['photo'], 'data:image/') ? $data['photo'] : $user->photo;

            $updateData = [
                'nom_fr' => $data['nom_fr'] ?? $user->nom_fr,
                'prenom_fr' => $data['prenom_fr'] ?? $user->prenom_fr,
                'nom_ar' => $data['nom_ar'] ?? $user->nom_ar,
                'prenom_ar' => $data['prenom_ar'] ?? $user->prenom_ar,
                'cin' => $data['cin'] ?? $user->cin,
                'matricule' => $data['matricule'] ?? $user->matricule,
                'date_cin' => $data['date_cin'] ?? $user->date_cin,
                'lieu_cin_fr' => $data['lieu_cin_fr'] ?? $user->lieu_cin_fr,
                'lieu_cin_ar' => $data['lieu_cin_ar'] ?? $user->lieu_cin_ar,
                'date_naissance' => $data['date_naissance'] ?? $user->date_naissance,
                'lieu_naissance_fr' => $data['lieu_naissance_fr'] ?? $user->lieu_naissance_fr,
                'lieu_naissance_ar' => $data['lieu_naissance_ar'] ?? $user->lieu_naissance_ar,
                'nationalite_fr' => $data['nationalite_fr'] ?? $user->nationalite_fr,
                'nationalite_ar' => $data['nationalite_ar'] ?? $user->nationalite_ar,
                'date_recrutement' => $data['date_recrutement'] ?? $user->date_recrutement,
                'date_fin_service' => $data['statut'] === 'Actif' ? null : ($data['date_fin_service'] ?? $user->date_fin_service),
                'genre_fr' => $data['genre_fr'] ?? $user->genre_fr,
                'genre_ar' => $data['genre_ar'] ?? $user->genre_ar,
                'statut' => $data['statut'] ?? $user->statut,
                'cause_inactivite_fr' => $data['statut'] === 'Actif' ? null : ($data['cause_inactivite_fr'] ?? $user->cause_inactivite_fr),
                'cause_inactivite_ar' => $data['statut'] === 'Actif' ? null : ($data['cause_inactivite_ar'] ?? $user->cause_inactivite_ar),
                'adresse_fr' => $data['adresse_fr'] ?? $user->adresse_fr,
                'adresse_ar' => $data['adresse_ar'] ?? $user->adresse_ar,
                'gouvernorat_fr' => $data['gouvernorat_fr'] ?? $user->gouvernorat_fr,
                'gouvernorat_ar' => $data['gouvernorat_ar'] ?? $user->gouvernorat_ar,
                'delegation_fr' => $data['delegation_fr'] ?? $user->delegation_fr,
                'delegation_ar' => $data['delegation_ar'] ?? $user->delegation_ar,
                'telephone_1' => $data['telephone_1'] ?? $user->telephone_1,
                'telephone_2' => $data['telephone_2'] ?? $user->telephone_2,
                'etat_civil_fr' => $data['etat_civil_fr'] ?? $user->etat_civil_fr,
                'etat_civil_ar' => $data['etat_civil_ar'] ?? $user->etat_civil_ar,
                'observation_fr' => $data['observation_fr'] ?? $user->observation_fr,
                'observation_ar' => $data['observation_ar'] ?? $user->observation_ar,
                'photo' => $photo,
                'ajouter_par' => $data['ajouter_par'] ?? $user->ajouter_par,
                'email' => $data['email'] ?? $user->email,
                'qualite_fr' => $data['qualite_fr'] ?? $user->qualite_fr,
                'qualite_ar' => $data['qualite_ar'] ?? $user->qualite_ar,
                'etablissement_origine_fr' => $data['etablissement_origine_fr'] ?? $user->etablissement_origine_fr,
                'etablissement_origine_ar' => $data['etablissement_origine_ar'] ?? $user->etablissement_origine_ar,
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $user->update($updateData);
            $user->syncRoles($roleNames);

            // Mettre à jour users_centres
            $userCentre = UserCentre::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'centre_id' => $data['centre_id'],
                    'centre_statut_fr' => $data['centre_statut_fr'],
                    'centre_statut_ar' => $data['centre_statut_ar'],
                    'date_debut' => $data['date_debut'],
                    'date_fin' => $data['date_fin'],
                    'statut' => $data['statut_affectation'],
                    'observation_fr' => $data['observation_fr_affectation'],
                    'observation_ar' => $data['observation_ar_affectation'],
                ]
            );

            // Mettre à jour personnels_centres
            PersonnelCentre::updateOrCreate(
                ['user_centre_id' => $userCentre->id],
                [
                    'etat_personnel_fr' => $data['etat_personnel_fr'],
                    'etat_personnel_ar' => $data['etat_personnel_ar'],
                    'niveau_etude_fr' => $data['niveau_etude_fr'],
                    'niveau_etude_ar' => $data['niveau_etude_ar'],
                    'specialite_diplome_fr' => $data['specialite_diplome_fr'],
                    'specialite_diplome_ar' => $data['specialite_diplome_ar'],
                    'code_niveau' => $data['code_niveau'],
                    'for_cons_fr' => $data['for_cons_fr'],
                    'for_cons_ar' => $data['for_cons_ar'],
                    'statut' => $data['statut_personnel'],
                    'observation_fr' => $data['observation_fr_personnel'],
                    'observation_ar' => $data['observation_ar_personnel'],
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $user->load(['roles' => function ($query) {
                    $query->select('roles.id', 'roles.name', 'roles.name_ar');
                }]),
                'message' => 'Personnel ATFP mis à jour avec succès'
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

    public function show(User $user)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $user->load(['roles' => function ($query) {
                    $query->select('roles.id', 'roles.name', 'roles.name_ar');
                }]),
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

    public function checkMatricule(Request $request)
    {
        Log::info('checkMatricule appelé pour matricule:', ['matricule' => $request->matricule]);
        try {
            $validator = Validator::make($request->all(), [
                'matricule' => 'required|string|max:50|unique:users,matricule',
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
                'cin' => 'required|string|size:8|unique:users,cin',
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

    public function destroy(Request $request, User $user)
    {
        Log::info('destroy appelé pour personnel ID:', ['id' => $user->id]);
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

            if ($user->photo) {
                Storage::disk('public')->delete(str_replace(Storage::url(''), '', $user->photo));
            }

            $user->delete();

            Log::info('Personnel ATFP supprimé avec succès', ['id' => $user->id]);
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

    public function getUserRoles(User $user)
    {
        try {
            $roles = $user->roles()->select('roles.id', 'roles.name', 'roles.name_ar')->get();
            Log::info('Fetched roles for user:', ['user_id' => $user->id, 'roles' => $roles->toArray()]);
            return response()->json([
                'success' => true,
                'data' => $roles,
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
            'lineData.cin' => 'nullable|string|size:8|regex:/^[A-Za-z0-9]{8}$/|unique:users,cin',
            'lineData.matricule' => 'nullable|string|max:20|unique:users,matricule',
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
            'lineData.date_fin_service' => 'nullable|date_format:Y-m-d',
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
            'lineData.statut' => 'required|in:Actif',
            'lineData.qualite_fr' => 'required|string|max:255',
            'lineData.qualite_ar' => 'required|string|max:255',
            'lineData.etablissement_origine_fr' => 'nullable|string|max:255',
            'lineData.etablissement_origine_ar' => 'nullable|string|max:255',
            'lineData.etat_personnel_fr' => 'required|string|max:255',
            'lineData.etat_personnel_ar' => 'required|string|max:255',
            'lineData.niveau_etude_fr' => 'required|string|max:255',
            'lineData.niveau_etude_ar' => 'required|string|max:255',
            'lineData.specialite_diplome_fr' => 'nullable|string|max:255',
            'lineData.specialite_diplome_ar' => 'nullable|string|max:255',
            'lineData.code_niveau' => 'nullable|string|max:10',
            'lineData.for_cons_fr' => 'nullable|string|max:255',
            'lineData.for_cons_ar' => 'nullable|string|max:255',
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
            'lineData.date_fin_service.date_format' => 'La date de fin de service doit être au format AAAA-MM-JJ et valide.',
            'lineData.telephone_1.regex' => 'Le numéro de téléphone 1 doit contenir entre 8 et 15 chiffres.',
            'lineData.telephone_2.regex' => 'Le numéro de téléphone 2 doit contenir entre 8 et 15 chiffres.',
            'lineData.qualite_fr.required' => 'La qualité est requise.',
            'lineData.qualite_ar.required' => 'La qualité en arabe est requise.',
            'lineData.etat_personnel_fr.required' => 'L\'état du personnel est requis.',
            'lineData.etat_personnel_ar.required' => 'L\'état du personnel en arabe est requis.',
            'lineData.niveau_etude_fr.required' => 'Le niveau d\'étude est requis.',
            'lineData.niveau_etude_ar.required' => 'Le niveau d\'étude en arabe est requis.',
            'lineData.for_cons_fr.max' => 'Le champ formateur/conseiller ne doit pas dépasser 255 caractères.',
            'lineData.for_cons_ar.max' => 'Le champ formateur/conseiller en arabe ne doit pas dépasser 255 caractères.',
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
            $importService = new PersonnelsCentresImportService;
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
                'date_fin_service' => [
                    'nullable',
                    'date_format:Y-m-d',
                    function ($attribute, $value, $fail) use ($lineData) {
                        if ($value && isset($lineData['date_recrutement']) && $lineData['date_recrutement'] && $value < $lineData['date_recrutement']) {
                            $fail('La date de fin de service doit être postérieure ou égale à la date de recrutement.');
                        }
                    },
                ],
            ], [
                'date_cin.date_format' => 'La date CIN doit être au format AAAA-MM-JJ et valide.',
                'date_cin.before' => 'La date CIN doit être antérieure à aujourd\'hui.',
                'date_recrutement.date_format' => 'La date de recrutement doit être au format AAAA-MM-JJ et valide.',
                'date_recrutement.before_or_equal' => 'La date de recrutement doit être antérieure ou égale à aujourd\'hui.',
                'date_fin_service.date_format' => 'La date de fin de service doit être au format AAAA-MM-JJ et valide.',
            ]);

            if ($lineValidator->fails()) {
                Log::warning('Erreurs de validation des dates dans import-line:', $lineValidator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation dans les données de la ligne.',
                    'errors' => $lineValidator->errors(),
                ], 422);
            }

            $importService = new PersonnelsCentresImportService;
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
            // Récupérer toutes les données de la table users avec leurs rôles
            $users = User::with('roles')->get();

            // Créer un nouveau classeur Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Définir les en-têtes des colonnes (excluant photo, password, remember_token)
            $headers = [
                'ID', 'Nom Fr', 'Prénom Fr', 'Nom Ar', 'Prénom Ar', 'Matricule', 'CIN', 'Date CIN',
                'Lieu CIN Fr', 'Lieu CIN Ar', 'Date Naissance', 'Lieu Naissance Fr', 'Lieu Naissance Ar',
                'Nationalité Fr', 'Nationalité Ar', 'Date Recrutement', 'Date Fin Service', 'Genre Fr',
                'Genre Ar', 'Statut', 'Cause Inactivité Fr', 'Cause Inactivité Ar', 'Adresse Fr',
                'Adresse Ar', 'Gouvernorat Fr', 'Gouvernorat Ar', 'Délégation Fr', 'Délégation Ar',
                'Téléphone 1', 'Téléphone 2', 'État Civil Fr', 'État Civil Ar', 'Observation Fr',
                'Observation Ar', 'Qualité Fr', 'Qualité Ar', 'Établissement Origine Fr', 'Établissement Origine Ar',
                'Ajouté Par', 'Email', 'Email Vérifié À', 'Créé Le', 'Mis à Jour Le',
                'Supprimé Le', 'Rôles'
            ];

            // Ajouter les en-têtes à la première ligne
            $sheet->fromArray($headers, null, 'A1');

            // Remplir les données
            $row = 2; // Commencer à la ligne 2 pour laisser la première ligne aux en-têtes
            foreach ($users as $user) {
                $roles = $user->roles->pluck('name')->implode(', '); // Concaténer les noms des rôles
                $sheet->fromArray([
                    $user->id,
                    $user->nom_fr ?? '-',
                    $user->prenom_fr ?? '-',
                    $user->nom_ar ?? '-',
                    $user->prenom_ar ?? '-',
                    $user->matricule ?? '-',
                    $user->cin ?? '-',
                    $user->date_cin ? $user->date_cin->format('Y-m-d') : '-',
                    $user->lieu_cin_fr ?? '-',
                    $user->lieu_cin_ar ?? '-',
                    $user->date_naissance ? $user->date_naissance->format('Y-m-d') : '-',
                    $user->lieu_naissance_fr ?? '-',
                    $user->lieu_naissance_ar ?? '-',
                    $user->nationalite_fr ?? '-',
                    $user->nationalite_ar ?? '-',
                    $user->date_recrutement ? $user->date_recrutement->format('Y-m-d') : '-',
                    $user->date_fin_service ? $user->date_fin_service->format('Y-m-d') : '-',
                    $user->genre_fr ?? '-',
                    $user->genre_ar ?? '-',
                    $user->statut ?? '-',
                    $user->cause_inactivite_fr ?? '-',
                    $user->cause_inactivite_ar ?? '-',
                    $user->adresse_fr ?? '-',
                    $user->adresse_ar ?? '-',
                    $user->gouvernorat_fr ?? '-',
                    $user->gouvernorat_ar ?? '-',
                    $user->delegation_fr ?? '-',
                    $user->delegation_ar ?? '-',
                    $user->telephone_1 ?? '-',
                    $user->telephone_2 ?? '-',
                    $user->etat_civil_fr ?? '-',
                    $user->etat_civil_ar ?? '-',
                    $user->observation_fr ?? '-',
                    $user->observation_ar ?? '-',
                    $user->qualite_fr ?? '-',
                    $user->qualite_ar ?? '-',
                    $user->etablissement_origine_fr ?? '-',
                    $user->etablissement_origine_ar ?? '-',
                    $user->ajouter_par ?? '-',
                    $user->email ?? '-',
                    $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i:s') : '-',
                    $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '-',
                    $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : '-',
                    $user->deleted_at ? $user->deleted_at->format('Y-m-d H:i:s') : '-',
                    $roles ?: '-'
                ], null, 'A' . $row);
                $row++;
            }

            // Ajuster automatiquement la largeur des colonnes
            foreach (range('A', 'AO') as $column) {
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

            // Définir les en-têtes des colonnes selon la liste fournie
            $headers = [
                'Nom Fr', 'Prénom Fr', 'Nom Ar', 'Prénom Ar', 'Matricule', 'CIN', 'Date CIN',
                'Lieu CIN Fr', 'Lieu CIN Ar', 'Date Naissance', 'Lieu Naissance Fr', 'Lieu Naissance Ar',
                'Nationalité Fr', 'Nationalité Ar', 'Date Recrutement', 'Date Fin Service', 'Genre Fr',
                'Adresse Fr', 'Adresse Ar', 'Gouvernorat Fr', 'Délégation Fr', 'Délégation Ar',
                'Téléphone 1', 'Téléphone 2', 'État Civil Fr', 'Email', 'Rôles', 'Qualité Fr',
                'Qualité Ar', 'Établissement Origine Fr', 'Établissement Origine Ar', 'État Personnel Fr',
                'État Personnel Ar', 'Niveau Étude Fr', 'Niveau Étude Ar', 'Spécialité Diplôme Fr',
                'Spécialité Diplôme Ar', 'Code Niveau', 'Formateur/Conseiller Fr', 'Formateur/Conseiller Ar'
            ];

            // Ajouter les en-têtes à la première ligne
            $sheet->fromArray($headers, null, 'A1');

            // Récupérer tous les rôles avec is_centre_role = 0 et statut = 'actif'
            $roles = Role::withoutGlobalScopes()
                ->where('guard_name', 'web')
                ->where('is_centre_role', 0)
                ->where('statut', 'actif')
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

            // Ajouter une validation de liste déroulante pour la colonne Rôles (colonne Y)
            $validationRoles = $sheet->getCell('Y2')->getDataValidation();
            $validationRoles->setType(DataValidation::TYPE_LIST);
            $validationRoles->setErrorStyle(DataValidation::STYLE_STOP);
            $validationRoles->setAllowBlank(true);
            $validationRoles->setShowDropDown(true);
            $validationRoles->setFormula1('ListeRôles!$A$1:$A$' . count($roles));
            $validationRoles->setErrorTitle('Rôle invalide');
            $validationRoles->setError('Veuillez sélectionner un rôle dans la liste déroulante.');

            // Appliquer la validation à la plage Y3:Y1000
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell('Y' . $row)->setDataValidation(clone $validationRoles);
            }

            // Ajouter une liste déroulante pour la colonne Genre Fr (colonne Q)
            $genres = ['Homme', 'Femme'];
            $genreSheet = $spreadsheet->createSheet();
            $genreSheet->setTitle('ListeGenres');
            foreach ($genres as $index => $genre) {
                $genreSheet->setCellValue('A' . ($index + 1), $genre);
            }
            $genreSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

            // Validation pour la colonne Genre Fr (Q)
            $validationGenres = $sheet->getCell('Q2')->getDataValidation();
            $validationGenres->setType(DataValidation::TYPE_LIST);
            $validationGenres->setErrorStyle(DataValidation::STYLE_STOP);
            $validationGenres->setAllowBlank(true);
            $validationGenres->setShowDropDown(true);
            $validationGenres->setFormula1('ListeGenres!$A$1:$A$' . count($genres));
            $validationGenres->setErrorTitle('Genre invalide');
            $validationGenres->setError('Veuillez sélectionner un genre dans la liste déroulante.');

            // Appliquer la validation à la plage Q2:Q1000
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell('Q' . $row)->setDataValidation(clone $validationGenres);
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

            // Validation pour la colonne Gouvernorat Fr (T)
            $validationGouvernorats = $sheet->getCell('T2')->getDataValidation();
            $validationGouvernorats->setType(DataValidation::TYPE_LIST);
            $validationGouvernorats->setErrorStyle(DataValidation::STYLE_STOP);
            $validationGouvernorats->setAllowBlank(true);
            $validationGouvernorats->setShowDropDown(true);
            $validationGouvernorats->setFormula1('ListeGouvernorats!$A$1:$A$' . count($gouvernorats));
            $validationGouvernorats->setErrorTitle('Gouvernorat invalide');
            $validationGouvernorats->setError('Veuillez sélectionner un gouvernorat dans la liste déroulante.');

            // Appliquer la validation à la plage T2:T1000
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell('T' . $row)->setDataValidation(clone $validationGouvernorats);
            }

            // Ajouter une liste déroulante pour la colonne État Civil Fr (colonne Y)
            $etatsCivils = ['Célibataire', 'Marié', 'Divorcé', 'Veuf'];
            $etatCivilSheet = $spreadsheet->createSheet();
            $etatCivilSheet->setTitle('ListeEtatsCivils');
            foreach ($etatsCivils as $index => $etatCivil) {
                $etatCivilSheet->setCellValue('A' . ($index + 1), $etatCivil);
            }
            $etatCivilSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

            // Validation pour la colonne État Civil Fr (Y)
            $validationEtatsCivils = $sheet->getCell('Y2')->getDataValidation();
            $validationEtatsCivils->setType(DataValidation::TYPE_LIST);
            $validationEtatsCivils->setErrorStyle(DataValidation::STYLE_STOP);
            $validationEtatsCivils->setAllowBlank(true);
            $validationEtatsCivils->setShowDropDown(true);
            $validationEtatsCivils->setFormula1('ListeEtatsCivils!$A$1:$A$' . count($etatsCivils));
            $validationEtatsCivils->setErrorTitle('État civil invalide');
            $validationEtatsCivils->setError('Veuillez sélectionner un état civil dans la liste déroulante.');

            // Appliquer la validation à la plage Y3:Y1000
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell('Y' . $row)->setDataValidation(clone $validationEtatsCivils);
            }

            // Ajouter une liste déroulante pour la colonne Qualité Fr (colonne Z)
            $qualites = ['Personnel (ATFP)', 'Personnel (Externe)'];
            $qualiteSheet = $spreadsheet->createSheet();
            $qualiteSheet->setTitle('ListeQualites');
            foreach ($qualites as $index => $qualite) {
                $qualiteSheet->setCellValue('A' . ($index + 1), $qualite);
            }
            $qualiteSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

            // Validation pour la colonne Qualité Fr (Z)
            $validationQualites = $sheet->getCell('Z2')->getDataValidation();
            $validationQualites->setType(DataValidation::TYPE_LIST);
            $validationQualites->setErrorStyle(DataValidation::STYLE_STOP);
            $validationQualites->setAllowBlank(true);
            $validationQualites->setShowDropDown(true);
            $validationQualites->setFormula1('ListeQualites!$A$1:$A$' . count($qualites));
            $validationQualites->setErrorTitle('Qualité invalide');
            $validationQualites->setError('Veuillez sélectionner une qualité dans la liste déroulante.');

            // Appliquer la validation à la plage Z2:Z1000
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell('Z' . $row)->setDataValidation(clone $validationQualites);
            }

            // Ajouter une liste déroulante pour la colonne État Personnel Fr (colonne AC)
            $etatsPersonnel = ['Titulaire', 'Contractuel', 'Détaché', 'Autre'];
            $etatPersonnelSheet = $spreadsheet->createSheet();
            $etatPersonnelSheet->setTitle('ListeEtatsPersonnel');
            foreach ($etatsPersonnel as $index => $etat) {
                $etatPersonnelSheet->setCellValue('A' . ($index + 1), $etat);
            }
            $etatPersonnelSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

            // Validation pour la colonne État Personnel Fr (AC)
            $validationEtatsPersonnel = $sheet->getCell('AC2')->getDataValidation();
            $validationEtatsPersonnel->setType(DataValidation::TYPE_LIST);
            $validationEtatsPersonnel->setErrorStyle(DataValidation::STYLE_STOP);
            $validationEtatsPersonnel->setAllowBlank(true);
            $validationEtatsPersonnel->setShowDropDown(true);
            $validationEtatsPersonnel->setFormula1('ListeEtatsPersonnel!$A$1:$A$' . count($etatsPersonnel));
            $validationEtatsPersonnel->setErrorTitle('État du personnel invalide');
            $validationEtatsPersonnel->setError('Veuillez sélectionner un état du personnel dans la liste déroulante.');

            // Appliquer la validation à la plage AC2:AC1000
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell('AC' . $row)->setDataValidation(clone $validationEtatsPersonnel);
            }

            // Ajouter une liste déroulante pour la colonne Formateur/Conseiller Fr (colonne AH)
            $forCons = ['Formateur', 'Conseiller d\'apprentissage'];
            $forConsSheet = $spreadsheet->createSheet();
            $forConsSheet->setTitle('ListeForCons');
            foreach ($forCons as $index => $item) {
                $forConsSheet->setCellValue('A' . ($index + 1), $item);
            }
            $forConsSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

            // Validation pour la colonne Formateur/Conseiller Fr (AH)
            $validationForCons = $sheet->getCell('AH2')->getDataValidation();
            $validationForCons->setType(DataValidation::TYPE_LIST);
            $validationForCons->setErrorStyle(DataValidation::STYLE_STOP);
            $validationForCons->setAllowBlank(true);
            $validationForCons->setShowDropDown(true);
            $validationForCons->setFormula1('ListeForCons!$A$1:$A$' . count($forCons));
            $validationForCons->setErrorTitle('Formateur/Conseiller invalide');
            $validationForCons->setError('Veuillez sélectionner une valeur dans la liste déroulante.');

            // Appliquer la validation à la plage AH2:AH1000
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell('AH' . $row)->setDataValidation(clone $validationForCons);
            }

            // Ajuster automatiquement la largeur des colonnes
            foreach (range('A', 'AH') as $column) {
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
            $lists = Liste::whereIn('nom_fr', ['Gouvernorats', 'Niveaux Etudes PersonnelsDirectionCentrale'])
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
