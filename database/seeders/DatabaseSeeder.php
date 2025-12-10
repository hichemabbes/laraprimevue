<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ListesSeeder::class,
            DocumentSeeder::class,
            SeedRolesFr::class,
            // Ajoutez d'autres seeders ici au besoin
        ]);

        // Pour générer des utilisateurs de test (décommentez si besoin)
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
