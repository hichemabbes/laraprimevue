<?php

namespace App\Services\Import;

use App\Models\User;
use App\Models\Liste;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;

class PersonnelsAtfpImportService
{
    protected $listsCache = [];

    public function import($file)
    {
        Log::info('Démarrage de l\'importation du fichier Excel');

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            $header = array_shift($rows);

            $result = [
                'success_count' => 0,
                'existing_count' => 0,
                'error_count' => 0,
                'error_lines' => [],
            ];

            foreach ($rows as $index => $row) {
                $lineNumber = $index + 2;
                try {
                    $data = $this->mapRowToUserData($row);
                    $importedUser = $this->importRow($data);

                    if ($importedUser === 'existing') {
                        $result['existing_count']++;
                    } else {
                        $result['success_count']++;
                    }
                } catch (ValidationException $e) {
                    $result['error_count']++;
                    $result['error_lines'][] = [
                        'line' => $lineNumber,
                        'data' => implode('|', array_map('strval', $row)),
                        'message' => 'Validation échouée',
                        'errors' => $e->errors(),
                    ];
                    Log::error("Erreur à la ligne $lineNumber: Validation échouée", ['errors' => $e->errors()]);
                } catch (\Exception $e) {
                    $result['error_count']++;
                    $result['error_lines'][] = [
                        'line' => $lineNumber,
                        'data' => implode('|', array_map('strval', $row)),
                        'message' => $e->getMessage(),
                        'errors' => null,
                    ];
                    Log::error("Erreur à la ligne $lineNumber: {$e->getMessage()}");
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Erreur globale lors de l'importation: {$e->getMessage()}");
            throw $e;
        }
    }

    protected function mapRowToUserData($row)
    {
        $dateFields = [6, 9, 14, 15]; // date_cin, date_naissance, date_recrutement, date_fin_service

        foreach ($dateFields as $index) {
            if (!empty($row[$index])) {
                // Gestion des numéros de série Excel
                if (is_numeric($row[$index]) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $row[$index])) {
                    $row[$index] = $this->excelSerialToDate($row[$index]);
                } elseif (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/', $row[$index], $matches)) {
                    // Essayer JJ/MM/AAAA
                    $formatted = sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
                    $date = \DateTime::createFromFormat('Y-m-d', $formatted);
                    if ($date && $date->format('Y-m-d') === $formatted) {
                        $row[$index] = $formatted;
                    } else {
                        // Essayer MM/JJ/AAAA
                        $formatted = sprintf('%04d-%02d-%02d', $matches[3], $matches[1], $matches[2]);
                        $date = \DateTime::createFromFormat('Y-m-d', $formatted);
                        if ($date && $date->format('Y-m-d') === $formatted) {
                            $row[$index] = $formatted;
                        } else {
                            $row[$index] = null;
                        }
                    }
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $row[$index])) {
                    // Vérifier la validité de la date AAAA-MM-JJ
                    $date = \DateTime::createFromFormat('Y-m-d', $row[$index]);
                    if (!$date || $date->format('Y-m-d') !== $row[$index]) {
                        $row[$index] = null;
                    }
                } else {
                    // Tentative de parsing avec DateTime pour autres formats
                    try {
                        $date = new \DateTime($row[$index]);
                        $row[$index] = $date->format('Y-m-d');
                    } catch (\Exception $e) {
                        $row[$index] = null;
                    }
                }
            } else {
                $row[$index] = null;
            }
        }

        return [
            'nom_fr' => $row[0] ?? null,
            'prenom_fr' => $row[1] ?? null,
            'nom_ar' => $row[2] ?? null,
            'prenom_ar' => $row[3] ?? null,
            'matricule' => $row[4] ?? null,
            'cin' => $row[5] ?? null,
            'date_cin' => $row[6] ?? null,
            'lieu_cin_fr' => $row[7] ?? null,
            'lieu_cin_ar' => $row[8] ?? null,
            'date_naissance' => $row[9] ?? null,
            'lieu_naissance_fr' => $row[10] ?? null,
            'lieu_naissance_ar' => $row[11] ?? null,
            'nationalite_fr' => $row[12] ?? 'Tunisienne',
            'nationalite_ar' => $row[13] ?? 'تونسية',
            'date_recrutement' => $row[14] ?? null,
            'date_fin_service' => $row[15] ?? null,
            'genre_fr' => $row[16] ?? null,
            'adresse_fr' => $row[17] ?? null,
            'adresse_ar' => $row[18] ?? null,
            'gouvernorat_fr' => $row[19] ?? null,
            'delegation_fr' => $row[20] ?? null,
            'delegation_ar' => $row[21] ?? null,
            'telephone_1' => $row[22] ? preg_replace('/[^+\d]/', '', $row[22]) : null,
            'telephone_2' => $row[23] ? preg_replace('/[^+\d]/', '', $row[23]) : null,
            'etat_civil_fr' => $row[24] ?? null,
            'email' => $row[25] ?? null,
            'roles' => !empty($row[26]) ? array_map('trim', explode(',', $row[26])) : [],
            'statut' => 'Actif',
        ];
    }

    protected function excelSerialToDate($serial)
    {
        if (!is_numeric($serial) || $serial <= 0) {
            return null;
        }
        $excelEpoch = new \DateTime('1899-12-30');
        $date = $excelEpoch->modify("+$serial days");
        return $date ? $date->format('Y-m-d') : null;
    }

    public function importRow($data)
    {
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

        $validator = Validator::make($data, [
            'nom_fr' => 'nullable|string|max:255',
            'prenom_fr' => 'nullable|string|max:255',
            'nom_ar' => 'nullable|string|max:255',
            'prenom_ar' => 'nullable|string|max:255',
            'cin' => 'nullable|string|size:8|regex:/^[A-Za-z0-9]{8}$/|unique:users,cin',
            'matricule' => 'nullable|string|max:20|unique:users,matricule',
            'date_cin' => [
                'nullable',
                'date_format:Y-m-d',
                'before:today',
                function ($attribute, $value, $fail) use ($data) {
                    if ($value && $data['date_naissance'] && $value < $data['date_naissance']) {
                        $fail('La date CIN doit être postérieure ou égale à la date de naissance.');
                    }
                },
            ],
            'lieu_cin_fr' => 'nullable|string|max:255',
            'lieu_cin_ar' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date_format:Y-m-d|before:today',
            'lieu_naissance_fr' => 'nullable|string|max:255',
            'lieu_naissance_ar' => 'nullable|string|max:255',
            'nationalite_fr' => 'nullable|string|max:255',
            'nationalite_ar' => 'nullable|string|max:255',
            'date_recrutement' => [
                'nullable',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'date_fin_service' => [
                'nullable',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) use ($data) {
                    if ($value && $data['date_recrutement'] && $value < $data['date_recrutement']) {
                        $fail('La date de fin de service doit être postérieure ou égale à la date de recrutement.');
                    }
                },
            ],
            'genre_fr' => 'nullable|in:Homme,Femme,Autre',
            'genre_ar' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($data, $genreMap) {
                    if ($data['genre_fr'] && $value !== ($genreMap[$data['genre_fr']] ?? null)) {
                        $fail('Le genre en arabe doit correspondre au genre en français.');
                    }
                    if (!$data['genre_fr'] && $value) {
                        $fail('Le genre en arabe ne peut pas être défini si le genre en français est vide.');
                    }
                },
            ],
            'statut' => 'required|in:Actif',
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
                function ($attribute, $value, $fail) use ($data, $etatCivilMap) {
                    if ($data['etat_civil_fr'] && $value !== ($etatCivilMap[$data['etat_civil_fr']] ?? null)) {
                        $fail("L'état civil en arabe doit correspondre à l'état civil en français.");
                    }
                    if (!$data['etat_civil_fr'] && $value) {
                        $fail("L'état civil en arabe ne peut pas être défini si l'état civil en français est vide.");
                    }
                },
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name,guard_name,web',
        ], [
            'cin.size' => 'Le CIN doit contenir exactement 8 caractères alphanumériques.',
            'cin.regex' => 'Le CIN doit contenir exactement 8 caractères alphanumériques.',
            'cin.unique' => 'Ce CIN est déjà utilisé.',
            'matricule.max' => 'Le matricule ne doit pas dépasser 20 caractères.',
            'matricule.unique' => 'Ce matricule est déjà utilisé.',
            'date_cin.date_format' => 'La date CIN doit être au format AAAA-MM-JJ et valide.',
            'date_cin.before' => 'La date CIN doit être antérieure à aujourd\'hui.',
            'date_naissance.date_format' => 'La date de naissance doit être au format AAAA-MM-JJ et valide.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'date_recrutement.date_format' => 'La date de recrutement doit être au format AAAA-MM-JJ et valide.',
            'date_recrutement.before_or_equal' => 'La date de recrutement doit être antérieure ou égale à aujourd\'hui.',
            'date_fin_service.date_format' => 'La date de fin de service doit être au format AAAA-MM-JJ et valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'email.required' => "L'email est requis.",
            'email.email' => "L'email est invalide.",
            'roles.required' => 'Au moins un rôle doit être sélectionné.',
            'roles.*.exists' => 'Le rôle sélectionné n\'existe pas.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        // Vérification explicite de l'email
        if ($validated['email'] && User::where('email', $validated['email'])->whereNull('deleted_at')->exists()) {
            Log::warning('Email déjà existant détecté:', ['email' => $validated['email']]);
            throw new ValidationException($validator, response()->json([
                'message' => 'Validation échouée',
                'errors' => ['email' => ['Cet email est déjà utilisé.']],
            ]));
        }

        $validated['genre_ar'] = isset($validated['genre_fr']) ? ($genreMap[$validated['genre_fr']] ?? null) : null;
        $validated['etat_civil_ar'] = isset($validated['etat_civil_fr']) ? ($etatCivilMap[$validated['etat_civil_fr']] ?? null) : null;
        $validated['nationalite_fr'] = $validated['nationalite_fr'] ?? 'Tunisienne';
        $validated['nationalite_ar'] = $validated['nationalite_ar'] ?? 'تونسية';
        $password = ucfirst($validated['prenom_fr'] ?? 'user') . '123***';

        if (!empty($validated['gouvernorat_fr'])) {
            $liste = Liste::where('nom_fr', 'Gouvernorats')->where('statut', 'Actif')->first();
            if ($liste && !empty($liste->options)) {
                $option = collect($liste->options)->firstWhere('nom_fr', $validated['gouvernorat_fr']);
                if (!$option) {
                    throw new ValidationException($validator, response()->json([
                        'message' => 'Validation échouée',
                        'errors' => ['gouvernorat_fr' => ["Le gouvernorat n'est pas valide."]],
                    ]));
                }
                $validated['gouvernorat_fr'] = $option['nom_fr'];
                $validated['gouvernorat_ar'] = $option['nom_ar'] ?? $validated['gouvernorat_ar'];
            } else {
                throw new ValidationException($validator, response()->json([
                    'message' => 'Validation échouée',
                    'errors' => ['gouvernorat_fr' => ["Le gouvernorat n'est pas valide."]],
                ]));
            }
        }

        $user = User::create([
            'nom_fr' => $validated['nom_fr'] ?? null,
            'prenom_fr' => $validated['prenom_fr'] ?? null,
            'nom_ar' => $validated['nom_ar'] ?? null,
            'prenom_ar' => $validated['prenom_ar'] ?? null,
            'cin' => $validated['cin'] ?? null,
            'matricule' => $validated['matricule'] ?? null,
            'date_cin' => $validated['date_cin'] ?? null,
            'lieu_cin_fr' => $validated['lieu_cin_fr'] ?? null,
            'lieu_cin_ar' => $validated['lieu_cin_ar'] ?? null,
            'date_naissance' => $validated['date_naissance'] ?? null,
            'lieu_naissance_fr' => $validated['lieu_naissance_fr'] ?? null,
            'lieu_naissance_ar' => $validated['lieu_naissance_ar'] ?? null,
            'nationalite_fr' => $validated['nationalite_fr'],
            'nationalite_ar' => $validated['nationalite_ar'],
            'date_recrutement' => $validated['date_recrutement'] ?? null,
            'date_fin_service' => $validated['date_fin_service'] ?? null,
            'genre_fr' => $validated['genre_fr'] ?? null,
            'genre_ar' => $validated['genre_ar'],
            'statut' => 'Actif',
            'adresse_fr' => $validated['adresse_fr'] ?? null,
            'adresse_ar' => $validated['adresse_ar'] ?? null,
            'gouvernorat_fr' => $validated['gouvernorat_fr'] ?? null,
            'gouvernorat_ar' => $validated['gouvernorat_ar'] ?? null,
            'delegation_fr' => $validated['delegation_fr'] ?? null,
            'delegation_ar' => $validated['delegation_ar'] ?? null,
            'telephone_1' => $validated['telephone_1'] ?? null,
            'telephone_2' => $validated['telephone_2'] ?? null,
            'etat_civil_fr' => $validated['etat_civil_fr'] ?? null,
            'etat_civil_ar' => $validated['etat_civil_ar'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
        ]);

        $user->syncRoles($validated['roles']);

        return $user;
    }
}
