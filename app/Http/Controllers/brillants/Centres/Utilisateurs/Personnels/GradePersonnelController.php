<?php

namespace App\Http\Controllers\brillants\Centres\Utilisateurs\Personnels;

use App\Http\Controllers\Controller;
use App\Models\Centre\Centre;
use App\Models\Centre\Utilisateurs\Personnels\GradePersonnel;
use App\Models\DeletePassword;
use App\Models\Liste;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GradePersonnelController extends Controller
{
    /**
     * Mettre à jour les statuts des grades pour garantir qu'un seul grade est actif.
     * Le grade avec la date de début la plus récente est marqué comme Actif,
     * tous les autres sont marqués comme Inactif.
     *
     * @param int $personnelId
     * @param int|null $excludeGradeId ID du grade à exclure (optionnel, utilisé lors de l'update)
     * @return void
     */
    private function updateGradeStatuses($personnelId, $excludeGradeId = null)
    {
        try {
            $query = GradePersonnel::where('personnel_id', $personnelId)
                ->whereNull('deleted_at');

            if ($excludeGradeId) {
                $query->where('id', '!=', $excludeGradeId);
            }

            $grades = $query->orderBy('date_debut', 'desc')->get();

            if ($grades->isEmpty()) {
                Log::info('Aucun grade trouvé pour mise à jour des statuts.', [
                    'personnel_id' => $personnelId,
                    'exclude_grade_id' => $excludeGradeId
                ]);
                return;
            }

            $latestGrade = $grades->first();

            foreach ($grades as $grade) {
                $isLatest = $grade->id === $latestGrade->id;
                $grade->statut = $isLatest ? 'Actif' : 'Inactif';
                $grade->save();

                Log::info('Mise à jour du statut du grade.', [
                    'grade_id' => $grade->id,
                    'personnel_id' => $personnelId,
                    'statut' => $grade->statut,
                    'date_debut' => $grade->date_debut
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour des statuts des grades.', [
                'personnel_id' => $personnelId,
                'exclude_grade_id' => $excludeGradeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function getGradeTypes()
    {
        try {
            $filieres = Liste::where('nom_fr', 'Filières PersonnelsDirectionCentrale')->first();
            $grades = Liste::where('nom_fr', 'Grades PersonnelsDirectionCentrale')->first();
            $postes = Liste::where('nom_fr', 'Postes PersonnelsDirectionCentrale')->first();
            $centres = Centre::whereNull('deleted_at')->select('id', 'nom_fr', 'nom_ar')->get();

            $filieresOptions = $filieres && $filieres->options
                ? (is_array($filieres->options) ? $filieres->options : json_decode($filieres->options, true))
                : [];
            $gradesOptions = $grades && $grades->options
                ? (is_array($grades->options) ? $grades->options : json_decode($grades->options, true))
                : [];
            $postesOptions = $postes && $postes->options
                ? (is_array($postes->options) ? $postes->options : json_decode($postes->options, true))
                : [];

            return response()->json([
                'filieres' => $filieresOptions,
                'grades' => $gradesOptions,
                'postes' => $postesOptions,
                'centres' => $centres->toArray()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur dans getGradeTypes: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la récupération des types de grades.'], 500);
        }
    }

    public function index()
    {
        try {
            $personnel_id = request()->query('personnel_id');
            if (!$personnel_id) {
                Log::warning('personnel_id manquant dans la requête.', ['query' => request()->query()]);
                return response()->json(['error' => 'personnel_id est requis.'], 400);
            }

            $personnelExists = PersonnelCentre::where('id', $personnel_id)->exists();
            if (!$personnelExists) {
                Log::warning('Personnel non trouvé.', ['personnel_id' => $personnel_id]);
                return response()->json(['error' => 'Personnel non trouvé.'], 404);
            }

            $grades = GradePersonnel::where('personnel_id', $personnel_id)
                ->whereNull('grades_personnels.deleted_at')
                ->join('personnels_centres', 'grades_personnels.personnel_id', '=', 'personnels_centres.id')
                ->join('users_centres', 'personnels_centres.user_centre_id', '=', 'users_centres.id')
                ->join('users', 'users_centres.user_id', '=', 'users.id')
                ->select(
                    'grades_personnels.*',
                    'users.nom_fr',
                    'users.prenom_fr',
                    'users.nom_ar',
                    'users.prenom_ar',
                    'users.photo',
                    'users_centres.centre_id'
                )
                ->get();

            $userInfo = [];
            if ($grades->isNotEmpty()) {
                $userInfo = [
                    'nom_fr' => $grades[0]->nom_fr ?? '',
                    'prenom_fr' => $grades[0]->prenom_fr ?? '',
                    'nom_ar' => $grades[0]->nom_ar ?? '',
                    'prenom_ar' => $grades[0]->prenom_ar ?? '',
                    'photo' => $grades[0]->photo,
                    'centre_id' => $grades[0]->centre_id,
                    'is_super_admin' => Auth::user()->hasRole('SuperAdmin')
                ];
            } else {
                $personnel = PersonnelCentre::where('personnels_centres.id', $personnel_id)
                    ->join('users_centres', 'personnels_centres.user_centre_id', '=', 'users_centres.id')
                    ->join('users', 'users_centres.user_id', '=', 'users.id')
                    ->select(
                        'users.nom_fr',
                        'users.prenom_fr',
                        'users.nom_ar',
                        'users.prenom_ar',
                        'users.photo',
                        'users_centres.centre_id'
                    )
                    ->first();

                $userInfo = $personnel ? [
                    'nom_fr' => $personnel->nom_fr ?? '',
                    'prenom_fr' => $personnel->prenom_fr ?? '',
                    'nom_ar' => $personnel->nom_ar ?? '',
                    'prenom_ar' => $personnel->prenom_ar ?? '',
                    'photo' => $personnel->photo,
                    'centre_id' => $personnel->centre_id,
                    'is_super_admin' => Auth::user()->hasRole('SuperAdmin')
                ] : [
                    'nom_fr' => '',
                    'prenom_fr' => '',
                    'nom_ar' => '',
                    'prenom_ar' => '',
                    'photo' => null,
                    'centre_id' => null,
                    'is_super_admin' => Auth::user()->hasRole('SuperAdmin')
                ];
            }

            Log::info('GradePersonnelController::index', [
                'personnel_id' => $personnel_id,
                'user_info' => $userInfo,
                'grades_count' => $grades->count()
            ]);

            $centres = Centre::whereNull('deleted_at')->select('id', 'nom_fr', 'nom_ar')->get();

            return response()->json([
                'grades' => $grades->toArray(),
                'user_info' => $userInfo,
                'centres' => $centres->toArray()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur dans index: ' . $e->getMessage(), [
                'exception' => $e,
                'personnel_id' => request()->query('personnel_id'),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erreur lors de la récupération des grades.'], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'personnel_id' => 'required|exists:personnels_centres,id',
            'filiere_fr' => 'nullable|string',
            'filiere_ar' => 'nullable|string',
            'grade_fr' => 'required|string',
            'grade_ar' => 'nullable|string',
            'poste_fr' => 'nullable|string',
            'poste_ar' => 'nullable|string',
            'categorie' => 'nullable|string',
            'echelle' => 'nullable|string',
            'echelon' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'observation_fr' => 'nullable|string',
            'observation_ar' => 'nullable|string',
            'statut' => 'nullable|in:Actif,Inactif',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $grade = new GradePersonnel($request->all());
            $grade->statut = 'Actif';
            $grade->save();

            $this->updateGradeStatuses($request->personnel_id);

            return response()->json(['message' => 'Grade créé avec succès.', 'data' => $grade], 201);
        } catch (\Exception $e) {
            Log::error('Erreur dans store: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la création du grade.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $grade = GradePersonnel::withTrashed()->findOrFail($id);
            return response()->json($grade, 200);
        } catch (\Exception $e) {
            Log::error('Erreur dans show: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Grade non trouvé.'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'filiere_fr' => 'nullable|string',
            'filiere_ar' => 'nullable|string',
            'grade_fr' => 'required|string',
            'grade_ar' => 'nullable|string',
            'poste_fr' => 'nullable|string',
            'poste_ar' => 'nullable|string',
            'categorie' => 'nullable|string',
            'echelle' => 'nullable|string',
            'echelon' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'observation_fr' => 'nullable|string',
            'observation_ar' => 'nullable|string',
            'statut' => 'nullable|in:Actif,Inactif',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $grade = GradePersonnel::findOrFail($id);
            $grade->fill($request->all());
            $grade->save();

            $this->updateGradeStatuses($grade->personnel_id, $id);

            return response()->json(['message' => 'Grade mis à jour avec succès.', 'data' => $grade], 200);
        } catch (\Exception $e) {
            Log::error('Erreur dans update: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la mise à jour du grade.'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for destroy request', [
                'id' => $id,
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $deletePassword = DeletePassword::first();
            Log::info('Checking password for destroy', [
                'id' => $id,
                'user_id' => Auth::id(),
                'password_exists' => !is_null($deletePassword),
                'password_provided' => !empty($request->password)
            ]);

            if (!$deletePassword) {
                Log::error('No delete password record found');
                return response()->json(['error' => 'Aucun mot de passe de suppression configuré.'], 500);
            }

            if (!$deletePassword->verifyPassword($request->password)) {
                Log::warning('Password verification failed', [
                    'id' => $id,
                    'user_id' => Auth::id()
                ]);
                return response()->json(['error' => 'Mot de passe incorrect.'], 401);
            }

            $grade = GradePersonnel::findOrFail($id);
            $personnelId = $grade->personnel_id;
            $wasActive = $grade->statut === 'Actif';
            $grade->delete();

            Log::info('Grade deleted successfully', [
                'id' => $id,
                'personnel_id' => $personnelId,
                'was_active' => $wasActive
            ]);

            if ($wasActive) {
                $this->updateGradeStatuses($personnelId);
            }

            return response()->json(['message' => 'Grade supprimé avec succès.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur dans destroy: ' . $e->getMessage(), [
                'exception' => $e,
                'id' => $id,
                'user_id' => Auth::id(),
                'password_provided' => !empty($request->password)
            ]);
            return response()->json(['error' => 'Erreur lors de la suppression du grade.'], 500);
        }
    }

    private function getArabicTranslation($frenchValue, $listName)
    {
        if (!$frenchValue) return null;
        $liste = Liste::where('nom_fr', $listName)->first();
        if ($liste) {
            $options = is_array($liste->options) ? $liste->options : json_decode($liste->options, true);
            $option = collect($options)->firstWhere('nom_fr', $frenchValue);
            return $option['nom_ar'] ?? null;
        }
        return null;
    }
}
