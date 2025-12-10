<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SeedSuperAdmin extends Seeder
{
    public function run()
    {
        // 1. Vérifier si le rôle SuperAdmin existe déjà
        $role = Role::where('name', 'SuperAdmin')->first();

        if (!$role) {
            // Créer le rôle SuperAdmin
            $role = Role::create([
                'name' => 'SuperAdmin',
                'name_ar' => 'المسؤول الأعلى',
                'guard_name' => 'web',
                'statut' => 'Actif',
                'creer_par' => null, // sera mis à jour après création de l'utilisateur
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Vérifier si l'utilisateur SuperAdmin existe déjà
        $user = User::where('email', 'hichem.abbes@takwin.atfp.tn')->first();

        if (!$user) {
            $user = User::create([
                'nom_fr' => 'Abbes',
                'prenom_fr' => 'Hichem',
                'nom_ar' => 'عباس',
                'prenom_ar' => 'هشام',
                'nationalite_fr' => 'Tunisienne',
                'nationalite_ar' => 'تونسية',
                'genre_fr' => 'Homme',
                'genre_ar' => 'ذكر',
                'statut' => 'Actif',
                'telephone_1' => '28226085',
                'observation_fr' => 'Utilisateur avec tous les privilèges',
                'observation_ar' => 'المستخدم بجميع الصلاحيات للتطبيقة',
                'email' => 'hichem.abbes@takwin.atfp.tn',
                'password' => Hash::make('Hichem123***'),
                'type_user_fr' => 'Personnel Direction Centrale',
                'type_user_ar' => 'عون الإدارة المركزية',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Mettre à jour le champ 'creer_par' du rôle
        $role->update(['creer_par' => $user->id]);

        // 4. Vérifier si le rôle est déjà assigné à l'utilisateur
        $roleAssignment = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->where('model_id', $user->id)
            ->where('model_type', 'App\\Models\\User')
            ->exists();

        if (!$roleAssignment) {
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
        }

        echo "✅ SuperAdmin créé et assigné avec succès.\n";
    }
}
