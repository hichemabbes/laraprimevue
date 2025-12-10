<?php

namespace App\Http\Controllers\brillants\Centres\Utilisateurs\Personnels;

use App\Http\Controllers\Controller;
use App\Models\Annees\AnneeFormation;
use App\Models\Centre\Centre;
use App\Models\Centre\Utilisateurs\Personnels\ContratPersonnel;
use App\Models\Centre\Utilisateurs\Personnels\DocumentPersonnel;
use App\Models\Centre\Utilisateurs\Personnels\GradePersonnel;
use App\Models\Centre\Utilisateurs\Personnels\RegimePersonnel;
use App\Models\Centre\Utilisateurs\Personnels\UserCentre;
use App\Models\Role;
use App\Models\User;
use App\Models\Utilisateurs\PersonnelsCentres\PersonnelCentre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfilPersonnelController extends Controller
{
    /**
     * Get reference data (centres, roles, training years, affectation types).
     */
    public function getReferenceData(Request $request)
    {
        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $centres = Centre::select('id', 'nom_fr', 'nom_ar')->get();
            $roles = Role::select('id', 'name')->get();
            $annees_formations = AnneeFormation::select('id', 'intitule as nom_fr', 'intitule as nom_ar')->get();
            $types_affectation = [
                ['id' => 1, 'nom_fr' => 'Permanent', 'nom_ar' => 'دائم'],
                ['id' => 2, 'nom_fr' => 'Intérimaire', 'nom_ar' => 'مؤقت'],
                ['id' => 3, 'nom_fr' => 'Vacataire', 'nom_ar' => 'متفرغ'],
            ];

            if ($isCentreRole && $centreId) {
                $centres = Centre::where('id', $centreId)->select('id', 'nom_fr', 'nom_ar')->get();
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'centres' => $centres,
                    'roles' => $roles,
                    'annees_formations' => $annees_formations,
                    'types_affectation' => $types_affectation,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des données de référence : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retrieve personnel details by ID.
     */
    public function show($id, Request $request)
    {
        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $query = PersonnelCentre::with([
                'user',
                'userCentre.centre',
                'contrats' => function ($query) {
                    $query->select('id', 'personnel_id', 'type_contrat_fr', 'type_contrat_ar', 'num_contrat', 'num_decision', 'date_debut', 'date_fin', 'statut', 'observation');
                },
                'documents' => function ($query) {
                    $query->select('id', 'personnel_id', 'type_document_personnel_fr', 'type_document_personnel_ar', 'chemin_fichier', 'date_depot', 'validite_fr', 'validite_ar', 'depot_fr', 'depot_ar', 'description', 'observation', 'statut');
                },
                'regimesPersonnels' => function ($query) {
                    $query->select('id', 'personnel_id', 'annee_formation_id', 'regime_horaire', 'rabattement', 'statut', 'observation');
                },
                'fonctionsPersonnels' => function ($query) {
                    $query->select('id', 'personnel_id', 'fonction_fr', 'fonction_ar', 'avantage_fonction_fr', 'avantage_fonction_ar', 'taches_fr', 'taches_ar', 'date_debut', 'date_fin', 'statut_fr', 'statut_ar', 'observation');
                },
            ])->where('id', $id);

            if ($isCentreRole && $centreId) {
                $query->whereHas('userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $personnel = $query->first();

            if (!$personnel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Personnel non trouvé ou accès non autorisé.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $this->formatPersonnelData($personnel),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération du personnel : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update personnel information.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nom_fr' => 'required|string|max:255',
            'prenom_fr' => 'required|string|max:255',
            'nom_ar' => 'nullable|string|max:255',
            'prenom_ar' => 'nullable|string|max:255',
            'matricule' => 'nullable|string|max:50|unique:personnels_centres,matricule,' . $id,
            'cin' => 'nullable|string|max:20|unique:personnels_centres,cin,' . $id,
            'date_cin' => 'nullable|date',
            'date_naissance' => 'nullable|date',
            'nationalite_fr' => 'nullable|string|max:100',
            'nationalite_ar' => 'nullable|string|max:100',
            'genre_fr' => 'nullable|in:Homme,Femme,Autre',
            'genre_ar' => 'nullable|string|max:50',
            'statut' => 'nullable|in:Actif,Inactif,Suspendu',
            'qualite_fr' => 'nullable|string|max:255',
            'qualite_ar' => 'nullable|string|max:255',
            'etablissement_origine_fr' => 'nullable|string|max:255',
            'etablissement_origine_ar' => 'nullable|string|max:255',
            'mission_fr' => 'nullable|string|max:255',
            'mission_ar' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . ($this->getUserId($id) ?? 'NULL') . ',id',
            'centre_id' => 'required|exists:centres,id',
            'role_id' => 'required|exists:roles,id',
            'type_affectation_fr' => 'nullable|string|max:100',
            'type_affectation_ar' => 'nullable|string|max:100',
            'centre_origine_fr' => 'nullable|string|max:255',
            'centre_origine_ar' => 'nullable|string|max:255',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'etat_civil_fr' => 'nullable|in:Célibataire,Marié,Divorcé,Veuf',
            'etat_civil_ar' => 'nullable|string|max:50',
            'nombre_enfants' => 'nullable|integer|min:0',
            'niveau_etude_fr' => 'nullable|string|max:255',
            'niveau_etude_ar' => 'nullable|string|max:255',
            'specialite_diplome_fr' => 'nullable|string|max:255',
            'specialite_diplome_ar' => 'nullable|string|max:255',
            'code_niveau' => 'nullable|string|max:50',
            'date_recrutement' => 'nullable|date',
            'date_fin_service' => 'nullable|date',
            'adresse_fr' => 'nullable|string|max:255',
            'adresse_ar' => 'nullable|string|max:255',
            'gouvernorat_fr' => 'nullable|string|max:100',
            'gouvernorat_ar' => 'nullable|string|max:100',
            'delegation_fr' => 'nullable|string|max:100',
            'delegation_ar' => 'nullable|string|max:100',
            'telephone_1' => 'nullable|string|max:20',
            'telephone_2' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $personnel = PersonnelCentre::where('id', $id);
            if ($isCentreRole && $centreId) {
                $personnel->whereHas('userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $personnel = $personnel->first();

            if (!$personnel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Personnel non trouvé ou accès non autorisé.',
                ], 404);
            }

            // Update personnel data
            $personnel->update([
                'nom_fr' => $request->nom_fr,
                'prenom_fr' => $request->prenom_fr,
                'nom_ar' => $request->nom_ar,
                'prenom_ar' => $request->prenom_ar,
                'matricule' => $request->matricule,
                'cin' => $request->cin,
                'date_cin' => $request->date_cin,
                'date_naissance' => $request->date_naissance,
                'nationalite_fr' => $request->nationalite_fr,
                'nationalite_ar' => $request->nationalite_ar,
                'genre_fr' => $request->genre_fr,
                'genre_ar' => $request->genre_ar,
                'statut' => $request->statut,
                'qualite_fr' => $request->qualite_fr,
                'qualite_ar' => $request->qualite_ar,
                'etablissement_origine_fr' => $request->etablissement_origine_fr,
                'etablissement_origine_ar' => $request->etablissement_origine_ar,
                'mission_fr' => $request->mission_fr,
                'mission_ar' => $request->mission_ar,
                'type_affectation_fr' => $request->type_affectation_fr,
                'type_affectation_ar' => $request->type_affectation_ar,
                'centre_origine_fr' => $request->centre_origine_fr,
                'centre_origine_ar' => $request->centre_origine_ar,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'etat_civil_fr' => $request->etat_civil_fr,
                'etat_civil_ar' => $request->etat_civil_ar,
                'nombre_enfants' => $request->nombre_enfants,
                'niveau_etude_fr' => $request->niveau_etude_fr,
                'niveau_etude_ar' => $request->niveau_etude_ar,
                'specialite_diplome_fr' => $request->specialite_diplome_fr,
                'specialite_diplome_ar' => $request->specialite_diplome_ar,
                'code_niveau' => $request->code_niveau,
                'date_recrutement' => $request->date_recrutement,
                'date_fin_service' => $request->date_fin_service,
                'adresse_fr' => $request->adresse_fr,
                'adresse_ar' => $request->adresse_ar,
                'gouvernorat_fr' => $request->gouvernorat_fr,
                'gouvernorat_ar' => $request->gouvernorat_ar,
                'delegation_fr' => $request->delegation_fr,
                'delegation_ar' => $request->delegation_ar,
                'telephone_1' => $request->telephone_1,
                'telephone_2' => $request->telephone_2,
            ]);

            // Update associated user data
            $user = User::find($this->getUserId($id));
            if ($user) {
                $user->update([
                    'email' => $request->email,
                ]);
            }

            // Update or create UserCentre association
            UserCentre::updateOrCreate(
                ['user_id' => $this->getUserId($id), 'centre_id' => $request->centre_id],
                ['role_id' => $request->role_id]
            );

            return response()->json([
                'status' => 'success',
                'data' => $this->formatPersonnelData($personnel->fresh(['user', 'userCentre.centre', 'contrats', 'documents', 'regimesPersonnels', 'fonctionsPersonnels'])),
                'message' => 'Personnel mis à jour avec succès.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du personnel : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload personnel photo.
     */
    public function uploadPhoto(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $personnel = PersonnelCentre::where('id', $id);
            if ($isCentreRole && $centreId) {
                $personnel->whereHas('userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $personnel = $personnel->first();

            if (!$personnel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Personnel non trouvé ou accès non autorisé.',
                ], 404);
            }

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = Str::uuid() . 'PersonnelsDirectionCentrale' . $file->getClientOriginalExtension();
                $path = $file->storeAs('photos/personnel', $filename, 'public');
                if ($personnel->photo) {
                    Storage::disk('public')->delete($personnel->photo);
                }
                $personnel->update(['photo' => $path]);
            }

            return response()->json([
                'status' => 'success',
                'photo' => Storage::url($personnel->photo),
                'message' => 'Photo mise à jour avec succès.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du téléversement de la photo : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new contract for a personnel.
     */
    public function storeContract(Request $request, $personnelId)
    {
        $validator = Validator::make($request->all(), [
            'type_contrat_fr' => 'required|string|max:255',
            'type_contrat_ar' => 'nullable|string|max:255',
            'num_contrat' => 'required|string|max:50',
            'num_decision' => 'nullable|string|max:50',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'statut' => 'required|in:Actif,Terminé,Resilié',
            'observation' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $personnel = PersonnelCentre::where('id', $personnelId);
            if ($isCentreRole && $centreId) {
                $personnel->whereHas('userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $personnel = $personnel->first();

            if (!$personnel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Personnel non trouvé ou accès non autorisé.',
                ], 404);
            }

            $contract = ContratPersonnel::create([
                'personnel_id' => $personnelId,
                'type_contrat_fr' => $request->type_contrat_fr,
                'type_contrat_ar' => $request->type_contrat_ar,
                'num_contrat' => $request->num_contrat,
                'num_decision' => $request->num_decision,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'statut' => $request->statut,
                'observation' => $request->observation,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $contract,
                'message' => 'Contrat ajouté avec succès.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'ajout du contrat : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing contract.
     */
    public function updateContract(Request $request, $personnelId, $contractId)
    {
        $validator = Validator::make($request->all(), [
            'type_contrat_fr' => 'required|string|max:255',
            'type_contrat_ar' => 'nullable|string|max:255',
            'num_contrat' => 'required|string|max:50',
            'num_decision' => 'nullable|string|max:50',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'statut' => 'required|in:Actif,Terminé,Resilié',
            'observation' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $contract = ContratPersonnel::where('id', $contractId)
                ->where('personnel_id', $personnelId);
            if ($isCentreRole && $centreId) {
                $contract->whereHas('personnel.userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $contract = $contract->first();

            if (!$contract) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Contrat non trouvé ou accès non autorisé.',
                ], 404);
            }

            $contract->update([
                'type_contrat_fr' => $request->type_contrat_fr,
                'type_contrat_ar' => $request->type_contrat_ar,
                'num_contrat' => $request->num_contrat,
                'num_decision' => $request->num_decision,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'statut' => $request->statut,
                'observation' => $request->observation,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $contract,
                'message' => 'Contrat mis à jour avec succès.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du contrat : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a contract.
     */
    public function deleteContract($personnelId, $contractId, Request $request)
    {
        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $contract = ContratPersonnel::where('id', $contractId)
                ->where('personnel_id', $personnelId);
            if ($isCentreRole && $centreId) {
                $contract->whereHas('personnel.userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $contract = $contract->first();

            if (!$contract) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Contrat non trouvé ou accès non autorisé.',
                ], 404);
            }

            $contract->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Contrat supprimé avec succès.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression du contrat : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new document.
     */
    public function storeDocument(Request $request, $personnelId)
    {
        $validator = Validator::make($request->all(), [
            'type_document_personnel_fr' => 'required|string|max:255',
            'type_document_personnel_ar' => 'nullable|string|max:255',
            'date_depot' => 'required|date',
            'validite_fr' => 'nullable|string|max:255',
            'validite_ar' => 'nullable|string|max:255',
            'depot_fr' => 'nullable|string|max:255',
            'depot_ar' => 'nullable|string|max:255',
            'observation' => 'nullable|string',
            'chemin_fichier' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png,jpg|max:10240',
            'statut' => 'nullable|in:Valide,En attente,Rejeté',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $personnel = PersonnelCentre::where('id', $personnelId);
            if ($isCentreRole && $centreId) {
                $personnel->whereHas('userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $personnel = $personnel->first();

            if (!$personnel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Personnel non trouvé ou accès non autorisé.',
                ], 404);
            }

            $data = $request->only([
                'type_document_personnel_fr',
                'type_document_personnel_ar',
                'date_depot',
                'validite_fr',
                'validite_ar',
                'depot_fr',
                'depot_ar',
                'observation',
                'statut',
            ]);
            $data['personnel_id'] = $personnelId;

            if ($request->hasFile('chemin_fichier')) {
                $file = $request->file('chemin_fichier');
                $filename = Str::uuid() . 'PersonnelsDirectionCentrale' . $file->getClientOriginalExtension();
                $path = $file->storeAs('documents/personnel', $filename, 'public');
                $data['chemin_fichier'] = $path;
            }

            $document = DocumentPersonnel::create($data);

            return response()->json([
                'status' => 'success',
                'data' => $document,
                'message' => 'Document ajouté avec succès.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'ajout du document : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing document.
     */
    public function updateDocument(Request $request, $personnelId, $documentId)
    {
        $validator = Validator::make($request->all(), [
            'type_document_personnel_fr' => 'required|string|max:255',
            'type_document_personnel_ar' => 'nullable|string|max:255',
            'date_depot' => 'required|date',
            'validite_fr' => 'nullable|string|max:255',
            'validite_ar' => 'nullable|string|max:255',
            'depot_fr' => 'nullable|string|max:255',
            'depot_ar' => 'nullable|string|max:255',
            'observation' => 'nullable|string',
            'chemin_fichier' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png,jpg|max:10240',
            'statut' => 'nullable|in:Valide,En attente,Rejeté',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $document = DocumentPersonnel::where('id', $documentId)
                ->where('personnel_id', $personnelId);
            if ($isCentreRole && $centreId) {
                $document->whereHas('personnel.userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $document = $document->first();

            if (!$document) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Document non trouvé ou accès non autorisé.',
                ], 404);
            }

            $data = $request->only([
                'type_document_personnel_fr',
                'type_document_personnel_ar',
                'date_depot',
                'validite_fr',
                'validite_ar',
                'depot_fr',
                'depot_ar',
                'observation',
                'statut',
            ]);

            if ($request->hasFile('chemin_fichier')) {
                $file = $request->file('chemin_fichier');
                $filename = Str::uuid() . 'PersonnelsDirectionCentrale' . $file->getClientOriginalExtension();
                $path = $file->storeAs('documents/personnel', $filename, 'public');
                if ($document->chemin_fichier) {
                    Storage::disk('public')->delete($document->chemin_fichier);
                }
                $data['chemin_fichier'] = $path;
            }

            $document->update($data);

            return response()->json([
                'status' => 'success',
                'data' => $document,
                'message' => 'Document mis à jour avec succès.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du document : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a document.
     */
    public function deleteDocument($personnelId, $documentId, Request $request)
    {
        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $document = DocumentPersonnel::where('id', $documentId)
                ->where('personnel_id', $personnelId);
            if ($isCentreRole && $centreId) {
                $document->whereHas('personnel.userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $document = $document->first();

            if (!$document) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Document non trouvé ou accès non autorisé.',
                ], 404);
            }

            if ($document->chemin_fichier) {
                Storage::disk('public')->delete($document->chemin_fichier);
            }

            $document->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Document supprimé avec succès.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression du document : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a document.
     */
    public function downloadDocument($cheminFichier, Request $request)
    {
        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $document = DocumentPersonnel::where('chemin_fichier', $cheminFichier);
            if ($isCentreRole && $centreId) {
                $document->whereHas('personnel.userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $document = $document->first();

            if (!$document || !Storage::disk('public')->exists($cheminFichier)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Document non trouvé ou accès non autorisé.',
                ], 404);
            }

            return Storage::disk('public')->download($cheminFichier);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du téléchargement du document : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new regime.
     */
    public function storeRegime(Request $request, $personnelId)
    {
        $validator = Validator::make($request->all(), [
            'annee_formation_id' => 'required|exists:annees_formations,id',
            'regime_horaire' => 'required|numeric|min:0',
            'rabattement' => 'nullable|numeric|min:0',
            'statut' => 'required|in:Actif,Inactif',
            'observation' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $personnel = PersonnelCentre::where('id', $personnelId);
            if ($isCentreRole && $centreId) {
                $personnel->whereHas('userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $personnel = $personnel->first();

            if (!$personnel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Personnel non trouvé ou accès non autorisé.',
                ], 404);
            }

            $regime = RegimePersonnel::create([
                'personnel_id' => $personnelId,
                'annee_formation_id' => $request->annee_formation_id,
                'regime_horaire' => $request->regime_horaire,
                'rabattement' => $request->rabattement,
                'statut' => $request->statut,
                'observation' => $request->observation,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $regime,
                'message' => 'Régime ajouté avec succès.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'ajout du régime : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing regime.
     */
    public function updateRegime(Request $request, $personnelId, $regimeId)
    {
        $validator = Validator::make($request->all(), [
            'annee_formation_id' => 'required|exists:annees_formations,id',
            'regime_horaire' => 'required|numeric|min:0',
            'rabattement' => 'nullable|numeric|min:0',
            'statut' => 'required|in:Actif,Inactif',
            'observation' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $regime = RegimePersonnel::where('id', $regimeId)
                ->where('personnel_id', $personnelId);
            if ($isCentreRole && $centreId) {
                $regime->whereHas('personnel.userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $regime = $regime->first();

            if (!$regime) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Régime non trouvé ou accès non autorisé.',
                ], 404);
            }

            $regime->update([
                'annee_formation_id' => $request->annee_formation_id,
                'regime_horaire' => $request->regime_horaire,
                'rabattement' => $request->rabattement,
                'statut' => $request->statut,
                'observation' => $request->observation,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $regime,
                'message' => 'Régime mis à jour avec succès.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du régime : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a regime.
     */
    public function deleteRegime($personnelId, $regimeId, Request $request)
    {
        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $regime = RegimePersonnel::where('id', $regimeId)
                ->where('personnel_id', $personnelId);
            if ($isCentreRole && $centreId) {
                $regime->whereHas('personnel.userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $regime = $regime->first();

            if (!$regime) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Régime non trouvé ou accès non autorisé.',
                ], 404);
            }

            $regime->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Régime supprimé avec succès.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression du régime : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new function.
     */
    public function storeFunction(Request $request, $personnelId)
    {
        $validator = Validator::make($request->all(), [
            'fonction_fr' => 'required|string|max:255',
            'fonction_ar' => 'nullable|string|max:255',
            'avantage_fonction_fr' => 'nullable|string|max:255',
            'avantage_fonction_ar' => 'nullable|string|max:255',
            'taches_fr' => 'nullable|string',
            'taches_ar' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'statut_fr' => 'required|in:Active,Inactive',
            'statut_ar' => 'nullable|string|max:50',
            'observation' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $personnel = PersonnelCentre::where('id', $personnelId);
            if ($isCentreRole && $centreId) {
                $personnel->whereHas('userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $personnel = $personnel->first();

            if (!$personnel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Personnel non trouvé ou accès non autorisé.',
                ], 404);
            }

            $function = GradePersonnel::create([
                'personnel_id' => $personnelId,
                'fonction_fr' => $request->fonction_fr,
                'fonction_ar' => $request->fonction_ar,
                'avantage_fonction_fr' => $request->avantage_fonction_fr,
                'avantage_fonction_ar' => $request->avantage_fonction_ar,
                'taches_fr' => $request->taches_fr,
                'taches_ar' => $request->taches_ar,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'statut_fr' => $request->statut_fr,
                'statut_ar' => $request->statut_ar,
                'observation' => $request->observation,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $function,
                'message' => 'Fonction ajoutée avec succès.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'ajout de la fonction : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing function.
     */
    public function updateFunction(Request $request, $personnelId, $functionId)
    {
        $validator = Validator::make($request->all(), [
            'fonction_fr' => 'required|string|max:255',
            'fonction_ar' => 'nullable|string|max:255',
            'avantage_fonction_fr' => 'nullable|string|max:255',
            'avantage_fonction_ar' => 'nullable|string|max:255',
            'taches_fr' => 'nullable|string',
            'taches_ar' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'statut_fr' => 'required|in:Active,Inactive',
            'statut_ar' => 'nullable|string|max:50',
            'observation' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $function = GradePersonnel::where('id', $functionId)
                ->where('personnel_id', $personnelId);
            if ($isCentreRole && $centreId) {
                $function->whereHas('personnel.userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $function = $function->first();

            if (!$function) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Fonction non trouvée ou accès non autorisé.',
                ], 404);
            }

            $function->update([
                'fonction_fr' => $request->fonction_fr,
                'fonction_ar' => $request->fonction_ar,
                'avantage_fonction_fr' => $request->avantage_fonction_fr,
                'avantage_fonction_ar' => $request->avantage_fonction_ar,
                'taches_fr' => $request->taches_fr,
                'taches_ar' => $request->taches_ar,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'statut_fr' => $request->statut_fr,
                'statut_ar' => $request->statut_ar,
                'observation' => $request->observation,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $function,
                'message' => 'Fonction mise à jour avec succès.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de la fonction : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a function.
     */
    public function deleteFunction($personnelId, $functionId, Request $request)
    {
        try {
            $isCentreRole = $request->header('X-Is-Centre-Role') === '1';
            $centreId = $request->header('X-Centre-Id');

            $function = GradePersonnel::where('id', $functionId)
                ->where('personnel_id', $personnelId);
            if ($isCentreRole && $centreId) {
                $function->whereHas('personnel.userCentre', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $function = $function->first();

            if (!$function) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Fonction non trouvée ou accès non autorisé.',
                ], 404);
            }

            $function->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Fonction supprimée avec succès.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression de la fonction : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method to get user_id from personnel_id.
     */
    protected function getUserId($personnelId)
    {
        return PersonnelCentre::where('id', $personnelId)->value('user_id');
    }

    /**
     * Format personnel data for API response.
     */
    protected function formatPersonnelData($personnel)
    {
        return [
            'id' => $personnel->id,
            'nom_fr' => $personnel->nom_fr,
            'prenom_fr' => $personnel->prenom_fr,
            'nom_ar' => $personnel->nom_ar,
            'prenom_ar' => $personnel->prenom_ar,
            'matricule' => $personnel->matricule,
            'cin' => $personnel->cin,
            'date_cin' => $personnel->date_cin ? $personnel->date_cin->format('Y-m-d') : null,
            'date_naissance' => $personnel->date_naissance ? $personnel->date_naissance->format('Y-m-d') : null,
            'nationalite_fr' => $personnel->nationalite_fr,
            'nationalite_ar' => $personnel->nationalite_ar,
            'genre_fr' => $personnel->genre_fr,
            'genre_ar' => $personnel->genre_ar,
            'statut' => $personnel->statut,
            'qualite_fr' => $personnel->qualite_fr,
            'qualite_ar' => $personnel->qualite_ar,
            'etablissement_origine_fr' => $personnel->etablissement_origine_fr,
            'etablissement_origine_ar' => $personnel->etablissement_origine_ar,
            'mission_fr' => $personnel->mission_fr,
            'mission_ar' => $personnel->mission_ar,
            'photo' => $personnel->photo ? Storage::url($personnel->photo) : null,
            'email' => $personnel->user ? $personnel->user->email : null,
            'centre_id' => $personnel->userCentre ? $personnel->userCentre->centre_id : null,
            'role_id' => $personnel->userCentre ? $personnel->userCentre->role_id : null,
            'type_affectation_fr' => $personnel->type_affectation_fr,
            'type_affectation_ar' => $personnel->type_affectation_ar,
            'centre_origine_fr' => $personnel->centre_origine_fr,
            'centre_origine_ar' => $personnel->centre_origine_ar,
            'date_debut' => $personnel->date_debut ? $personnel->date_debut->format('Y-m-d') : null,
            'date_fin' => $personnel->date_fin ? $personnel->date_fin->format('Y-m-d') : null,
            'etat_civil_fr' => $personnel->etat_civil_fr,
            'etat_civil_ar' => $personnel->etat_civil_ar,
            'nombre_enfants' => $personnel->nombre_enfants,
            'niveau_etude_fr' => $personnel->niveau_etude_fr,
            'niveau_etude_ar' => $personnel->niveau_etude_ar,
            'specialite_diplome_fr' => $personnel->specialite_diplome_fr,
            'specialite_diplome_ar' => $personnel->specialite_diplome_ar,
            'code_niveau' => $personnel->code_niveau,
            'date_recrutement' => $personnel->date_recrutement ? $personnel->date_recrutement->format('Y-m-d') : null,
            'date_fin_service' => $personnel->date_fin_service ? $personnel->date_fin_service->format('Y-m-d') : null,
            'adresse_fr' => $personnel->adresse_fr,
            'adresse_ar' => $personnel->adresse_ar,
            'gouvernorat_fr' => $personnel->gouvernorat_fr,
            'gouvernorat_ar' => $personnel->gouvernorat_ar,
            'delegation_fr' => $personnel->delegation_fr,
            'delegation_ar' => $personnel->delegation_ar,
            'telephone_1' => $personnel->telephone_1,
            'telephone_2' => $personnel->telephone_2,
            'contrats_personnels' => $personnel->contrats,
            'documents_personnels' => $personnel->documents,
            'regimes_personnels' => $personnel->regimesPersonnels,
            'fonctions_personnels' => $personnel->fonctionsPersonnels,
        ];
    }
}
