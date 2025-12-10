<?php

namespace App\Http\Controllers\Centre;

use App\Exports\CentresExport;
use App\Http\Controllers\Controller;
use App\Models\Centre\Centre;
use App\Models\DeletePassword;
use App\Models\Liste;
use App\Services\Import\CentreImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class CentreController extends Controller
{
    public function indexWithOptions(Request $request)
    {
        Log::info('indexWithOptions appelé', [
            'per_page' => $request->input('per_page', 100),
            'user' => auth()->user() ? auth()->user()->id : 'non authentifié',
        ]);
        $perPage = $request->input('per_page', 100);
        $centres = Centre::paginate($perPage);

        $lists = Liste::whereIn('nom_fr', [
            'Gouvernorats',
            'Types Centres',
            'Classes Centres',
            'Economats',
            'Etats Centres',
            'Statuts Centres',
        ])->where('statut', 'Actif')->get()->pluck('options', 'nom_fr');

        Log::info('Données renvoyées', [
            'centres_count' => $centres->count(),
            'lists' => $lists->toArray(),
        ]);

        return response()->json([
            'centres' => $centres,
            'lists' => $lists,
        ]);
    }

    public function showWithOptions($id)
    {
        Log::info('showWithOptions appelé pour centre ID:', ['id' => $id]);
        $centre = Centre::withTrashed()->findOrFail($id);
        $lists = Liste::whereIn('nom_fr', [
            'Gouvernorats',
            'Types Centres',
            'Classes Centres',
            'Economats',
            'Etats Centres',
            'Statuts Centres',
        ])->where('statut', 'Actif')->get()->pluck('options', 'nom_fr');

        return response()->json([
            'centre' => $centre,
            'lists' => $lists,
        ]);
    }

    public function store(Request $request)
    {
        Log::info('store appelé avec données:', $request->all());

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20|unique:centres,code',
            'nom_fr' => 'required|string|max:255',
            'nom_ar' => 'nullable|string|max:255',
            'adresse_fr' => 'required|string|max:255',
            'adresse_ar' => 'nullable|string|max:255',
            'telephone_1' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'telephone_2' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'fax_1' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'fax_2' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'email' => 'nullable|email|max:255',
            'gouvernorat_fr' => 'required|string|max:255',
            'type_centre_fr' => 'nullable|string|max:255',
            'classe_fr' => 'nullable|string|max:255',
            'economat_fr' => 'nullable|string|max:255',
            'etat_fr' => 'nullable|string|max:255',
            'statut_fr' => 'required|string|max:255',
            'date_creation' => 'nullable|date',
            'date_ouverture' => 'nullable|date|after_or_equal:date_creation',
            'date_fermeture' => 'nullable|date|after_or_equal:date_ouverture',
            'observation_fr' => 'nullable|string',
            'observation_ar' => 'nullable|string',
            'logo' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (! preg_match('/^data:image\/(png|jpeg|jpg|svg\+xml);base64,/', $value)) {
                        $fail('Le logo doit être une chaîne Base64 valide pour une image PNG, JPG, JPEG ou SVG.');
                    } else {
                        $base64String = preg_replace('/^data:image\/(png|jpeg|jpg|svg\+xml);base64,/', '', $value);
                        $decoded = base64_decode($base64String, true);
                        if ($decoded === false) {
                            $fail('La chaîne Base64 du logo est invalide.');
                        } else {
                            $sizeInBytes = strlen($decoded);
                            if ($sizeInBytes > 2000000) {
                                $fail('Le logo ne doit pas dépasser 2 Mo.');
                            }
                        }
                    }
                },
            ],
        ], [
            'code.required' => 'Le code est obligatoire.',
            'code.max' => 'Le code ne doit pas dépasser 20 caractères.',
            'code.unique' => 'Ce code est déjà utilisé.',
            'nom_fr.required' => 'Le nom en français est obligatoire.',
            'nom_fr.max' => 'Le nom en français ne doit pas dépasser 255 caractères.',
            'adresse_fr.required' => 'L\'adresse en français est obligatoire.',
            'adresse_fr.max' => 'L\'adresse en français ne doit pas dépasser 255 caractères.',
            'telephone_1.regex' => 'Le numéro de téléphone 1 doit contenir entre 8 et 15 chiffres.',
            'telephone_2.regex' => 'Le numéro de téléphone 2 doit contenir entre 8 et 15 chiffres.',
            'fax_1.regex' => 'Le numéro de fax 1 doit contenir entre 8 et 15 chiffres.',
            'fax_2.regex' => 'Le numéro de fax 2 doit contenir entre 8 et 15 chiffres.',
            'email.email' => 'L\'adresse email doit être valide.',
            'gouvernorat_fr.required' => 'Le gouvernorat est obligatoire.',
            'statut_fr.required' => 'Le statut est obligatoire.',
            'date_ouverture.after_or_equal' => 'La date d\'ouverture doit être postérieure ou égale à la date de création.',
            'date_fermeture.after_or_equal' => 'La date de fermeture doit être postérieure ou égale à la date d\'ouverture.',
        ]);

        if ($validator->fails()) {
            Log::warning('Erreurs de validation dans store:', $validator->errors()->toArray());

            return response()->json(['message' => 'Erreurs de validation', 'errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if (isset($data['logo']) && ! empty($data['logo'])) {
            Log::info('Logo Base64 reçu:', ['length' => strlen($data['logo'])]);
        } else {
            unset($data['logo']);
        }

        $listFields = [
            'gouvernorat_fr' => 'Gouvernorats',
            'type_centre_fr' => 'Types Centres',
            'classe_fr' => 'Classes Centres',
            'economat_fr' => 'Economats',
            'etat_fr' => 'Etats Centres',
            'statut_fr' => 'Statuts Centres',
        ];

        foreach ($listFields as $field => $listName) {
            if (! empty($data[$field])) {
                $liste = Liste::where('nom_fr', $listName)->where('statut', 'Actif')->first();
                if ($liste && ! empty($liste->options)) {
                    $option = collect($liste->options)->firstWhere('nom_fr', $data[$field]);
                    if ($option) {
                        $data[$field] = $option['nom_fr'];
                        $data[$field.'_ar'] = $option['nom_ar'];
                    } else {
                        Log::warning("Valeur invalide pour le champ '$field':", ['value' => $data[$field]]);

                        return response()->json(['message' => "Valeur invalide pour $field", 'errors' => [$field => ["Valeur invalide pour $field"]]], 422);
                    }
                } else {
                    Log::warning("Liste '$listName' introuvable ou vide pour le champ '$field'.");

                    return response()->json(['message' => "Liste '$listName' non disponible", 'errors' => [$field => ["Liste '$listName' non disponible"]]], 422);
                }
            }
        }

        $centre = Centre::create($data);
        Log::info('Centre créé avec succès:', $centre->toArray());

        return response()->json($centre, 201);
    }

    public function update(Request $request, $id)
    {
        Log::info('update appelé pour centre ID:', ['id' => $id, 'data' => $request->all()]);

        $centre = Centre::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20', // Removed unique validation
            'nom_fr' => 'required|string|max:255',
            'nom_ar' => 'nullable|string|max:255',
            'adresse_fr' => 'required|string|max:255',
            'adresse_ar' => 'nullable|string|max:255',
            'telephone_1' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'telephone_2' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'fax_1' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'fax_2' => 'nullable|string|max:20|regex:/^\+?\d{8,15}$/',
            'email' => 'nullable|email|max:255',
            'gouvernorat_fr' => 'required|string|max:255',
            'type_centre_fr' => 'nullable|string|max:255',
            'classe_fr' => 'nullable|string|max:255',
            'economat_fr' => 'nullable|string|max:255',
            'etat_fr' => 'nullable|string|max:255',
            'statut_fr' => 'required|string|max:255',
            'date_creation' => 'nullable|date',
            'date_ouverture' => 'nullable|date|after_or_equal:date_creation',
            'date_fermeture' => 'nullable|date|after_or_equal:date_ouverture',
            'observation_fr' => 'nullable|string',
            'observation_ar' => 'nullable|string',
            'logo' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (! preg_match('/^data:image\/(png|jpeg|jpg|svg\+xml);base64,/', $value)) {
                        $fail('Le logo doit être une chaîne Base64 valide pour une image PNG, JPG, JPEG ou SVG.');
                    } else {
                        $base64String = preg_replace('/^data:image\/(png|jpeg|jpg|svg\+xml);base64,/', '', $value);
                        $decoded = base64_decode($base64String, true);
                        if ($decoded === false) {
                            $fail('La chaîne Base64 du logo est invalide.');
                        } else {
                            $sizeInBytes = strlen($decoded);
                            if ($sizeInBytes > 2000000) {
                                $fail('Le logo ne doit pas dépasser 2 Mo.');
                            }
                        }
                    }
                },
            ],
        ], [
            'code.required' => 'Le code est obligatoire.',
            'code.max' => 'Le code ne doit pas dépasser 20 caractères.',
            'nom_fr.required' => 'Le nom en français est obligatoire.',
            'nom_fr.max' => 'Le nom en français ne doit pas dépasser 255 caractères.',
            'adresse_fr.required' => 'L\'adresse en français est obligatoire.',
            'adresse_fr.max' => 'L\'adresse en français ne doit pas dépasser 255 caractères.',
            'telephone_1.regex' => 'Le numéro de téléphone 1 doit contenir entre 8 et 15 chiffres.',
            'telephone_2.regex' => 'Le numéro de téléphone 2 doit contenir entre 8 et 15 chiffres.',
            'fax_1.regex' => 'Le numéro de fax 1 doit contenir entre 8 et 15 chiffres.',
            'fax_2.regex' => 'Le numéro de fax 2 doit contenir entre 8 et 15 chiffres.',
            'email.email' => 'L\'adresse email doit être valide.',
            'gouvernorat_fr.required' => 'Le gouvernorat est obligatoire.',
            'statut_fr.required' => 'Le statut est obligatoire.',
            'date_ouverture.after_or_equal' => 'La date d\'ouverture doit être postérieure ou égale à la date de création.',
            'date_fermeture.after_or_equal' => 'La date de fermeture doit être postérieure ou égale à la date d\'ouverture.',
        ]);

        if ($validator->fails()) {
            Log::warning('Erreurs de validation dans update:', $validator->errors()->toArray());

            return response()->json(['message' => 'Erreurs de validation', 'errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        // Prevent updating the code field
        unset($data['code']);

        if (array_key_exists('logo', $data)) {
            if (! empty($data['logo'])) {
                Log::info('Logo Base64 mis à jour:', ['length' => strlen($data['logo'])]);
            } else {
                $data['logo'] = null;
            }
        }

        $listFields = [
            'gouvernorat_fr' => 'Gouvernorats',
            'type_centre_fr' => 'Types Centres',
            'classe_fr' => 'Classes Centres',
            'economat_fr' => 'Economats',
            'etat_fr' => 'Etats Centres',
            'statut_fr' => 'Statuts Centres',
        ];

        foreach ($listFields as $field => $listName) {
            if (! empty($data[$field])) {
                $liste = Liste::where('nom_fr', $listName)->where('statut', 'Actif')->first();
                if ($liste && ! empty($liste->options)) {
                    $option = collect($liste->options)->firstWhere('nom_fr', $data[$field]);
                    if ($option) {
                        $data[$field] = $option['nom_fr'];
                        $data[$field.'_ar'] = $option['nom_ar'];
                    } else {
                        Log::warning("Valeur invalide pour le champ '$field':", ['value' => $data[$field]]);

                        return response()->json(['message' => "Valeur invalide pour $field", 'errors' => [$field => ["Valeur invalide pour $field"]]], 422);
                    }
                } else {
                    Log::warning("Liste '$listName' introuvable ou vide pour le champ '$field'.");

                    return response()->json(['message' => "Liste '$listName' non disponible", 'errors' => [$field => ["Liste '$listName' non disponible"]]], 422);
                }
            }
        }

        $centre->update($data);
        Log::info('Centre mis à jour avec succès:', $centre->toArray());

        return response()->json($centre);
    }

    public function destroy(Request $request, $id)
    {
        Log::info('destroy appelé pour centre ID:', ['id' => $id, 'password_provided' => ! empty($request->input('password'))]);

        // Vérifier si l'utilisateur est authentifié
        $user = Auth::user();
        if (! $user) {
            Log::error('Aucun utilisateur authentifié pour la suppression du centre', ['centre_id' => $id]);

            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Vérifier si l'utilisateur est SuperAdmin
        if (! in_array('SuperAdmin', $user->roles->pluck('name')->toArray())) {
            Log::error('Utilisateur non autorisé pour la suppression du centre', ['centre_id' => $id, 'user_id' => $user->id]);

            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        // Valider la présence du mot de passe
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ], [
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        if ($validator->fails()) {
            Log::warning('Erreur de validation dans destroy:', $validator->errors()->toArray());

            return response()->json(['message' => 'Mot de passe requis', 'errors' => $validator->errors()], 422);
        }

        // Vérifier le mot de passe de suppression
        $password = $request->input('password');
        $deletePassword = DeletePassword::first();
        if (! $deletePassword) {
            Log::error('Aucun mot de passe de suppression configuré', ['centre_id' => $id]);

            return response()->json(['error' => 'Aucun mot de passe de suppression configuré'], 400);
        }

        if (! Hash::check($password, $deletePassword->password)) {
            Log::warning('Mot de passe de suppression incorrect', ['centre_id' => $id]);

            return response()->json(['message' => 'Mot de passe incorrect', 'error' => 'Mot de passe incorrect'], 401);
        }

        // Supprimer le centre
        $centre = Centre::findOrFail($id);
        $centre->delete();

        Log::info('Centre supprimé avec succès:', ['centre_id' => $id, 'user_id' => $user->id]);

        return response()->json(['message' => 'Centre supprimé avec succès']);
    }

    public function bulk(Request $request)
    {
        Log::info('bulk appelé avec action:', ['action' => $request->input('action')]);

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:import,export,import-line',
            'file' => 'required_if:action,import|file|mimes:xlsx,xls',
            'lineData' => 'required_if:action,import-line|array',
        ], [
            'action.required' => 'L\'action est obligatoire.',
            'action.in' => 'L\'action doit être import, export ou import-line.',
            'file.required_if' => 'Un fichier est requis pour l\'importation.',
            'file.mimes' => 'Le fichier doit être de type xlsx ou xls.',
            'lineData.required_if' => 'Les données de ligne sont requises pour l\'importation de ligne.',
        ]);

        if ($validator->fails()) {
            Log::warning('Erreurs de validation dans bulk:', $validator->errors()->toArray());

            return response()->json(['message' => 'Erreurs de validation', 'errors' => $validator->errors()], 422);
        }

        $action = $request->input('action');

        if ($action === 'import') {
            $file = $request->file('file');
            $importService = new CentreImportService;
            try {
                $result = $importService->import($file);
                Log::info('Importation terminée:', [
                    'success_count' => $result['success_count'],
                    'error_count' => $result['error_count'],
                    'error_lines' => $result['error_lines'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => empty($result['error_lines']) ? 'Import completed successfully.' : 'Import completed with errors.',
                    'success_count' => $result['success_count'],
                    'error_count' => $result['error_count'],
                    'error_lines' => $result['error_lines'],
                ], 200);
            } catch (\Exception $e) {
                Log::error('Error during centres import: '.$e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred during import: '.$e->getMessage(),
                ], 500);
            }
        } elseif ($action === 'import-line') {
            $lineData = $request->input('lineData');
            Log::info('Données reçues pour import-line:', ['lineData' => $lineData]);

            $requiredFields = ['code', 'nom_fr', 'adresse_fr', 'gouvernorat_fr', 'statut_fr'];
            $missingFields = array_filter($requiredFields, fn ($field) => ! isset($lineData[$field]) || empty(trim($lineData[$field])));
            if (! empty($missingFields)) {
                Log::warning('Champs requis manquants dans lineData:', ['missing' => $missingFields, 'lineData' => $lineData]);

                return response()->json([
                    'success' => false,
                    'message' => 'Champs requis manquants',
                    'errors' => ['lineData' => 'Les champs suivants sont requis : '.implode(', ', $missingFields)],
                ], 422);
            }

            $importService = new CentreImportService;
            try {
                $result = $importService->importRow($lineData);
                Log::info('Importation d\'une ligne terminée:', ['result' => $result]);

                return response()->json([
                    'success' => true,
                    'message' => 'Ligne importée avec succès.',
                    'data' => $result,
                ], 200);
            } catch (\Exception $e) {
                Log::error('Error during line import:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'importation de la ligne',
                    'error' => $e->getMessage(),
                ], 500);
            }
        } elseif ($action === 'export') {
            Log::info('Exportation des centres initiée');

            return Excel::download(new CentresExport, 'centres_export.xlsx');
        }
    }

    public function updateLogo(Request $request, $id)
    {
        Log::info('updateLogo appelé pour centre ID:', ['id' => $id]);

        $centre = Centre::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'logo' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (! preg_match('/^data:image\/(png|jpeg|jpg|svg\+xml);base64,/', $value)) {
                        $fail('Le logo doit être une chaîne Base64 valide pour une image PNG, JPG, JPEG ou SVG.');
                    } else {
                        $base64String = preg_replace('/^data:image\/(png|jpeg|jpg|svg\+xml);base64,/', '', $value);
                        $decoded = base64_decode($base64String, true);
                        if ($decoded === false) {
                            $fail('La chaîne Base64 du logo est invalide.');
                        } else {
                            $sizeInBytes = strlen($decoded);
                            if ($sizeInBytes > 2000000) {
                                $fail('Le logo ne doit pas dépasser 2 Mo.');
                            }
                        }
                    }
                },
            ],
        ], [
            'logo.required' => 'Le logo est obligatoire.',
        ]);

        if ($validator->fails()) {
            Log::warning('Erreurs de validation dans updateLogo:', $validator->errors()->toArray());

            return response()->json(['message' => 'Erreurs de validation', 'errors' => $validator->errors()], 422);
        }

        $centre->logo = $request->input('logo');
        $centre->save();

        Log::info('Logo mis à jour avec succès pour centre ID:', ['id' => $id]);

        return response()->json([
            'message' => 'Logo mis à jour avec succès',
            'logo' => $centre->logo,
        ], 200);
    }

    public function trashed()
    {
        try {
            $centres = Centre::onlyTrashed()->get();
            Log::info('Centres supprimés récupérés', ['count' => $centres->count()]);

            return response()->json($centres, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des centres supprimés : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la récupération des centres supprimés',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            if (! is_numeric($id) || (int) $id <= 0) {
                Log::warning('ID invalide pour la restauration du centre', ['id' => $id]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'ID de centre invalide',
                ], 400);
            }

            $centre = Centre::onlyTrashed()->find($id);
            if (! $centre) {
                Log::warning('Centre introuvable pour la restauration', ['id' => $id]);

                return response()->json(['message' => 'Centre introuvable'], 404);
            }

            $centre->restore();
            Log::info('Centre restauré avec succès', ['centre_id' => $id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Centre restauré',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la restauration du centre : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la restauration du centre',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function restoreAll(Request $request)
    {
        Log::info('restoreAll appelé', ['user' => auth()->user() ? auth()->user()->id : 'non authentifié']);

        $user = Auth::user();
        if (! $user) {
            Log::error('Aucun utilisateur authentifié pour la restauration de tous les centres');

            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        if (! in_array('SuperAdmin', $user->roles->pluck('name')->toArray())) {
            Log::error('Utilisateur non autorisé pour la restauration de tous les centres', ['user_id' => $user->id]);

            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        try {
            $restoredCount = Centre::onlyTrashed()->restore();
            Log::info('Tous les centres supprimés restaurés', ['count' => $restoredCount]);

            return response()->json(['message' => 'Tous les centres restaurés avec succès', 'count' => $restoredCount], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la restauration de tous les centres : '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la restauration des centres',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
