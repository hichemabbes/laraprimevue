<?php

namespace App\Http\Controllers;

use App\Imports\RolesImport;
use App\Models\DeletePassword;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Ajout pour récupérer l'utilisateur connecté
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    private function verifyDeletePassword(Request $request)
    {
        $request->validate(['password' => 'required|string']);
        $passwordRecord = Cache::remember('delete_password', 3600, fn () => DeletePassword::first());

        if (!$passwordRecord) {
            Log::warning('Aucun mot de passe de suppression défini');
            throw new \Exception('Aucun mot de passe de suppression défini', 400);
        }

        if (!$passwordRecord->verifyPassword($request->password)) {
            Log::warning('Mot de passe incorrect pour suppression', ['input' => $request->password]);
            throw new \Exception('Mot de passe incorrect', 401);
        }
    }

    public function index()
    {
        try {
            $roles = Role::with(['creerPar', 'desactivePar'])->get()->toArray();
            Log::info('Rôles récupérés:', ['count' => count($roles)]);
            return response()->json(['status' => 'success', 'message' => 'Rôles récupérés', 'data' => $roles], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Erreur récupération rôles : ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Échec récupération rôles', 'error' => $e->getMessage(), 'data' => []], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'name_ar' => 'required|string|max:255|unique:roles,name_ar',
                'is_centre_role' => 'required|boolean',
                'guard_name' => 'nullable|string|max:255',
                'statut' => 'nullable|string|in:actif,inactif', // Validation pour statut
            ]);

            $data['guard_name'] = $data['guard_name'] ?? '';
            $data['creer_par'] = Auth::id(); // Récupérer l'ID de l'utilisateur connecté
            $data['statut'] = $data['statut'] ?? 'actif'; // Par défaut actif
            $role = Role::create($data);
            Log::info('Rôle créé:', $role->toArray());
            return response()->json(['status' => 'success', 'message' => 'Rôle créé', 'role' => $role->toArray()], 201, [], JSON_UNESCAPED_UNICODE);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de la création de rôle:', $e->errors());
            return response()->json(['status' => 'error', 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur création rôle : ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Échec création rôle', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $role = Role::findOrFail($id);
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $id,
                'name_ar' => 'required|string|max:255|unique:roles,name_ar,' . $id,
                'is_centre_role' => 'required|boolean',
                'guard_name' => 'nullable|string|max:255',
                'statut' => 'nullable|string|in:actif,inactif',
            ]);

            $data['guard_name'] = $data['guard_name'] ?? '';
            if ($data['statut'] === 'inactif' && $role->statut !== 'inactif') {
                $data['desactive_par'] = Auth::id(); // Remplir desactive_par si le statut passe à inactif
            }
            $role->update($data);
            Log::info('Rôle mis à jour:', $role->toArray());
            return response()->json(['status' => 'success', 'message' => 'Rôle mis à jour', 'role' => $role->toArray()], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de la mise à jour de rôle:', $e->errors());
            return response()->json(['status' => 'error', 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour rôle : ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Échec mise à jour rôle', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->verifyDeletePassword($request);
            $role = Role::findOrFail($id);

            // Vérifier si des utilisateurs sont associés au rôle
            $userCount = DB::table('model_has_roles')
                ->where('role_id', $id)
                ->where('model_type', 'App\\Models\\User')
                ->count();

            if ($userCount > 0) {
                Log::warning('Tentative de suppression de rôle avec utilisateurs associés', ['role_id' => $id, 'user_count' => $userCount]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Impossible de supprimer ce rôle car il y a des utilisateurs qui sont associés à ce rôle.',
                ], 403);
            }

            $role->permissions()->detach(); // Detach all permissions to avoid relationship errors
            $role->delete();
            Log::info('Rôle supprimé:', ['id' => $id]);
            return response()->json(['status' => 'success', 'message' => 'Rôle supprimé avec succès'], 200);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de la suppression de rôle:', $e->errors());
            return response()->json(['status' => 'error', 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur suppression rôle : ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Échec suppression rôle', 'error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function importXLS(Request $request)
    {
        try {
            $request->validate(['file' => 'required|file|mimes:xls,xlsx|max:2048']);
            $import = new RolesImport;
            Excel::import($import, $request->file('file'));
            $errorLines = $import->getErrors();
            Log::info('Importation XLS terminée:', ['error_lines' => count($errorLines)]);
            return response()->json([
                'status' => 'success',
                'message' => $errorLines ? 'Importation terminée avec erreurs' : 'Importation réussie',
                'data' => ['error_lines' => $errorLines]
            ], 200);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de l\'importation XLS:', $e->errors());
            return response()->json(['status' => 'error', 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur importation XLS : ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Échec importation XLS', 'error' => $e->getMessage(), 'data' => ['error_lines' => []]], 500);
        }
    }

    public function trashed()
    {
        try {
            $trashedRoles = Role::onlyTrashed()->get()->toArray();
            Log::info('Rôles supprimés récupérés:', ['count' => count($trashedRoles)]);
            return response()->json(['status' => 'success', 'message' => 'Rôles supprimés récupérés', 'data' => $trashedRoles], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Erreur récupération rôles supprimés : ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Échec récupération rôles supprimés', 'error' => $e->getMessage(), 'data' => []], 500);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $this->verifyDeletePassword($request);
            $role = Role::onlyTrashed()->findOrFail($id);
            $role->restore();
            Log::info('Rôle restauré:', ['id' => $id]);
            return response()->json(['status' => 'success', 'message' => 'Rôle restauré', 'role' => $role->toArray()], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (ValidationException $e) {
            Log::error('Erreur de validation lors de la restauration de rôle:', $e->errors());
            return response()->json(['status' => 'error', 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur restauration rôle : ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Échec restauration rôle', 'error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
